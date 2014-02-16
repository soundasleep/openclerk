<?php

/**
 * Issue #24: GHash.io wallet mining job.
 */

$exchange = "ghashio";

// get the relevant address
$q = db()->prepare("SELECT * FROM accounts_ghashio WHERE user_id=? AND id=?");
$q->execute(array($job['user_id'], $job['arg_id']));
$account = $q->fetch();
if (!$account) {
	throw new JobException("Cannot find a $exchange account " . $job['arg_id'] . " for user " . $job['user_id']);
}

require(__DIR__ . "/_cexio.php");

$balance = cexio_query($account['api_key'], $account['api_username'], $account['api_secret'], "https://cex.io/api/ghash.io/hashrate");

if (isset($balance['error'])) {
	if ($balance['error'] == "Permission denied") {
		// for weeks now the GHash.io hashrate API has returned "permission denied" for no reason
		throw new ExternalAPIException("Permission denied: The GHash.io API may currently be broken");
	}
	throw new ExternalAPIException(htmlspecialchars($balance['error']));
}

if (!isset($balance['last5m'])) {
	throw new ExternalAPIException("No last5m balance found");
}

$hashrate = $balance['last5m'];

insert_new_hashrate($job, $account, $exchange, 'btc', $hashrate /* mhash */);
insert_new_hashrate($job, $account, $exchange, 'nmc', $hashrate /* mhash */);
// TODO support IXC, DVC hashrates
