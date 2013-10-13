<?php

/**
 * Sum job (any currency) - delegates out to jobs/summary/<summary-type>
 * Also see summary.php, which handles conversions
 */

// overall summary job of all cryptocurrencies and fiat currencies, before any conversions
$total = 0;
require("jobs/summary/totalbtc.php");
add_summary_instance($job, 'totalbtc', $total);
add_summary_instance($job, 'blockchainbtc', $total_blockchain_balance);
add_summary_instance($job, 'offsetsbtc', $total_offsets_balance);

$total = 0;
require("jobs/summary/totalltc.php");
add_summary_instance($job, 'totalltc', $total);
add_summary_instance($job, 'blockchainltc', $total_blockchain_balance);
add_summary_instance($job, 'offsetsltc', $total_offsets_balance);

$total = 0;
require("jobs/summary/totalnmc.php");
add_summary_instance($job, 'totalnmc', $total);
add_summary_instance($job, 'blockchainnmc', $total_blockchain_balance);
add_summary_instance($job, 'offsetsnmc', $total_offsets_balance);

$total = 0;
require("jobs/summary/totalftc.php");
add_summary_instance($job, 'totalftc', $total);
add_summary_instance($job, 'blockchainftc', $total_blockchain_balance);
add_summary_instance($job, 'offsetsftc', $total_offsets_balance);

$total = 0;
require("jobs/summary/totalppc.php");
add_summary_instance($job, 'totalppc', $total);
add_summary_instance($job, 'blockchainppc', $total_blockchain_balance);
add_summary_instance($job, 'offsetsppc', $total_offsets_balance);

$total = 0;
require("jobs/summary/totalnvc.php");
add_summary_instance($job, 'totalnvc', $total);
add_summary_instance($job, 'blockchainnvc', $total_blockchain_balance);
add_summary_instance($job, 'offsetsnvc', $total_offsets_balance);

$total = 0;
require("jobs/summary/totalusd.php");
add_summary_instance($job, 'totalusd', $total);
//add_summary_instance($job, 'blockchainusd', $total_blockchain_balance);
add_summary_instance($job, 'offsetsusd', $total_offsets_balance);

$total = 0;
require("jobs/summary/totaleur.php");
add_summary_instance($job, 'totaleur', $total);
//add_summary_instance($job, 'blockchaineur', $total_blockchain_balance);
add_summary_instance($job, 'offsetseur', $total_offsets_balance);

$total = 0;
require("jobs/summary/totalaud.php");
add_summary_instance($job, 'totalaud', $total);
//add_summary_instance($job, 'blockchainaud', $total_blockchain_balance);
add_summary_instance($job, 'offsetsaud', $total_offsets_balance);

$total = 0;
require("jobs/summary/totalcad.php");
add_summary_instance($job, 'totalcad', $total);
//add_summary_instance($job, 'blockchaincad', $total_blockchain_balance);
add_summary_instance($job, 'offsetscad', $total_offsets_balance);

$total = 0;
require("jobs/summary/totalnzd.php");
add_summary_instance($job, 'totalnzd', $total);
//add_summary_instance($job, 'blockchainnzd', $total_blockchain_balance);
add_summary_instance($job, 'offsetsnzd', $total_offsets_balance);

$total = 0;
require("jobs/summary/totalhashrate_btc.php");
add_summary_instance($job, 'totalmh_btc', $total);

$total = 0;
require("jobs/summary/totalhashrate_ltc.php");
add_summary_instance($job, 'totalmh_ltc', $total);

$total = 0;
require("jobs/summary/totalhashrate_nmc.php");
add_summary_instance($job, 'totalmh_nmc', $total);
