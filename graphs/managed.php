<?php

/* functionality relating to managed graphs */

/**
 * Calculate all of the graphs that would be created for a given user,
 * with a given graph calculation strategy.
 * @param $strategy 'auto', 'managed' or '' (use user-defined)
 */
function calculate_user_graphs($user, $strategy = false) {
	if ($strategy === false) {
		if (!$user['graph_managed_type']) {
			throw new ManagedGraphException("User has no managed graph type, so cannot select user-defined strategy");
		}
		return calculate_user_graphs($user, $user['graph_managed_type']);
	}

	$managed = calculate_all_managed_graphs($user);

	// merge them all together based on user preferences
	$categories = array();
	if ($strategy == "managed") {
		$q = db()->prepare("SELECT * FROM managed_graphs WHERE user_id=?");
		$q->execute(array($user['id']));
		while ($m = $q->fetch()) {
			$categories[] = $m['preference'];
		}
	} else {
		// default categories for auto
		$categories = get_auto_managed_graph_categories();
	}

	// merge all graphs based on categories
	$result = array();
	foreach ($categories as $key) {
		if (isset($managed[$key])) {
			$result = array_merge($result, $managed[$key]);
		}
	}

	// sort by order
	uasort($result, '_sort_by_order_key');

	return $result;
}

/**
 * Calculate all of the different types of managed graphs that
 * may be provided to a given user, in each category of managed
 * graphs (see get_managed_graph_categories()).
 */
function calculate_all_managed_graphs($user) {
	$result = array();

	$summaries = get_all_summary_currencies();
	$all_summaries = get_all_summaries();
	$currencies = get_all_currencies();
	$accounts = account_data_grouped();
	$wallets = get_supported_wallets();

	$order_currency = array();
	foreach (get_all_currencies() as $c) {
		$order_currency[$c] = count($order_currency);
	}
	$order_exchange = array();
	foreach (get_all_exchanges() as $key => $label) {
		$order_exchange[$key] = count($order_exchange) * 10;
	}

	$default_order = array(
		'composition_pie' => 0,
		'balances_table' => 1000,
		'exchange_daily' => 2000,
		'total_daily' => 3000,
		'all_daily' => 4000,
		'composition_daily' => 5000,
		'hashrate_daily' => 6000,
	);

	$result['summary'] = array();
	$result['all_summary'] = array();
	$result['summary']['balances_table'] = array(
		'order' => $default_order['balances_table'],
	);
	foreach (get_all_cryptocurrencies() as $cur) {
		if (isset($summaries[$cur])) {
			$result['summary']["composition_" . $cur . "_pie"] = array(
				'order' => $default_order['composition_pie'] + $order_currency[$cur],
			);
			$result['summary']["composition_" . $cur . "_daily"] = array(
				'order' => $default_order['composition_daily'] + $order_currency[$cur],
			);
			$result['summary']['total_' . $cur . '_daily'] = array(
				'order' => $default_order['total_daily'] + $order_currency[$cur],
				'source' => $cur,
			);
		}
	}

	$result['currency'] = array();
	$result['all_currency'] = array();
	foreach (get_exchange_pairs() as $exchange => $pairs) {
		foreach ($pairs as $pair) {

			// we are interested in both of these currencies
			if (isset($summaries[$pair[0]]) && isset($summaries[$pair[1]])) {

				// and one of these currencies are a preferred currency
				if ($pair[0] == $user['preferred_crypto'] || $pair[0] == $user['preferred_fiat'] ||
					$pair[1] == $user['preferred_crypto'] || $pair[1] == $user['preferred_fiat']) {

					// and we have a summary instance for this pair somewhere
					$possible_summaries = array('summary_' . $pair[0] . '_' . $exchange, 'summary_' . $pair[1] . '_' . $exchange);
					if (in_array($pair[0], get_all_cryptocurrencies())) {
						$possible_summaries[] = "summary_" . $pair[0];
					}
					if (in_array($pair[1], get_all_cryptocurrencies())) {
						$possible_summaries[] = "summary_" . $pair[1];
					}
					foreach ($possible_summaries as $p) {
						if (isset($all_summaries[$p])) {
							$is_default = (in_array($pair[0], get_all_fiat_currencies()) && get_default_currency_exchange($pair[0]) == $exchange) ||
									(in_array($pair[1], get_all_fiat_currencies()) && get_default_currency_exchange($pair[1]) == $exchange);

							$result['all_currency'][$exchange . "_" . $pair[0] . $pair[1] . "_daily"] = array(
								'order' => $default_order['exchange_daily'] + $order_exchange[$exchange] + $order_currency[$pair[0]],
								'source' => $p,
							);
							if ($is_default) {
								$result['currency'][$exchange . "_" . $pair[0] . $pair[1] . "_daily"] = array(
									'order' => $default_order['exchange_daily'] + $order_exchange[$exchange] + $order_currency[$pair[0]],
									'source' => $p,
								);
							}

							// don't display all2btc etc
							if (!in_array(substr($p, strlen("summary_")), get_all_cryptocurrencies())) {
								$result['all_summary']['all2' . substr($p, strlen("summary_")) . '_daily'] = array(
									'order' => $default_order['all_daily'] + $order_exchange[$exchange] + $order_currency[$pair[0]],
									'source' => $p,
								);
								if ($is_default) {
									$result['summary']['all2' . substr($p, strlen("summary_")) . '_daily'] = array(
										'order' => $default_order['all_daily'] + $order_exchange[$exchange] + $order_currency[$pair[0]],
										'source' => $p,
									);
								}
							}
							break;
						}
					}
				}
			}
		}
	}

	$result['securities'] = array();
	// no graphs to put in here yet...
	// TODO in the future: securities composition graphs? e.g. composition_litecoinglobal_daily

	$result['mining'] = array();
	foreach (get_all_hashrate_currencies() as $cur) {
		if (isset($summaries[$cur])) {
			// we need to have at least one pool that supports reporting hashrate
			$has_hashing_account = false;
			foreach ($accounts['Mining pools'] as $key => $account) {
				if (!isset($wallets[$key])) {
					continue;
				}
				$instances = get_all_user_account_instances($key);
				if ($instances) {
					if (in_array('hash', $wallets[$key])) {
						$has_hashing_account = $key;
					}
				}
			}

			if (!$has_hashing_account)
				continue;

			$result['mining']["hashrate_" . $cur . "_daily"] = array(
				'order' => $default_order['hashrate_daily'] + $order_currency[$cur],
				'source' => $has_hashing_account,
			);
		}
	}

	// all 'summary' are also 'all_summary' etc
	foreach ($result['summary'] as $key => $value) {
		$result['all_summary'][$key] = $value;
	}

	foreach ($result['currency'] as $key => $value) {
		$result['all_currency'][$key] = $value;
	}

	// go through each category and sort by order
	foreach ($result as $key => $value) {
		uasort($result[$key], '_sort_by_order_key');
	}

	return $result;

}

