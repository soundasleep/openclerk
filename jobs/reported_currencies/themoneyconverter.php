<?php

/**
 * TheMoneyConverter reported currencies job (#121).
 * Also update the list of currencies that can be voted on (#264).
 */

// get the feed
$feed = crypto_get_contents(crypto_wrap_url("http://themoneyconverter.com/rss-feed/USD/rss.xml"));

// load as XML
$xml = new SimpleXMLElement($feed);

$nodes = $xml->xpath("/rss/channel/item/title");
$currencies = array();
$currency_votes = array();
foreach ($nodes as $node) {
	$pair = explode("/", strtolower((string) $node));
	if (count($pair) == 2) {
		$currencies[] = trim($pair[0]);
		$currencies[] = trim($pair[1]);
	}

	$description = $node->xpath("../description");
	if (count($description)) {
		$title = (string) $description[0];
		// get the last text
		$matches = false;
		if (preg_match("/= [0-9\.]+ (.+)$/i", $title, $matches)) {
			$currency_votes[strtoupper(trim($pair[0]))] = $matches[1];
		}

	}
}

$currencies = array_unique($currencies);
crypto_log("Found currencies " . implode(", ", $currencies));

// update the database
$q = db()->prepare("DELETE FROM reported_currencies WHERE exchange=?");
$q->execute(array($exchange['name']));

foreach ($currencies as $cur) {
	$q = db()->prepare("INSERT INTO reported_currencies SET exchange=?, currency=?");
	$q->execute(array($exchange['name'], $cur));
}

// update the list of votable currencies
foreach ($currency_votes as $code => $name) {
	// don't add currencies we already support
	if (in_array(get_currency_key($code), get_all_currencies())) {
		continue;
	}

	$q = db()->prepare("SELECT * FROM vote_coins WHERE code=? LIMIT 1");
	$q->execute(array($code));
	if (!$q->fetch()) {
		$q = db()->prepare("INSERT INTO vote_coins SET code=?, title=?");
		$q->execute(array($code, $name));
		crypto_log("Added new vote currency $code/$name.");
	}
}
