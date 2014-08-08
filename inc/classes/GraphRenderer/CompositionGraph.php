<?php

class GraphRenderer_CompositionGraph extends GraphRenderer_AbstractCompositionGraph {

	public function getTitle() {
		return ct("All :currency balances");
	}

	public function getTitleArgs() {
		return array(
			':currency' => get_currency_abbr($this->currency),
		);
	}

	function getCompositionSources($days, $extra_days) {
		return array(
			// we can't LIMIT by days here, because we may have many accounts for one exchange
			// first get summarised data
			array('query' => "SELECT * FROM graph_data_balances WHERE user_id=:user_id AND currency=:currency
				AND data_date > DATE_SUB(NOW(), INTERVAL " . ($days + $extra_days) . " DAY) ORDER BY data_date DESC", 'key' => 'data_date', 'balance_key' => 'balance_closing'),
			// and then get more recent data
			array('query' => "SELECT * FROM balances WHERE is_daily_data=1 AND currency=:currency
				AND user_id=:user_id AND created_at >= DATE_SUB(NOW(), INTERVAL " . ($days + $extra_days) . " DAY) ORDER BY created_at DESC", 'key' => 'created_at', 'balance_key' => 'balance'),
			// also include blockchain balances
			// first get summarised data
			array('query' => "SELECT *, 'blockchain' AS exchange FROM graph_data_summary WHERE user_id=:user_id AND summary_type=CONCAT('blockchain', :currency) AND
				data_date >= DATE_SUB(NOW(), INTERVAL " . ($days + $extra_days) . " DAY) ORDER BY data_date DESC", 'key' => 'data_date', 'balance_key' => 'balance_closing'),
			// and then get more recent data
			array('query' => "SELECT *, 'blockchain' AS exchange FROM summary_instances WHERE is_daily_data=1 AND summary_type=CONCAT('blockchain', :currency) AND
				user_id=:user_id ORDER BY created_at DESC LIMIT " . ($days + $extra_days), 'key' => 'created_at', 'balance_key' => 'balance'),
			// also include offset balances
			// first get summarised data
			array('query' => "SELECT *, 'offsets' AS exchange FROM graph_data_summary WHERE user_id=:user_id AND summary_type=CONCAT('offsets', :currency) AND
				data_date >= DATE_SUB(NOW(), INTERVAL " . ($days + $extra_days) . " DAY) ORDER BY data_date DESC", 'key' => 'data_date', 'balance_key' => 'balance_closing'),
			// and then get more recent data
			array('query' => "SELECT *, 'offsets' AS exchange FROM summary_instances WHERE is_daily_data=1 AND summary_type=CONCAT('offsets', :currency) AND
				user_id=:user_id ORDER BY created_at DESC LIMIT " . ($days + $extra_days), 'key' => 'created_at', 'balance_key' => 'balance'),
		);
	}

	function getCompositionArgs() {
		return array(
			'user_id' => $this->getUser(),
			'currency' => $this->currency,
		);

	}

	/**
	 * e.g. {@link #get_exchange_name()}
	 */
	function getHeadingTitle($key, $args) {
		return get_exchange_name($key);
	}

}
