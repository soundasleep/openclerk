<?php
/**
 * Helper methods for inserting in new balances, tickers, summary instances, etc.
 */

function insert_new_balance($job, $account, $exchange, $currency, $balance) {

	crypto_log("$exchange $currency balance for user " . $job['user_id'] . ": " . $balance);

	// we have a balance; update the database
	$q = db()->prepare("INSERT INTO balances SET user_id=:user_id, exchange=:exchange, account_id=:account_id, balance=:balance, currency=:currency, job_id=:job_id, is_recent=1, is_daily_data=1, created_at=NOW(), created_at_day=TO_DAYS(NOW())");
	$q->execute(array(
		"user_id" => $job['user_id'],
		"account_id" => $account['id'],
		"exchange" => $exchange,
		"currency" => $currency,
		"balance" => $balance,
		"job_id" => $job['id'],
		// we ignore server_time
	));
	$last_id = db()->lastInsertId();
	crypto_log("Inserted new $exchange $currency balances id=" . $last_id);

	// disable old instances
	$q = db()->prepare("UPDATE balances SET is_recent=0 WHERE is_recent=1 AND user_id=:user_id AND exchange=:exchange AND account_id=:account_id AND currency=:currency AND id <> :id");
	$q->execute(array(
		"user_id" => $job['user_id'],
		"account_id" => $account['id'],
		"exchange" => $exchange,
		"currency" => $currency,
		"id" => $last_id,
	));

	// all other data from today is now old
	// NOTE if the system time changes between the next two commands, then we may erraneously
	// specify that there is no valid daily data. one solution is to specify NOW() as $created_at rather than
	// relying on MySQL
	$q = db()->prepare("UPDATE balances SET is_daily_data=0 WHERE is_daily_data=1 AND user_id=:user_id AND account_id=:account_id AND exchange=:exchange AND currency=:currency AND
		created_at_day = TO_DAYS(NOW()) AND id <> :id");
	$q->execute(array(
		"user_id" => $job['user_id'],
		"account_id" => $account['id'],
		"exchange" => $exchange,
		"currency" => $currency,
		"id" => $last_id,
	));

}

function insert_new_hashrate($job, $account, $exchange, $currency, $mhash) {

	crypto_log("$exchange $currency hashrate for user " . $job['user_id'] . ": " . $mhash . " MH/s");

	// we have a balance; update the database
	$q = db()->prepare("INSERT INTO hashrates SET user_id=:user_id, exchange=:exchange, account_id=:account_id, mhash=:mhash, currency=:currency, job_id=:job_id, is_recent=1, is_daily_data=1, created_at=NOW(), created_at_day=TO_DAYS(NOW())");
	$q->execute(array(
		"user_id" => $job['user_id'],
		"account_id" => $account['id'],
		"exchange" => $exchange,
		"currency" => $currency,
		"mhash" => $mhash,
		"job_id" => $job['id'],
		// we ignore server_time
	));
	$last_id = db()->lastInsertId();
	crypto_log("Inserted new $exchange $currency hashrates id=" . $last_id);

	// disable old instances
	$q = db()->prepare("UPDATE hashrates SET is_recent=0 WHERE is_recent=1 AND user_id=:user_id AND exchange=:exchange AND account_id=:account_id AND currency=:currency AND id <> :id");
	$q->execute(array(
		"user_id" => $job['user_id'],
		"account_id" => $account['id'],
		"exchange" => $exchange,
		"currency" => $currency,
		"id" => $last_id,
	));

	// all other data from today is now old
	// NOTE if the system time changes between the next two commands, then we may erraneously
	// specify that there is no valid daily data. one solution is to specify NOW() as $created_at rather than
	// relying on MySQL
	$q = db()->prepare("UPDATE hashrates SET is_daily_data=0 WHERE is_daily_data=1 AND user_id=:user_id AND account_id=:account_id AND exchange=:exchange AND currency=:currency AND
		created_at_day = TO_DAYS(NOW()) AND id <> :id");
	$q->execute(array(
		"user_id" => $job['user_id'],
		"account_id" => $account['id'],
		"exchange" => $exchange,
		"currency" => $currency,
		"id" => $last_id,
	));

}

function insert_new_address_balance($job, $address, $balance) {

	crypto_log("Address balance for user " . $job['user_id'] . ": " . $balance);

	// we have a balance; update the database
	$q = db()->prepare("INSERT INTO address_balances SET user_id=:user_id, address_id=:address_id, balance=:balance, is_recent=1, is_daily_data=1, created_at=NOW(), created_at_day=TO_DAYS(NOW())");
	$q->execute(array(
		"user_id" => $job['user_id'],
		"address_id" => $address['id'],
		"balance" => $balance,
		// we ignore server_time
	));
	$last_id = db()->lastInsertId();
	crypto_log("Inserted new address_balances id=" . $last_id);

	// disable old instances
	$q = db()->prepare("UPDATE address_balances SET is_recent=0 WHERE is_recent=1 AND user_id=:user_id AND address_id=:address_id AND id <> :id");
	$q->execute(array(
		"user_id" => $job['user_id'],
		"address_id" => $address['id'],
		"id" => $last_id,
	));

	// all other data from today is now old
	// NOTE if the system time changes between the next two commands, then we may erraneously
	// specify that there is no valid daily data. one solution is to specify NOW() as $created_at rather than
	// relying on MySQL
	$q = db()->prepare("UPDATE address_balances SET is_daily_data=0 WHERE is_daily_data=1 AND user_id=:user_id AND address_id=:address_id AND
		created_at_day = TO_DAYS(NOW()) AND id <> :id");
	$q->execute(array(
		"user_id" => $job['user_id'],
		"address_id" => $address['id'],
		"id" => $last_id,
	));

}

function add_summary_instance($job, $summary_type, $total) {

	// insert new summary
	$q = db()->prepare("INSERT INTO summary_instances SET is_recent=1, user_id=:user_id, summary_type=:summary_type, balance=:balance, job_id=:job_id, is_daily_data=1, created_at=NOW(), created_at_day=TO_DAYS(NOW())");
	$q->execute(array(
		"user_id" => $job['user_id'],
		"summary_type" => $summary_type,
		"balance" => $total,
		"job_id" => $job['id'],
	));
	$last_id = db()->lastInsertId();
	crypto_log("Inserted new summary_instances '$summary_type' id=" . $last_id);

	// update old summaries
	$q = db()->prepare("UPDATE summary_instances SET is_recent=0 WHERE is_recent=1 AND user_id=? AND summary_type=? AND id <> ?");
	$q->execute(array($job['user_id'], $summary_type, $last_id));

	// all other data from today is now old
	// NOTE if the system time changes between the next two commands, then we may erraneously
	// specify that there is no valid daily data. one solution is to specify NOW() as $created_at rather than
	// relying on MySQL
	$q = db()->prepare("UPDATE summary_instances SET is_daily_data=0 WHERE is_daily_data=1 AND summary_type=:summary_type AND user_id=:user_id AND
		created_at_day = TO_DAYS(NOW()) AND id <> :id");
	$q->execute(array(
		"summary_type" => $summary_type,
		"user_id" => $job['user_id'],
		"id" => $last_id,
	));

}

/**
 * @param $values array(last_trade, bid, ask, volume (optional))
 */
function insert_new_ticker($job, $exchange, $cur1, $cur2, $values) {

	// sanity and quality checks
	if (!isset($values['last_trade'])) {
		throw new Exception("No last_trade specified for $cur1/$cur2 on $exchange");	// need at least this
	}
	if (isset($values['sell'])) {
		throw new Exception("Invalid parameter: sell (should be bid)");
	}
	if (isset($values['buy'])) {
		throw new Exception("Invalid parameter: buy (should be ask)");
	}
	if (!isset($values['volume'])) {
		$values['volume'] = null;
	}
	if (!isset($values['bid'])) {
		$values['bid'] = null;
	}
	if (!isset($values['ask'])) {
		$values['ask'] = null;
	}
	if (strlen($exchange['name']) <= 1) {
		throw new Exception("Invalid parameter: exchange '" . htmlspecialchars($exchange['name']) . "'");
	}
	if (strlen($cur1) <= 1) {
		throw new Exception("Invalid parameter: currency1 '" . htmlspecialchars($cur1) . "'");
	}
	if (strlen($cur2) <= 1) {
		throw new Exception("Invalid parameter: currency2 '" . htmlspecialchars($cur2) . "'");
	}

	crypto_log($exchange['name'] . " rate for $cur1/$cur2: " . $values['last_trade'] . " (" . $values['bid'] . "/" . $values['ask'] . ") [" . $values['volume'] . "]");
	if ($values['bid'] > $values['ask']) {
		crypto_log("<strong>WARNING:</strong> bid > ask");
	}

	// insert in new ticker value
	$q = db()->prepare("INSERT INTO ticker SET exchange=:exchange, currency1=:currency1, currency2=:currency2, last_trade=:last_trade, bid=:bid, ask=:ask, volume=:volume, job_id=:job_id, is_daily_data=1, created_at=NOW(), created_at_day=TO_DAYS(NOW())");
	$q->execute(array(
		"exchange" => $exchange['name'],
		"currency1" => $cur1,
		"currency2" => $cur2,
		"last_trade" => $values['last_trade'],
		/*
		 * The 'bid' price is the highest price that a buyer is willing to pay (i.e. the 'sell');
		 * the 'ask' price is the lowest price that a seller is willing to sell (i.e. the 'buy').
		 * Therefore bid <= ask, buy <= sell.
		 */
		"bid" => $values['bid'],
		"ask" => $values['ask'],
		"volume" => $values['volume'],
		"job_id" => $job['id'],
	));

	$last_id = db()->lastInsertId();
	crypto_log("Inserted new ticker id=" . $last_id);

	// put into the most recent table
	// TODO could also use a REPLACE statement
	$q = db_master()->prepare("SELECT * FROM ticker_recent WHERE exchange=:exchange AND currency1=:currency1 AND currency2=:currency2 LIMIT 1");
	$q->execute(array(
		"exchange" => $exchange['name'],
		"currency1" => $cur1,
		"currency2" => $cur2,
	));
	if (!$q->fetch()) {
		// insert in a new blank value (this will not occur very often)
		$q = db_master()->prepare("INSERT INTO ticker_recent SET exchange=:exchange, currency1=:currency1, currency2=:currency2");
		$q->execute(array(
			"exchange" => $exchange['name'],
			"currency1" => $cur1,
			"currency2" => $cur2,
		));
	}

	// update the previously existing recent value
	$q = db()->prepare("UPDATE ticker_recent SET created_at=NOW(), last_trade=:last_trade, bid=:bid, ask=:ask, volume=:volume, job_id=:job_id
			WHERE exchange=:exchange AND currency1=:currency1 AND currency2=:currency2");
	$q->execute(array(
		"last_trade" => $values['last_trade'],
		"bid" => $values['bid'],
		"ask" => $values['ask'],
		"volume" => $values['volume'],
		"job_id" => $job['id'],
		"exchange" => $exchange['name'],
		"currency1" => $cur1,
		"currency2" => $cur2,
	));

	// all other data from today is now old
	// NOTE if the system time changes between the next two commands, then we may erraneously
	// specify that there is no valid daily data. one solution is to specify NOW() as $created_at rather than
	// relying on MySQL
	$q = db()->prepare("UPDATE ticker SET is_daily_data=0 WHERE is_daily_data=1 AND exchange=:exchange AND currency1=:currency1 AND currency2=:currency2 AND
		created_at_day = TO_DAYS(NOW()) AND id <> :id");
	$q->execute(array(
		"exchange" => $exchange['name'],
		"currency1" => $cur1,
		"currency2" => $cur2,
		"id" => $last_id,
	));

}
