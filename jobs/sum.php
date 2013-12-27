<?php

/**
 * Sum job (any currency) - delegates out to jobs/summary/<summary-type>
 * Also see summary.php, which handles conversions
 */

$latest_tickers = array();
/**
 * Get the latest ticker value for the given exchange and currency pairs.
 * Allows for caching these values.
 * @returns false if no ticker value could be found.
 */
function get_latest_ticker($exchange, $cur1, $cur2) {
	$key = $exchange . '_' . $cur1 . '_' . $cur2;
	global $latest_tickers;
	if (!isset($latest_tickers[$key])) {
		$latest_tickers[$key] = false;
		$q = db()->prepare("SELECT * FROM ticker WHERE exchange=:exchange AND currency1=:currency1 AND currency2=:currency2 AND is_recent=1 LIMIT 1");
		$q->execute(array(
			"exchange" => $exchange,
			"currency1" => $cur1,
			"currency2" => $cur2,
		));
		if ($ticker = $q->fetch()) {
			$latest_tickers[$key] = $ticker;
		}
	}
	return $latest_tickers[$key];
}

// get all of the relevant summaries for this user; we don't want to generate empty
// summary values for summary currencies that this user does not use
$q = db()->prepare("SELECT summary_type FROM summaries WHERE user_id=?");
$q->execute(array($job['user_id']));
$currencies = array();
$summaries = array();
while ($summary = $q->fetch()) {
	$summaries[] = $summary;
	if (substr($summary['summary_type'], 0, strlen('summary_')) == 'summary_') {
		$currencies[] = substr($summary['summary_type'], strlen('summary_'), 3);	// usd_mtgox -> usd
	}
}

