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

	// TODO implement
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

	$result['summary'] = array();
	$result['summary'][] = "composition_" . $user['preferred_crypto'] . "_pie";

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
							$result['all_currency'][] = $exchange . "_" . $pair[0] . $pair[1] . "_daily";
							if ((in_array($pair[0], get_all_fiat_currencies()) && get_default_currency_exchange($pair[0]) == $exchange) ||
								(in_array($pair[1], get_all_fiat_currencies()) && get_default_currency_exchange($pair[1]) == $exchange)) {
								$result['currency'][] = $exchange . "_" . $pair[0] . $pair[1] . "_daily";
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
						$has_hashing_account = true;
					}
				}
			}

			if (!$has_hashing_account)
				continue;

			$result['mining'][] = "hashrate_" . $cur . "_daily";
		}
	}

	return $result;

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
		'currency' => 'Currency exchange',
		'all_currency' => 'All currency exchanges',
		'securities' => 'Securities and investments',
		'mining' => 'Cryptocurrency mining',
	);
}

class ManagedGraphException extends GraphException { }
