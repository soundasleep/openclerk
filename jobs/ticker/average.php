<?php

/**
 * Cryptocurrency average price indices (#186).
 * This is a pretty neat job; it takes all of the current ticker values,
 * and generates average price indices for all supported currency pairs,
 * by balancing volume data (for markets that provide it).
 */

$q = db()->prepare("SELECT * FROM ticker_recent WHERE exchange <> ?");
$q->execute(array('average'));
$recents = $q->fetchAll();

$pairs = array();
foreach ($recents as $recent) {
	$key = $recent['currency1'] . $recent['currency2'];
	if (!isset($pairs[$key])) {
		$pairs[$key] = array(
			'total_last_trade' => 0,
			'total_volume' => 0,
			'total_ask' => 0,
			'total_bid' => 0,
			'total_volume_ask' => 0,
			'total_volume_bid' => 0,
			'currency1' => $recent['currency1'],
			'currency2' => $recent['currency2'],
			'exchanges' => 0,
		);
	}
	$pairs[$key]['total_last_trade'] += ($recent['last_trade'] * $recent['volume']);
	$pairs[$key]['total_volume'] += $recent['volume'];
	$pairs[$key]['exchanges'] ++;
	if ($recent['ask'] > 0) {
		$pairs[$key]['total_ask'] += ($recent['ask'] * $recent['volume']);
		$pairs[$key]['total_volume_ask'] += $recent['volume'];
	}
	if ($recent['bid'] > 0) {
		$pairs[$key]['total_bid'] += ($recent['bid'] * $recent['volume']);
		$pairs[$key]['total_volume_bid'] += $recent['volume'];
	}
}

// we can now create ticker values as necessary
foreach ($pairs as $pair) {

	if ($pair['total_volume'] > 0) {
		insert_new_ticker($job, $exchange, $pair['currency1'], $pair['currency2'], array(
			"last_trade" => $pair['total_last_trade'] / $pair['total_volume'],
			"bid" => $pair['total_volume_bid'] > 0 ? $pair['total_bid'] / $pair['total_volume_bid'] : 0,
			"ask" => $pair['total_volume_ask'] > 0 ? $pair['total_ask'] / $pair['total_volume_ask'] : 0,
			"volume" => $pair['total_volume'],
		));
	}

	if ($pair['exchanges'] > 0) {
		$q = db()->prepare("DELETE FROM average_market_count WHERE currency1=? AND currency2=?");
		$q->execute(array($pair['currency1'], $pair['currency2']));

		$q = db()->prepare("INSERT INTO average_market_count SET currency1=?, currency2=?, market_count=?");
		$q->execute(array($pair['currency1'], $pair['currency2'], $pair['exchanges']));
		crypto_log($pair['currency1'] . "/" . $pair['currency2'] . ": from " . number_format($pair['exchanges']) . " exchanges");
	}

}