// collate all of the total/blockchain/offsets for each currency
$totals = array();
foreach (get_all_currencies() as $cur) {
	if (in_array($cur, $currencies)) {

		crypto_log("Calculating currency $cur:<ul>");

		$total = 0;
		$total_blockchain_balance = false;

		// only non-fiat currencies have blockchains
		if (!in_array($cur, get_all_fiat_currencies())) {
			// get the most recent blockchain balances
			$q = db()->prepare("SELECT * FROM address_balances
				JOIN addresses ON address_balances.address_id=addresses.id
				WHERE address_balances.user_id=? AND is_recent=1 AND currency=?
				GROUP BY address_id");	// group by address_id to prevent race conditions
			$q->execute(array($job['user_id'], $cur));
			$total_blockchain_balance = 0;
			while ($balance = $q->fetch()) {
				$total += $balance['balance'];
				$total_blockchain_balance += $balance['balance'];
			}
		}

		// and the most recent offsets
		$q = db()->prepare("SELECT * FROM offsets
			WHERE user_id=? AND is_recent=1 AND currency=?");
		$q->execute(array($job['user_id'], $cur));
		$total_offsets_balance = 0;
		while ($offset = $q->fetch()) { // we should only have one anyway
			$total += $offset['balance'];
			$total_offsets_balance += $offset['balance'];
		}

		// and the most recent exchange/API balances
		$q = db()->prepare("SELECT * FROM balances
			WHERE user_id=? AND is_recent=1 AND currency=?
			GROUP BY exchange, account_id");	// group by exchange/account_id to prevent race conditions
		$q->execute(array($job['user_id'], $cur));
		while ($offset = $q->fetch()) { // we should only have one anyway
			$total += $offset['balance'];
		}

		crypto_log("Total $cur balance for user " . $job['user_id'] . ": " . $total);
		add_summary_instance($job, 'total' . $cur, $total);
		// only non-fiat currencies have blockchains
		if (!in_array($cur, get_all_fiat_currencies())) {
			add_summary_instance($job, 'blockchain' . $cur, $total_blockchain_balance);
		}
		add_summary_instance($job, 'offsets' . $cur, $total_offsets_balance);

		$totals[$cur] = $total;

		// calculate hashrates?
		if (in_array($cur, get_all_hashrate_currencies())) {

			$total = 0;

			// get the most recent exchange/API balances
			$q = db()->prepare("SELECT * FROM hashrates
				WHERE user_id=? AND is_recent=1 AND currency=?
				GROUP BY exchange, account_id");	// group by exchange/account_id to prevent race conditions
			$q->execute(array($job['user_id'], $cur));
			while ($offset = $q->fetch()) { // we should only have one anyway
				$total += $offset['mhash'];
			}

			crypto_log("Total $cur MHash/s for user " . $job['user_id'] . ": " . $total);

			add_summary_instance($job, 'totalmh_' . $cur, $total);

		}

		crypto_log("</ul>");

	}
}

// calculate the converted values for each currency
// by doing it in a single job, we can guarantee that all 'total', 'blockchain', 'offset' and 'converted'
// balances will always be up-to-date

// first, convert all currencies to btc
// (we might not store this value if btc is not an enabled currency, but it is the basis for all later conversions)
crypto_log("Converting equivalent BTC value<ul>");
$crypto2btc = 0;
{
	$currency = 'btc';
	$total = 0;

	// BTC is kept as-is
	if (isset($totals[$currency])) {
		crypto_log("Initial $currency balance: " . $totals[$currency]);
		$total += $totals[$currency];
	}

	// other cryptocurrencies are converted first to BTC, and then to the given currency
	foreach (array_merge(get_all_cryptocurrencies(), get_all_commodity_currencies()) as $c) {
		if ($c == $currency || $c == 'btc') continue;

		// e.g. NMC to BTC
		if (isset($totals[$c])) {
			if ($ticker = get_latest_ticker(get_default_currency_exchange($c), "btc", $c)) {
				$temp = $totals[$c] * $ticker['sell'];
				crypto_log("+ from " . get_currency_abbr($c) . " (BTC): " . ($temp));

				add_summary_instance($job, 'equivalent_btc_' . $c, $temp);

				$total += $temp;
			}
		}
	}

	// we also want to calculate equivalent_btc_FIAT for each fiat currency
	foreach (get_all_fiat_currencies() as $c) {
		// e.g. NMC to BTC
		if (isset($totals[$c])) {
			if ($ticker = get_latest_ticker(get_default_currency_exchange($c), $c, "btc")) {
				$temp = $totals[$c] / $ticker['sell'];
				crypto_log("equivalent " . get_currency_abbr($c) . " (BTC): " . ($temp));

				add_summary_instance($job, 'equivalent_btc_' . $c, $temp);
			}
		}
	}

	crypto_log("Total converted " . get_currency_abbr($currency) . " balance for user " . $job['user_id'] . ": " . $total);
	$crypto2btc = $total;

}
crypto_log("</ul>");

crypto_log("Executing " . number_format(count($summaries)) . " summaries");
foreach ($summaries as $summary) {

	$bits = explode("_", $summary['summary_type'], 3);
	if (count($bits) < 2) {
		throw new JobException("Invalid summary type '" . htmlspecialchars($summary['summary_type']) . "'");
	}
	$currency = $bits[1];
	if (!in_array($currency, get_all_currencies())) {
		throw new JobException("Currency '$currency' is not a valid currency");
	}

	crypto_log("Summary '" . htmlspecialchars($summary['summary_type']) . "'\n<ul>");

	if (in_array($currency, get_all_fiat_currencies())) {
		// fiat currencies only have all2 jobs
		$exchange = $bits[2];
		if (!$exchange) {
			throw new JobException("Invalid summary exchange '$exchange'");
		}

		$total = 0;

		// BTC is converted at the exchange's last sell rate
		// fail if there is no current rate (otherwise there is no point of this job, we don't want erraneous zero balances)
		if ($ticker = get_latest_ticker($exchange, $currency, "btc")) {
			$total += $crypto2btc * $ticker['sell'];
		} else {
			throw new JobException("There is no recent ticker balance for $currency/btc on $exchange - cannot convert");
		}

		// add total FIAT balances calculated earlier
		if (isset($totals[$currency])) {
			$total += $totals[$currency];
		}

		crypto_log("Total converted $currency $exchange balance for user " . $job['user_id'] . ": " . $total);

		add_summary_instance($job, 'all2' . $currency . '_' . $exchange, $total);

	} else if ($currency == 'btc') {

		$total = $crypto2btc;
		crypto_log("Previously calculated crypto2btc: $total");
		add_summary_instance($job, 'crypto2' . $currency, $total);

	} else {
		// non-fiat currencies only have crypto2 jobs
		// TODO enable non-fiat currencies to have all2 jobs

		$total = 0;

		// CUR is kept as-is
		if (isset($totals[$currency])) {
			crypto_log("Initial $currency balance: " . $totals[$currency]);
			$total += $totals[$currency];
		}

		// BTC is converted at default ticker rate buy
		if (isset($totals['btc'])) {
			if ($ticker = get_latest_ticker(get_default_currency_exchange($currency), "btc", $currency)) {
				crypto_log("+ from BTC: " . ($totals['btc'] / $ticker['buy']));
				$total += $totals['btc'] / $ticker['buy'];
			}
		}

		// other cryptocurrencies are converted first to BTC, and then to the given currency
		foreach (array_merge(get_all_cryptocurrencies(), get_all_commodity_currencies()) as $c) {
			if ($c == $currency || $c == 'btc') continue;

			// e.g. NMC to BTC
			if (isset($totals[$c])) {
				if ($ticker = get_latest_ticker(get_default_currency_exchange($c), "btc", $c)) {
					$temp = $totals[$c] * $ticker['sell'];
					crypto_log("+ from " . get_currency_abbr($c) . " (BTC): " . ($temp));

					// and then BTC to CUR
					if ($ticker = get_latest_ticker(get_default_currency_exchange($currency), "btc", $currency)) {
						crypto_log("+ from " . get_currency_abbr($c) . " (" . get_currency_abbr($currency) . "): " . ($temp / $ticker['buy']));
						$total += $temp / $ticker['buy'];
					}
				}

			}
		}

		crypto_log("Total converted " . get_currency_abbr($currency) . " balance for user " . $job['user_id'] . ": " . $total);

		add_summary_instance($job, 'crypto2' . $currency, $total);

	}

	crypto_log("</ul>");

}

// and now that we have added summary instances, check for first_report
// (this is so that first_report jobs don't block up the job queue)

/**
 * Send an e-mail to new users once their first non-zero summary reports have been compiled.
 */

// reload user in case multiple summary jobs for the same user are all blocked at once
$user = get_user($job['user_id']);

if (!$user['is_first_report_sent']) {
	// is there a non-zero summary instance?
	$q = db()->prepare("SELECT * FROM summary_instances WHERE user_id=? AND is_recent=1 AND balance > 0 LIMIT 1");
	$q->execute(array($user['id']));
	if ($instance = $q->fetch()) {
		crypto_log("User has a non-zero summary instance.");

		// update that we've reported now
		$q = db()->prepare("UPDATE users SET is_first_report_sent=1,first_report_sent=NOW() WHERE id=?");
		$q->execute(array($user['id']));

		// send email
		if ($user['email']) {
			send_email($user['email'], ($user['name'] ? $user['name'] : $user['email']), "first_report", array(
				"name" => ($user['name'] ? $user['name'] : $user['email']),
				"url" => absolute_url(url_for("profile")),
				"login" => absolute_url(url_for("login")),
				// TODO in the future this will have reporting values (when automatic reports are implemented)
			));
			crypto_log("Sent first report e-mail to " . htmlspecialchars($user['email']) . ".");
		}

	}
}
