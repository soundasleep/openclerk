<?php

/**
 * TODO refactor into EquivalentXXX (any currency)
 */
class GraphRenderer_EquivalentPieBTC extends GraphRenderer {

	public function __construct() {
		parent::__construct();
	}

	public function requiresUser() {
		return true;
	}

	public function getTitle() {
		return ct("Equivalent BTC");
	}

	public function canHaveTechnicals() {
		// do not try to calculate technicals; this also resorts the data by first key
		return false;
	}

	public function getChartType() {
		return "piechart";
	}

	function usesDays() {
		return false;
	}

	function usesSummaries() {
		return true;
	}

	public function getData($days) {

		$key_column = array('type' => 'string', 'title' => ct("Currency"));

		$columns = array();

		// get all balances
		$balances = get_all_summary_instances($this->getUser());

		$last_updated = find_latest_created_at($balances, "total");

		// and convert them using the most recent rates
		$rates = get_all_recent_rates();

		// create data
		// TODO refactor this into generic any-currency balances
		$data = array();
		if (isset($balances['totalbtc']) && $balances['totalbtc']['balance'] != 0) {
			$columns[] = array('type' => 'number', 'title' => get_currency_abbr('btc'));
			$data[] = graph_number_format(demo_scale($balances['totalbtc']['balance']));
		}
		foreach (get_all_currencies() as $cur) {
			if ($cur == 'btc') continue;
			if (!is_fiat_currency($cur) && isset($balances['total' . $cur]) && $balances['total' . $cur]['balance'] != 0 && isset($rates['btc' . $cur])) {
				$columns[] = array('type' => 'number', 'title' => get_currency_abbr($cur));
				$data[] = graph_number_format(demo_scale($balances['total' . $cur]['balance'] * $rates['btc' . $cur]['bid']));
			}
			if (is_fiat_currency($cur) && isset($balances['total' . $cur]) && $balances['total' . $cur]['balance'] != 0 && isset($rates[$cur . 'btc']) && $rates[$cur . 'btc'] /* no div by 0 */) {
				$columns[] = array('type' => 'number', 'title' => get_currency_abbr($cur));
				$data[] = graph_number_format(demo_scale($balances['total' . $cur]['balance'] / $rates[$cur . 'btc']['ask']));
			}
		}

		// sort data by balance
		arsort($data);
		$data = array(get_currency_abbr('btc') => $data);

		return array(
			'key' => $key_column,
			'columns' => $columns,
			'data' => $data,
			'last_updated' => $last_updated,
		);

	}

}
