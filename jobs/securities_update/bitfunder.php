<?php

/**
 * A batch script to get all current BitFunder securities and their owners, and add
 * ticker values for owners as appropriate. Also gets all current BitFunder securities,
 * and their current values.
 * BitFunder doesn't provide an API for market data, so we have to scrape HTML.
 * BitFunder publishes all asset owners to a public .json file, so we can download
 * this file and match public BTC addresses to users.
 */

$exchange = "securities_bitfunder";
$currency = 'btc';

// step 1: download a list of all assets and their values
// and create securities as necessary
// there's no API for this yet, so we just scrape HTML
$content = crypto_get_contents(crypto_wrap_url('https://bitfunder.com/market'));
if (!$content) {
	throw new ExternalAPIException("market API returned empty data");
}

require(__DIR__ . '/../../inc/html5lib/Parser.php');
$dom = HTML5_Parser::parse($content);

function numberise($s) {
	return preg_replace("#[^0-9\\.\\-]#im", "", $s);
}

// now load as XML
$xml = new SimpleXMLElement($dom->saveXML());
$assets = $xml->xpath('//table[contains(@id,"marketTable")]/tbody/tr');
$assets_securities = array();
$securities_parsed = 0;
for ($i = 0; $i < count($assets); $i++) {
	$rows = $assets[$i]->xpath("td");
	$vol = explode("\n", (string) $rows[8]);
	$asset = array(
		'name' => (string) $rows[1],
		'last_trade' => (string) $rows[3],
		'buy' => (string) $rows[5],
		'sell' => (string) $rows[7],
		'vol_24hr' => numberise($vol[0]),
		'vol_24hr_qty' => numberise($vol[1]),
		'low_24hr' => (string) $rows[11],
		'high_24hr' => (string) $rows[13],
		// ignore 48hr data
	);

	// try and prevent bad data from going in
	if (!$asset['name']) {
		crypto_log("Security had no name, ignoring: " . print_r($asset, true));
		continue;
	}

	// if this security has a balance of 0, then it's worthless and it's not really
	// worth saving into the database
	if ($asset['last_trade'] == 0) {
		crypto_log("Security '" . htmlspecialchars($asset['name']) . "' had a bid of 0: ignoring");
		continue;
	}

	$q = db()->prepare("SELECT * FROM securities_bitfunder WHERE name=?");
	$q->execute(array($asset['name']));
	$security_def = $q->fetch();
	if (!$security_def) {
		// need to insert a new security definition, so we can later get its value
		// we can't calculate the value of this security yet
		crypto_log("No securities_bitfunder definition existed for '" . htmlspecialchars($asset['name']) . "': adding in new definition");
		$q = db()->prepare("INSERT INTO securities_bitfunder SET name=?");
		$q->execute(array($asset['name']));
		$security_def = array(
			'name' => $asset['name'],
			'id' => db()->lastInsertId(),
		);
	}

	// keep a track of the security for later
	$asset['id'] = $security_def['id'];
	$assets_securities[$asset['name']] = $asset;

	// since we already have bid data here, we might as well save it for free
	// TODO create a new table for securities, so we can store vol/high/low, rather than balances
	insert_new_balance($job, $security_def, $exchange, $currency, $asset['last_trade']);

	$securities_parsed++;

}

crypto_log("Parsed " . plural($securities_parsed, "security", "securities") . ".");

// step 2: download asset ownership
// get the API data
$content = crypto_get_contents(crypto_wrap_url('https://bitfunder.com/assetlist.json'));
if (!$content) {
	throw new ExternalAPIException("assetlist API returned empty data");
}
$json = json_decode($content, true);
if (!$json) {
	throw new ExternalAPIException("JSON was invalid");
}

// find all addresses that we need to watch out for
$q = db()->prepare("SELECT accounts_bitfunder.* FROM accounts_bitfunder JOIN users ON accounts_bitfunder.user_id=users.id WHERE users.is_disabled=0");
$q->execute(array());
$addresses = array();
$total_securities = array();
while ($address = $q->fetch()) {
	if (!isset($addresses[$address['btc_address']])) {
		$addresses[$address['btc_address']] = array();	// an array of accounts - one public address can belong to many accounts
	}
	$address['balance'] = 0;
	$addresses[$address['btc_address']][] = $address;
	$total_securities[$address['user_id']] = array();
}

$owners = 0;
$owned = 0;
foreach ($json as $data) {
	$owners++;
	if (isset($addresses[$data['user_btc_address']])) {
		$owned++;
		foreach ($addresses[$data['user_btc_address']] as $key => $address) {
			$user_id = $address['user_id'];
			crypto_log('User ' . $user_id . ' (' . htmlspecialchars($data['user_btc_address']) . ') owns ' .
				htmlspecialchars($data['amount']) . ' of ' . htmlspecialchars($data['asset_name']) . ' @ ' . $assets_securities[$data['asset_name']]['sell'] . ' = '
				. ($data['amount'] * $assets_securities[$data['asset_name']]['sell']));

			// increase total securities owned (one user may have many public addresses)
			if (!isset($total_securities[$user_id][$data['asset_name']])) {
				$total_securities[$user_id][$data['asset_name']] = 0;
			}
			$total_securities[$user_id][$data['asset_name']] += $data['amount'];

			// increase total balance (stored per accounts_bitfunder)
			$addresses[$data['user_btc_address']][$key]['balance'] += ($data['amount'] * $assets_securities[$data['asset_name']]['sell']);
		}
	}
}

crypto_log("Processed " . plural($owners, "owner asset") . " into " . plural($owned, "owned asset") . ".");

// now, update all user securities
$securities_inserted = 0;
foreach ($total_securities as $user_id => $securities) {
	$q = db()->prepare("UPDATE securities SET is_recent=0 WHERE user_id=? AND exchange=?");
	$q->execute(array($user_id, 'bitfunder'));

	foreach ($securities as $name => $count) {
		$q = db()->prepare("INSERT INTO securities SET user_id=:user_id, exchange=:exchange, security_id=:security_id, quantity=:quantity, is_recent=1");
		$q->execute(array(
			'user_id' => $user_id,
			'exchange' => 'bitfunder',
			'security_id' => $assets_securities[$name]['id'],
			'quantity' => $count,
		));
		$securities_inserted++;
	}

}

crypto_log("Inserted " . plural($securities_inserted, "new security instance") . ".");

// now, update user balances
foreach ($addresses as $btc_address => $address_list) {
	foreach ($address_list as $address) {
		$job2 = $job;
		$job2['user_id'] = $address['user_id'];
		$balance = $address['balance'];
		insert_new_balance($job2, $address, 'bitfunder_securities', $currency, $balance);
	}
}

// and we're done!
