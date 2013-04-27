<?php

/**
 * Litecoin Explorer job (LTC).
 * The litecoin explorer (through Abe) does not provide an expressive enough API
 * for things like /address/x/balance?confirmations=6
 * So, we need to emulate this by periodically querying Explorer for the current block number,
 * and HTML parsing /address pages for transactions, and reversing any transactions within
 * a block number less than the current block number (minus some number).
 * We can go zero-confirmations for users, but for payment we need to have confirmations.
 */

// the 'litecoin_block' job will find the current block number
$q = db()->prepare("SELECT * FROM litecoin_blocks WHERE is_recent=1");
$q->execute();
if (!($block = $q->fetch())) {
	throw new JobException("Could not calculate current Litecoin block number");
}
$block = $block['blockcount'] - get_site_config('ltc_confirmations');			// will be decimal
crypto_log("Cached Litecoin block count: " . number_format($block));

// get the relevant address
$q = db()->prepare("SELECT * FROM addresses WHERE user_id=? AND id=?");
$q->execute(array($job['user_id'], $job['arg_id']));
$address = $q->fetch();
if (!$address) {
	throw new JobException("Cannot find an address " . $job['arg_id'] . " for user " . $job['user_id']);
}

// we can now request the HTML page
$html = crypto_get_contents(crypto_wrap_url("http://explorer.litecoin.net/address/" . urlencode($address['address'])));

// assumes that the page format will not change
if (preg_match('#<p>Balance: ([0-9\.]+) LTC#i', $html, $matches)) {
	$balance = $matches[1];
	crypto_log("Address balance before removing unconfirmed: " . $balance);

	if (preg_match_all('#<tr><td>.+</td><td><a href=[^>]+>([0-9]+)</a></td><td>.+</td><td>([0-9\\.\\(\\)]+)</td><td>([0-9\\.]+)</td><td>LTC</td></tr>#im', $html, $matches, PREG_SET_ORDER)) {
		foreach ($matches as $match) {
			if ($match[1] >= $block) {
				// too recent
				$amount = $match[2];
				if (substr($amount, 0, 1) == "(" && substr($amount, -1) == ")") {
					// convert (1.23) into -1.23
					$amount = - substr($amount, 1, strlen($amount) - 2);
				}
				crypto_log("Removing " . $amount . " from balance: unconfirmed (block " . $match[1] . " >= " . $block . ")");
				$balance -= $amount;
			}
		}

		crypto_log("Confirmed balance after " . get_site_config('ltc_confirmations') . " confirmations: " . $balance);

		// disable old instances
		$q = db()->prepare("UPDATE address_balances SET is_recent=0 WHERE is_recent=1 AND user_id=:user_id AND address_id=:address_id");
		$q->execute(array(
			"user_id" => $job['user_id'],
			"address_id" => $address['id'],
		));

		// we have a balance; update the database
		$q = db()->prepare("INSERT INTO address_balances SET user_id=:user_id, address_id=:address_id, balance=:balance, is_recent=1");
		$q->execute(array(
			"user_id" => $job['user_id'],
			"address_id" => $address['id'],
			"balance" => $balance,
		));
		crypto_log("Inserted new address_balances id=" . db()->lastInsertId());

	} else {
		throw new JobException("Could not find any transactions on page");
	}

} else {
	throw new JobException("Could not find balance on page");
}