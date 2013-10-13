<?php

require_once("graphs/util.php");
require_once("graphs/types.php");

/* functionality relating to managed graphs */

/**
 * Calculate all of the graphs that would be created for a given user,
 * with a given graph calculation strategy.
 * @param $strategy 'auto', 'managed' or '' (use user-defined)
 * @param $managed an array of categories or use, or empty to use user-defined
 */
function calculate_user_graphs($user, $strategy = false, $categories = array()) {
	if ($strategy === false) {
		if (!$user['graph_managed_type']) {
			throw new ManagedGraphException("User has no managed graph type, so cannot select user-defined strategy");
		}
		return calculate_user_graphs($user, $user['graph_managed_type'], $categories);
	}

	$managed = calculate_all_managed_graphs($user);

	// merge them all together based on user preferences
	if ($strategy == "managed") {
		if (!$categories) {
			$q = db()->prepare("SELECT * FROM managed_graphs WHERE user_id=?");
			$q->execute(array($user['id']));
			while ($m = $q->fetch()) {
				$categories[] = $m['preference'];
			}
		}
	} else {
		// default categories for auto
		$categories = get_auto_managed_graph_categories();
	}

	// merge all graphs based on categories
	$result = array();
	foreach ($categories as $key) {
		if (isset($managed[$key])) {
			foreach ($managed[$key] as $graph_key => $graph) {
				// remove any graphs that are not free priorities for non-premium users
				if ($user['is_premium'] || (isset($graph['free']) && $graph['free'])) {
					$result[$graph_key] = $graph;
				}
			}
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
		'btc_equivalent' => -1000,
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
		'width' => get_site_config('default_user_graph_height'),	// square
		'free' => true,		// free user priority
	);
	if (count($summaries) >= 2 && isset($summaries['btc'])) {
		$result['summary']['btc_equivalent'] = array(
			'order' => $default_order['btc_equivalent'],
			'width' => get_site_config('default_user_graph_height'),	// square
			'free' => true,		// free user priority
		);
	}
	foreach (get_all_cryptocurrencies() as $cur) {
		if (isset($summaries[$cur])) {
			$result['summary']["composition_" . $cur . "_pie"] = array(
				'order' => $default_order['composition_pie'] + $order_currency[$cur],
				'width' => get_site_config('default_user_graph_height'),	// square
				'free' => $cur == $user['preferred_crypto'],		// free user priority
			);
			$result['summary']["composition_" . $cur . "_daily"] = array(
				'order' => $default_order['composition_daily'] + $order_currency[$cur],
				'free' => $cur == $user['preferred_crypto'],		// free user priority
			);
			$result['summary']['total_' . $cur . '_daily'] = array(
				'order' => $default_order['total_daily'] + $order_currency[$cur],
				'source' => $cur,
				'free' => $cur == $user['preferred_crypto'],		// free user priority
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
									'free' => true,		// free user priority
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
										'free' => ($pair[0] == $user['preferred_crypto'] || $pair[0] == $user['preferred_fiat']),		// free user priority
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
				'free' => $cur == $user['preferred_crypto'],		// free user priority
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
		// 'securities' => 'Securities and investments',
		'mining' => 'Cryptocurrency mining',
	);
}

// these can change at any time post-deployment, so if users select 'auto' they will
// automatically receive new graph categories
function get_auto_managed_graph_categories() {
	return array(
		'summary',
		'currency',
		// 'securities',
	);
}

class ManagedGraphException extends GraphException { }

/**
 * Update all of the managed graphs of the given user.
 * This handles both 'auto' and 'managed' graph types.
 * This does not automatically delete all of the graphs on a user page - this is done in wizard_reports_post if necessary.
 * But it will delete any graphs that shouldn't be here. This way, any modified graphs should retain their changes
 * (especially if the graphs are 'managed' and not 'auto').
 */
function update_user_managed_graphs($user) {
	global $messages;

	// find all of the graphs this user should have
	$managed = calculate_user_graphs($user);

	// does this user at least have a graph page?
	$q = db()->prepare("SELECT * FROM graph_pages WHERE user_id=? AND is_removed=0 ORDER BY page_order ASC, id ASC");
	$q->execute(array($user['id']));
	$page = $q->fetch();
	if (!$page) {
		// insert a new page
		$q = db()->prepare("INSERT INTO graph_pages SET user_id=?, title=?, is_managed=1");
		$q->execute(array($user['id'], "Summary"));
		$page = array('id' => db()->lastInsertId());
		if (is_admin()) {
			$messages[] = "(admin) Added new graph_page " . htmlspecialchars($page['id']) . ".";
		}
	}

	// get all the graphs on this page
	$q = db()->prepare("SELECT * FROM graphs WHERE page_id=?");
	$q->execute(array($page['id']));
	$graphs = $q->fetchAll();
	$graphs_added = 0;
	$graphs_deleted = 0;

	// go through each managed graph, and see if we already have one defined
	foreach ($managed as $key => $config) {
		$found_graph = false;
		foreach ($graphs as $graph) {
			if ($graph['graph_type'] == $key) {
				$found_graph = true;
			}
		}
		if ($found_graph)
			continue;

		// no - we need to insert a new one
		$q = db()->prepare("INSERT INTO graphs SET graph_type=:graph_type,
			width=:width,
			height=:height,
			page_order=:page_order,
			days=:days,
			page_id=:page_id,
			is_managed=1");
		$q->execute(array(
			"graph_type" => $key,
			"width" => isset($config['width']) ? $config['width'] : get_site_config('default_user_graph_width'),
			"height" => isset($config['height']) ? $config['height'] : get_site_config('default_user_graph_height'),
			"page_order" => $config['order'],
			"page_id" => $page['id'],
			"days" => isset($config['height']) ? $config['height'] : get_site_config('default_user_graph_days'),
		));
		$graphs_added++;
	}

	// go through each existing graph, and remove any graphs that shouldn't be here
	foreach ($graphs as $graph) {
		$found_graph = false;
		foreach ($managed as $key => $config) {
			if ($graph['graph_type'] == $key) {
				$found_graph = true;
			}
		}
		if ($found_graph)
			continue;

		// no - we need to delete this graph
		$q = db()->prepare("DELETE FROM graphs WHERE id=?");
		$q->execute(array($graph['id']));
		$graphs_deleted++;
	}

	if (is_admin()) {
		$messages[] = "Added " . plural($graphs_added, "graph") . ($graphs_deleted ? " and removed " . plural($graphs_deleted, "graph") : "") . ".";
	}

	// finally, update the needs_managed_update flag
	$q = db()->prepare("UPDATE users SET needs_managed_update=0, last_managed_update=NOW() WHERE id=?");
	$q->execute(array($user['id']));
}