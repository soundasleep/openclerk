<?php

class GraphRenderer_StatisticsSystemLoad extends GraphRenderer_AbstractTicker {

	var $prefix;

	/**
	 * @param $prefix "" or "db_"
	 */
	public function __construct($prefix) {
		parent::__construct();
		$this->prefix = $prefix;
	}

	public function getTitle() {
		switch ($this->prefix) {
			case "":
				return ct("System load (hours)");

			case "db_":
				return ct("Database system load (hours)");

			default:
				throw new GraphException("Unknown prefix " . $this->prefix);
		}
	}

	/**
	 * @return an array of columns e.g. (type, title, args)
	 */
	function getTickerColumns() {
		$columns = array();
		$columns[] = array('type' => 'number', 'title' => ct("1 min"), 'min' => 0, 'max' => 5);
		$columns[] = array('type' => 'number', 'title' => ct("5 min"), 'min' => 0, 'max' => 5);
		$columns[] = array('type' => 'number', 'title' => ct("15 min"), 'min' => 0, 'max' => 5);
		return $columns;
	}

	/**
	 * The sources must return 'created_at' column as well, for last_updated
	 * @return an array of queries e.g. (query, key = created_at/data_date)
	 */
	function getTickerSources($days, $extra_days) {
		return array(
			array('query' => "SELECT * FROM site_statistics
				ORDER BY created_at DESC LIMIT " . ($days + $extra_days), 'key' => 'created_at'),
		);
	}

	/**
	 * @return an array of arguments passed to each {@link #getTickerSources()}
	 */
	function getTickerArgs() {
		return array();
	}

	/**
	 * @return an array of 1..2 values of the values for the particular row,
	 * 			maybe formatted with {@link #graph_number_format()}.
	 */
	function getTickerData($row) {
		return array(
			graph_number_format($row[$this->prefix . 'system_load_1min']),
			graph_number_format($row[$this->prefix . 'system_load_5min']),
			graph_number_format($row[$this->prefix . 'system_load_15min']),
		);
	}

}

