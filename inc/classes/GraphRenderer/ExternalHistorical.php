<?php

class GraphRenderer_ExternalHistorical extends GraphRenderer_AbstractTicker {

	var $job_type;
	var $arg0;
	var $currency2;

	public function __construct($job_type = false, $arg0 = false) {
		$this->job_type = $job_type;
		$this->arg0 = $arg0;

		if (!$this->arg0 && !$this->job_type) {
			throw new GraphException("ExternalHistorical graph requires arg0 or job_type");
		}

		// TODO maybe move this out of initialisation
		if (!$this->job_type) {
			$q = db()->prepare("SELECT * FROM external_status_types WHERE id=?");
			$q->execute(array($this->arg0));
			$resolved = $q->fetch();
			if (!$resolved) {
				throw new GraphException("Invalid external status type ID.");
			}
			$this->job_type = $resolved['job_type'];
		}
	}

	public function getTitle() {
		$titles = get_external_apis_titles();
		if (!isset($titles[$this->job_type])) {
			throw new GraphException("No such external API type '$this->job_type'");
		}
		return $titles[$this->job_type];
	}

	public function getURL() {
		return url_for('external_historical', array(
			'type' => $this->job_type,
		));
	}

	public function getLabel() {
		return ct("View historical data");
	}


	/**
	 * @return an array of columns e.g. (type, title, args)
	 */
	function getTickerColumns() {
		$columns = array();
		$columns[] = array('type' => 'percent', 'title' => ct(":% success"), 'min' => 0, 'max' => 100);
		return $columns;
	}

	/**
	 * The sources must return 'created_at' column as well, for last_updated
	 * @return an array of queries e.g. (query, key = created_at/data_date)
	 */
	function getTickerSources($days, $extra_days) {
		return array(
			// cannot use 'LIMIT :limit'; PDO escapes :limit into string, MySQL cannot handle or cast string LIMITs
			// TODO first get summarised data
			// and then get more recent data
			array('query' => "SELECT * FROM external_status WHERE job_type=:job_type
				AND created_at >= DATE_SUB(NOW(), INTERVAL " . ($days + $extra_days) . " DAY)
				ORDER BY is_recent ASC, created_at DESC", 'key' => 'created_at', 'balance_key' => 'balance'),
		);
	}

	/**
	 * @return an array of arguments passed to each {@link #getTickerSources()}
	 */
	function getTickerArgs() {
		return array(
			'job_type' => $this->job_type,
		);
	}

	/**
	 * @return an array of 1..2 values of the values for the particular row,
	 * 			maybe formatted with {@link #graph_number_format()}.
	 */
	function getTickerData($row) {
		return array(
			graph_number_format(100 * (1 - ($row['job_errors'] / $row['job_count']))),
		);
	}

}