function _sort_by_order_key($a, $b) {
	if ($a['order'] == $b['order']) {
		return 0;
	}
	return $a['order'] > $b['order'] ? 1 : -1;
}

$global_get_all_user_account_instances = array();
function get_all_user_account_instances($account_key) {
	global $get_all_user_account_instances;
	if (!isset($get_all_user_account_instances[$account_key])) {
		$accounts = account_data_grouped();
		foreach ($accounts as $label => $group) {
			foreach ($group as $key => $value) {
				if ($key == $account_key) {
					$q = db()->prepare("SELECT * FROM " . $value['table'] . " WHERE user_id=?");
					$q->execute(array(user_id()));
					$get_all_user_account_instances[$account_key] = $q->fetchAll();
				}
			}
		}
		if (!isset($get_all_user_account_instances[$account_key])) {
			throw new Exception("No account type '$account_key' defined");
		}
	}
	return $get_all_user_account_instances[$account_key];
}

function get_managed_graph_categories() {
	return array(
		'summary' => 'Portfolio summary',
		'all_summary' => 'Portfolio summary (detailed)',
		'currency' => 'Currency exchange',
		'all_currency' => 'Currency exchange (detailed)',
		'securities' => 'Securities and investments',
		'mining' => 'Cryptocurrency mining',
	);
}

// these can change at any time post-deployment, so if users select 'auto' they will
// automatically receive new graph categories
function get_auto_managed_graph_categories() {
	return array(
		'summary',
		'currency',
		'securities',
		'mining',
	);
}

class ManagedGraphException extends GraphException { }
