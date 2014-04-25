<?php

/**
 * Process a single account for transactions.
 */

$q = db()->prepare("SELECT * FROM transaction_creators WHERE id=?");
$q->execute(array($job['arg_id']));
$creator = $q->fetch();

if (!$creator) {
	throw new JobException("Could not find any creator " . $job['arg_id'] . " for user " . $job['user_id']);
}

$account_data = get_account_data($creator['exchange']);
$q = db()->prepare("SELECT * FROM " . $account_data['table'] . " WHERE id=? AND user_id=?");
$q->execute(array($creator['account_id'], $job['user_id']));
$account = $q->fetch();

if (!$account) {
	throw new JobException("Could not find any " . $creator['exchange'] . " account ID " . $creator['account_id'] . " for user " . $job['user_id']);
}

crypto_log("Investigating account " . $account['id'] . " " . $creator['exchange']);

// for every currency this exchange should supports
$get_supported_wallets = get_supported_wallets();

// add all blockchain wallets
$account_data_grouped = account_data_grouped();
foreach ($account_data_grouped['Addresses'] as $exchange => $data) {
	$get_supported_wallets[$exchange] = array($data['currency'], '_is_currency_');
}

$cursor = false;
if (!isset($get_supported_wallets[$account_data['title_key']])) {
	crypto_log("No supported wallets for " . $account_data['title_key']);
} else {
	foreach ($get_supported_wallets[$account_data['title_key']] as $currency) {
		if ($currency == 'hash' || $currency == '_is_currency_')
			continue;

		$is_address_balance = false;

		crypto_log("Processing currency $currency");
		if (in_array('_is_currency_', $get_supported_wallets[$account_data['title_key']])) {
			crypto_log("This is an address account: using address_balances rather than balances");
			$is_address_balance = true;
		}

		$cursor = false;
		if ($creator['transaction_cursor']) {
			if ($is_address_balance) {
				// select the cursor value
				$q = db()->prepare("SELECT * FROM address_balances WHERE is_daily_data=1 AND user_id=:user_id AND address_id=:address_id
					AND created_at_day <= :cursor ORDER BY created_at_day DESC LIMIT 1");
				$q->execute(array(
					"user_id" => $account['user_id'],
					"address_id" => $account['id'],
					"cursor" => $creator['transaction_cursor'],
				));
				$cursor = $q->fetch();
			} else {
				// select the cursor value
				$q = db()->prepare("SELECT * FROM balances WHERE is_daily_data=1 AND user_id=:user_id AND account_id=:account_id AND exchange=:exchange AND currency=:currency
					AND created_at_day <= :cursor ORDER BY created_at_day DESC LIMIT 1");
				$q->execute(array(
					"user_id" => $account['user_id'],
					"account_id" => $account['id'],
					"exchange" => $creator['exchange'],
					"currency" => $currency,
					"cursor" => $creator['transaction_cursor'],
				));
				$cursor = $q->fetch();

				// is the cursor older than recent data?
				if (!$cursor) {
					crypto_log("Searching graph_data_balances for older cursor than " . $creator['transaction_cursor']);
					$q = db()->prepare("SELECT exchange, account_id, currency, balance_closing AS balance, data_date AS created_at, data_date_day AS created_at_day
							FROM graph_data_balances WHERE user_id=:user_id AND account_id=:account_id AND exchange=:exchange AND currency=:currency
							AND data_date_day <= :cursor ORDER BY data_date_day DESC LIMIT 1");
					$q->execute(array(
						"user_id" => $account['user_id'],
						"account_id" => $account['id'],
						"exchange" => $creator['exchange'],
						"currency" => $currency,
						"cursor" => $creator['transaction_cursor'],
					));
					$cursor = $q->fetch();
				}
			}
		}

		// use the cursor to select new balances
		$balances = array();

		if ($is_address_balance) {
			$q = db()->prepare("SELECT *, :currency AS currency FROM address_balances WHERE is_daily_data=1 AND user_id=:user_id AND address_id=:address_id
					AND created_at_day > :cursor AND created_at_day <= TO_DAYS(DATE_SUB(NOW(), INTERVAL 1 DAY))");
			$q->execute(array(
				"user_id" => $account['user_id'],
				"address_id" => $account['id'],
				"currency" => $currency,
				"cursor" => $creator['transaction_cursor'],
			));
			$balances += $q->fetchAll();

		} else {
			$q = db()->prepare("SELECT exchange, account_id, currency, balance_closing AS balance, data_date AS created_at, data_date_day AS created_at_day
					FROM graph_data_balances WHERE user_id=:user_id AND account_id=:account_id AND exchange=:exchange AND currency=:currency
					AND data_date_day > :cursor AND data_date_day <= TO_DAYS(DATE_SUB(NOW(), INTERVAL 1 DAY))");
			$q->execute(array(
				"user_id" => $account['user_id'],
				"account_id" => $account['id'],
				"exchange" => $creator['exchange'],
				"currency" => $currency,
				"cursor" => $creator['transaction_cursor'],
			));
			$balances += $q->fetchAll();

			$q = db()->prepare("SELECT * FROM balances WHERE is_daily_data=1 AND user_id=:user_id AND account_id=:account_id AND exchange=:exchange AND currency=:currency
					AND created_at_day > :cursor AND created_at_day <= TO_DAYS(DATE_SUB(NOW(), INTERVAL 1 DAY))");
			$q->execute(array(
				"user_id" => $account['user_id'],
				"account_id" => $account['id'],
				"exchange" => $creator['exchange'],
				"currency" => $currency,
				"cursor" => $creator['transaction_cursor'],
			));
			$balances += $q->fetchAll();
		}

		crypto_log("Processing " . number_format(count($balances)) . " balances");

		foreach ($balances as $balance) {
			$transaction = false;
			if (!$cursor && $balance['balance'] != 0) {
				// this is a brand new account
				crypto_log($balance['created_at'] . ": New cursor at " . $balance['balance']);
				$transaction = array(
					'user_id' => $account['user_id'],
					'transaction_date' => $balance['created_at'],
					'exchange' => $creator['exchange'],
					'account_id' => $account['id'],
					'currency1' => $balance['currency'],
					'value1' => $balance['balance'],
				);
			} else if ((float) $balance['balance'] != (float) $cursor['balance']) {
				// this is a transaction that has changed balances
				crypto_log($balance['created_at'] . ": Balance is now " . $balance['balance'] . " from " . $cursor['balance']);
				$transaction = array(
					'user_id' => $account['user_id'],
					'transaction_date' => $balance['created_at'],
					'exchange' => $creator['exchange'],
					'account_id' => $account['id'],
					'currency1' => $balance['currency'],
					'value1' => $balance['balance'] - $cursor['balance'],
				);
			}

			if ($transaction) {
				$q = db()->prepare("INSERT INTO transactions SET user_id=:user_id, is_automatic=1,
					transaction_date=:transaction_date,
					transaction_date_day=TO_DAYS(:transaction_date),
					exchange=:exchange,
					account_id=:account_id,
					currency1=:currency1,
					value1=:value1");
				$q->execute($transaction);
				crypto_log("Added transaction " . db()->lastInsertId());
			}

			$cursor = $balance;
		}

		// TODO
	}

	// update transaction cursor
	if ($cursor) {
		$q = db()->prepare("UPDATE transaction_creators SET transaction_cursor=? WHERE id=?");
		$q->execute(array($cursor['created_at_day'], $creator['id']));
		crypto_log("Updated cursor to " . $cursor['created_at_day']);
	}

	crypto_log("Complete.");
}
