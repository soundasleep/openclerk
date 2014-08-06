<?php

class GraphRenderer_Ticker extends GraphRenderer {

	var $exchange;
	var $currency1;
	var $currency2;

	public function __construct($exchange, $currency1, $currency2) {
		$this->exchange = $exchange;
		$this->currency1 = $currency1;
		$this->currency2 = $currency2;
	}

	public function getTitle() {
		return get_exchange_name($this->exchange) . " " . get_currency_abbr($this->currency1) . "/" . get_currency_abbr($this->currency2);
	}

	public function getURL() {
		return url_for('historical', array(
			'id' => $this->exchange . '_' . $this->currency1 . $this->currency2 . '_daily',
			'days' => 180,
		));
	}

	public function getLabel() {
		return ct("View historical data");
	}

	public function getData($days) {
		$columns = array();

		$columns[] = array('type' => 'date', 'title' => ct("Date"));
		$columns[] = array('type' => 'number', 'title' => ct(":pair Bid"), 'args' => array('pair' => get_currency_abbr($this->currency1) . "/" . get_currency_abbr($this->currency2)));
		$columns[] = array('type' => 'number', 'title' => ct(":pair Ask"), 'args' => array('pair' => get_currency_abbr($this->currency1) . "/" . get_currency_abbr($this->currency2)));

		if ($this->exchange == 'themoneyconverter' || $this->exchange == "coinbase") {
			// hack fix because TheMoneyConverter and Coinbase only have last_trade
			// TODO this should maybe be in a separate class, e.g. BidAskTicker and LastTradeTicker
			throw new GraphException("Cannot support themoneyconverter or coinbase yet");
		}

		// TODO extra_days_necessary
		$extra_days = 10;

		$sources = array(
			// cannot use 'LIMIT :limit'; PDO escapes :limit into string, MySQL cannot handle or cast string LIMITs
			// first get summarised data
			array('query' => "SELECT * FROM graph_data_ticker WHERE exchange=:exchange AND
				currency1=:currency1 AND currency2=:currency2 AND data_date > DATE_SUB(NOW(), INTERVAL " . ($days + $extra_days) . " DAY) ORDER BY data_date DESC", 'key' => 'data_date'),
			// and then get more recent data
			array('query' => "SELECT * FROM ticker WHERE is_daily_data=1 AND exchange=:exchange AND
				currency1=:currency1 AND currency2=:currency2 ORDER BY created_at DESC LIMIT " . ($days + $extra_days), 'key' => 'created_at'),
		);

		$args = array(
			'exchange' => $this->exchange,
			'currency1' => $this->currency1,
			'currency2' => $this->currency2,
		);

		$data = array();
		$last_updated = false;
		foreach ($sources as $source) {
			$q = db()->prepare($source['query']);
			$q->execute($args);
			while ($ticker = $q->fetch()) {
				$data_key = date('Y-m-d', strtotime($ticker[$source['key']]));
				$data[$data_key] = array(
					graph_number_format($ticker['bid']),
					graph_number_format($ticker['ask']),
				);
				$last_updated = max($last_updated, strtotime($ticker['created_at']));
			}
		}

		// sort by key, but we only want values
		uksort($data, 'cmp_time_reverse');

		return array(
			'columns' => $columns,
			'data' => $data,
			'last_updated' => $last_updated,
		);

	}

}