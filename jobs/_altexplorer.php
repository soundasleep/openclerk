<?php

/**
 * Use the wonderful block explorers provided by https://altexplorer.net
 */

// expects $altexplorer_data input

// get the relevant address
$q = db()->prepare("SELECT * FROM addresses WHERE user_id=? AND id=?");
$q->execute(array($job['user_id'], $job['arg_id']));
$address = $q->fetch();
if (!$address) {
	throw new JobException("Cannot find an address " . $job['arg_id'] . " for user " . $job['user_id']);
}

// we can now request the HTML page
$html = crypto_get_contents(crypto_wrap_url($altexplorer_data['explorer_url'] . urlencode($address['address'])));
$html = preg_replace("#[\n\t]+#", "", $html);
$html = preg_replace("#</tr>#", "</tr>\n", $html);

if ($address['is_received']) {
	crypto_log("We are looking for received balance.");
}

// assumes that the page format will not change
if (!$address['is_received'] && preg_match('#(<p>|<tr><th>|<tr><td>)Balance:?( |</th><td>|</td><td>)([0-9\.]+) ' . get_currency_abbr($altexplorer_data['currency']) . '#im', $html, $matches)) {
	$balance = $matches[3];
	crypto_log("Address balance: " . $balance);

} else if ($address['is_received'] && preg_match('#(|<tr><th>|<tr><td>)Received:?( |</th><td>|</td><td>)([0-9\.]+) ' . get_currency_abbr($altexplorer_data['currency']) . '#i', $html, $matches)) {
	$balance = $matches[3];
	crypto_log("Address received: " . $balance);

} else {
	throw new ExternalAPIException("Could not find balance on page");
}

insert_new_address_balance($job, $address, $balance);
