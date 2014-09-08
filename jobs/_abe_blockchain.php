<?php

// expects $abe_data input

// the 'litecoin_block' job will find the current block number
$q = db()->prepare("SELECT * FROM " . $abe_data['block_table'] . " WHERE is_recent=1");
$q->execute();
if (!($block = $q->fetch())) {
	throw new JobException("Could not calculate current " . get_currency_name($abe_data['currency']) . " block number");
}
$block = $block['blockcount'] - $abe_data['confirmations'];			// will be decimal
crypto_log("Cached " . get_currency_name($abe_data['currency']) . " block count: " . number_format($block));

// get the relevant address
$q = db()->prepare("SELECT * FROM addresses WHERE user_id=? AND id=?");
$q->execute(array($job['user_id'], $job['arg_id']));
$address = $q->fetch();
if (!$address) {
	throw new JobException("Cannot find an address " . $job['arg_id'] . " for user " . $job['user_id']);
}

// we can now request the HTML page
$html = crypto_get_contents(crypto_wrap_url($abe_data['explorer_url'] . urlencode($address['address'])));
$html = preg_replace("#[\n\t]+#", "", $html);
$html = preg_replace("#</tr>#", "</tr>\n", $html);
$html = preg_replace("#<td[^>]+?>#", "<td>", $html);
$html = preg_replace("#<tr[^>]+?>#", "<tr>", $html);
$html = preg_replace("#<span[^>]+?>#", "", $html);
$html = preg_replace("#</span>#", "", $html);
$html = preg_replace("#> *<#", "><", $html);

if ($address['is_received']) {
	crypto_log("We are looking for received balance.");
}

// assumes that the page format will not change
if (!$address['is_received'] && preg_match('#(<p>|<tr><th>|<tr><td>)Balance:?( |</th><td>|</td><td>)([0-9\.]+) ' . get_currency_abbr($abe_data['currency']) . '#im', $html, $matches)) {
	$balance = $matches[3];
	crypto_log("Address balance before removing unconfirmed: " . $balance);

	if (preg_match_all('#<tr><td>.+</td><td><a href=[^>]+>([0-9]+)</a></td><td>.+</td><td>(- |\\+ |)([0-9\\.\\(\\)]+)</td><td>([0-9\\.]+)</td><td>' . get_currency_abbr($abe_data['currency']) . '</td></tr>#im', $html, $matches, PREG_SET_ORDER)) {
		foreach ($matches as $match) {
			if ($match[1] >= $block) {
				// too recent
				$amount = $match[3];
				if (substr($amount, 0, 1) == "(" && substr($amount, -1) == ")") {
					// convert (1.23) into -1.23
					$amount = - substr($amount, 1, strlen($amount) - 2);
				}
				if ($match[2] == "+ ") {
					$amount = +$amount;
				} else if ($match[2] == "- ") {
					$amount = -$amount;
				}
				crypto_log("Removing " . $amount . " from balance: unconfirmed (block " . $match[1] . " >= " . $block . ")");
				$balance -= $amount;
			}
		}

		crypto_log("Confirmed balance after " . $abe_data['confirmations'] . " confirmations: " . $balance);

	} else {
		if ($abe_data['currency'] == 'dog') {
			crypto_log("DOGE currency had no transactions: this is OK");
		} else {
			throw new ExternalAPIException("Could not find any transactions on page");
		}
	}
} else if ($address['is_received'] && preg_match('#(|<tr><th>|<tr><td>)Received:?( |</th><td>|</td><td>)([0-9\.]+) ' . get_currency_abbr($abe_data['currency']) . '#i', $html, $matches)) {
	$balance = $matches[3];
	crypto_log("Address received before removing unconfirmed: " . $balance);

	if (preg_match_all('#<tr><td>.+</td><td><a href=[^>]+>([0-9]+)</a></td><td>.+</td><td>(- |\\+ |)([0-9\\.\\(\\)]+)</td><td>([0-9\\.]+)</td><td>' . get_currency_abbr($abe_data['currency']) . '</td></tr>#im', $html, $matches, PREG_SET_ORDER)) {
		foreach ($matches as $match) {
			if ($match[1] >= $block) {
				// too recent
				$amount = $match[3];
				if (substr($amount, 0, 1) == "(" && substr($amount, -1) == ")") {
					// convert (1.23) into -1.23
					$amount = - substr($amount, 1, strlen($amount) - 2);
				}
				if ($match[2] == "+ ") {
					$amount = +$amount;
				} else if ($match[2] == "- ") {
					$amount = -$amount;
				}
				// only consider received
				if ($amount > 0) {
					crypto_log("Removing " . $amount . " from received: unconfirmed (block " . $match[1] . " >= " . $block . ")");
					$balance -= $amount;
				}
			}
		}

		crypto_log("Confirmed received after " . $abe_data['confirmations'] . " confirmations: " . $balance);

	} else {
		if ($abe_data['currency'] == 'dog') {
			crypto_log("DOGE currency had no transactions: this is OK");
		} else {
			throw new ExternalAPIException("Could not find any transactions on page");
		}
	}
} else if (strpos($html, "Address not seen on the network.") !== false) {
	// the address is valid, it just doesn't have a balance
	$balance = 0;
	crypto_log("Address is valid, but not yet seen on network");

} else if (strpos($html, "Not a valid address.") !== false) {
	// the address is NOT valid
	throw new ExternalAPIException("Not a valid address");

} else if (strpos($html, "this address has too many records to display") !== false) {
	// this address is valid, and it has a balance, but it has too many records for this Abe instance
	crypto_log("Address is valid, but has too many records to display");
	throw new ExternalAPIException("Address has too many transactions");

} else if (strpos(strtolower($html), "500 internal server error") !== false) {
	crypto_log("Server returned 500 Internal Server Error");
	throw new ExternalAPIException("Server returned 500 Internal Server Error");

} else {
	throw new ExternalAPIException("Could not find balance on page");
}

insert_new_address_balance($job, $address, $balance);
