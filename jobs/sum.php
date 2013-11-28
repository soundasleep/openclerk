<?php

/**
 * Sum job (any currency) - delegates out to jobs/summary/<summary-type>
 * Also see summary.php, which handles conversions
 */

// get all of the relevant summaries for this user; we don't want to generate empty
// summary values for summary currencies that this user does not use
$q = db()->prepare("SELECT summary_type FROM summaries WHERE user_id=?");
$q->execute(array($job['user_id']));
$currencies = array();
while ($summary = $q->fetch()) {
	if (substr($summary['summary_type'], 0, strlen('summary_')) == 'summary_') {
		$currencies[] = substr($summary['summary_type'], strlen('summary_'), 3);	// usd_mtgox -> usd
	}
}

// overall summary job of all cryptocurrencies and fiat currencies, before any conversions
foreach (get_all_cryptocurrencies() as $cur) {
	if (in_array($cur, $currencies)) {
		$total = 0;
		require(__DIR__ . "/summary/total" . $cur . ".php");
		add_summary_instance($job, 'total' . $cur, $total);
		add_summary_instance($job, 'blockchain' . $cur, $total_blockchain_balance);
		add_summary_instance($job, 'offsets' . $cur, $total_offsets_balance);
	}
}

if (in_array('usd', $currencies)) {
	$total = 0;
	require(__DIR__ . "/summary/totalusd.php");
	add_summary_instance($job, 'totalusd', $total);
	//add_summary_instance($job, 'blockchainusd', $total_blockchain_balance);
	add_summary_instance($job, 'offsetsusd', $total_offsets_balance);
}

if (in_array('eur', $currencies)) {
	$total = 0;
	require(__DIR__ . "/summary/totaleur.php");
	add_summary_instance($job, 'totaleur', $total);
	//add_summary_instance($job, 'blockchaineur', $total_blockchain_balance);
	add_summary_instance($job, 'offsetseur', $total_offsets_balance);
}

if (in_array('aud', $currencies)) {
	$total = 0;
	require(__DIR__ . "/summary/totalaud.php");
	add_summary_instance($job, 'totalaud', $total);
	//add_summary_instance($job, 'blockchainaud', $total_blockchain_balance);
	add_summary_instance($job, 'offsetsaud', $total_offsets_balance);
}

if (in_array('cad', $currencies)) {
	$total = 0;
	require(__DIR__ . "/summary/totalcad.php");
	add_summary_instance($job, 'totalcad', $total);
	//add_summary_instance($job, 'blockchaincad', $total_blockchain_balance);
	add_summary_instance($job, 'offsetscad', $total_offsets_balance);
}

if (in_array('nzd', $currencies)) {
	$total = 0;
	require(__DIR__ . "/summary/totalnzd.php");
	add_summary_instance($job, 'totalnzd', $total);
	//add_summary_instance($job, 'blockchainnzd', $total_blockchain_balance);
	add_summary_instance($job, 'offsetsnzd', $total_offsets_balance);
}

if (in_array('btc', $currencies)) {
	$total = 0;
	require(__DIR__ . "/summary/totalhashrate_btc.php");
	add_summary_instance($job, 'totalmh_btc', $total);
}

if (in_array('ltc', $currencies)) {
	$total = 0;
	require(__DIR__ . "/summary/totalhashrate_ltc.php");
	add_summary_instance($job, 'totalmh_ltc', $total);
}

if (in_array('nmc', $currencies)) {
	$total = 0;
	require(__DIR__ . "/summary/totalhashrate_nmc.php");
	add_summary_instance($job, 'totalmh_nmc', $total);
}

if (in_array('nvc', $currencies)) {
	$total = 0;
	require(__DIR__ . "/summary/totalhashrate_nvc.php");
	add_summary_instance($job, 'totalmh_nvc', $total);
}

if (in_array('ghs', $currencies)) {
	$total = 0;
	require(__DIR__ . "/summary/totalghs.php");
	add_summary_instance($job, 'totalghs', $total);
	// add_summary_instance($job, 'blockchainghs', $total_blockchain_balance);
	add_summary_instance($job, 'offsetsghs', $total_offsets_balance);
}
