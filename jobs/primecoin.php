<?php

/**
 * Get Primecoin balance (XPM).
 * Since this isn't based off Abe, we scrape HTML instead (ergh).
 */

// get the relevant address
$q = db()->prepare("SELECT * FROM addresses WHERE user_id=? AND id=?");
$q->execute(array($job['user_id'], $job['arg_id']));
$address = $q->fetch();
if (!$address) {
	throw new JobException("Cannot find an address " . $job['arg_id'] . " for user " . $job['user_id']);
}

if ($address['is_received']) {
	throw new JobException("is_received is not implemented for Primecoin");
}
$url = get_site_config('xpm_address_url') . $address['address'];

$html = crypto_get_contents(crypto_wrap_url($url));

// don't parse the page if we don't need to
if (preg_match("/Address not found/im", $html) || preg_match("/Address is either invalid or has not been used/im", $html)) {
	crypto_log("Address is either invalid or has not been used.");
	$balance = 0;

} else {
	require(__DIR__ . '/../vendor/soundasleep/html5lib-php/library/HTML5/Parser.php');

	// this HTML is totally messed up and invalid; try to clean it up
	$html = preg_replace("/&([a-z]+)/im", "", $html);
	$html = preg_replace("/class'/im", "class='", $html);
	$html = str_replace("<span<", "<span><", $html);
	$html = str_replace("</table<", "</table><", $html);

	$dom = HTML5_Parser::parse($html);

	// now load as XML
	$xml = new SimpleXMLElement($dom->saveXML());

	$x = $xml->xpath('//table[contains(@id,"blocks")]//td/.');
	if (!$x) {
		throw new ExternalAPIException("Could not find balance on page");
	}
	crypto_log(print_r($x, true));
	$balance = (string) $x[1];
	if (!is_numeric($balance)) {
		throw new ExternalAPIException("Balance was not numeric: " . $block);
	}
}

crypto_log("Address balance: " . $balance);

// this API does not report blocks with transactions, so we can't process
// min_confirmations at all
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

