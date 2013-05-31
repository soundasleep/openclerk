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

// assumes that the page format will not change
if (preg_match('#<p>Balance: ([0-9\.]+) ' . strtoupper($abe_data['currency']) . '#i', $html, $matches)) {
	$balance = $matches[1];
	crypto_log("Address balance before removing unconfirmed: " . $balance);

	if (preg_match_all('#<tr><td>.+</td><td><a href=[^>]+>([0-9]+)</a></td><td>.+</td><td>([0-9\\.\\(\\)]+)</td><td>([0-9\\.]+)</td><td>' . strtoupper($abe_data['currency']) . '</td></tr>#im', $html, $matches, PREG_SET_ORDER)) {
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

		crypto_log("Confirmed balance after " . $abe_data['confirmations'] . " confirmations: " . $balance);

	} else {
		throw new ExternalAPIException("Could not find any transactions on page");
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

} else {
	throw new ExternalAPIException("Could not find balance on page");
}

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
crypto_log("Inserted new " . strtoupper($abe_data['currency']) . " address_balances id=" . db()->lastInsertId());
