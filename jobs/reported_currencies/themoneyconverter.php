<?php

/**
 * TheMoneyConverter reported currencies job (#121).
 */

// get the feed
$feed = crypto_get_contents(crypto_wrap_url("http://themoneyconverter.com/rss-feed/USD/rss.xml"));

// load as XML
$xml = new SimpleXMLElement($feed);

$nodes = $xml->xpath("/rss/channel/item/title");
$currencies = array();
foreach ($nodes as $node) {
	$pair = explode("/", strtolower((string) $node));
	if (count($pair) == 2) {
		$currencies[] = trim($pair[0]);
		$currencies[] = trim($pair[1]);
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
