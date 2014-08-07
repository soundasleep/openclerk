<?php

class GraphRenderer_ExternalHistorical extends GraphRenderer {

	var $job_type;
	var $arg0;
	var $currency2;

	public function __construct($job_type = false, $arg0 = false) {
		$this->job_type = $job_type;
		$this->arg0 = $arg0;
		if (!$this->arg0 && !$this->job_type) {
			throw new GraphException("ExternalHistorical graph requires arg0 or job_type");
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

	public function getData($days) {
		if (!$this->job_type) {
			$q = db()->prepare("SELECT * FROM external_status_types WHERE id=?");
			$q->execute(array($this->arg0));
			$resolved = $q->fetch();
			if (!$resolved) {
				throw new GraphException("Invalid external status type ID.");
			$this->job_type = $resolved['job_type'];
			}
		}

		$columns = array();

		$columns[] = array('type' => 'date', 'title' => ct("Date"));
		$columns[] = array('type' => 'number', 'title' => ct(":% success"), 'min' => 0, 'max' => 100);

		// TODO extra_days_necessary
		$extra_days = 10;

		$sources = array(
			// cannot use 'LIMIT :limit'; PDO escapes :limit into string, MySQL cannot handle or cast string LIMITs
			// TODO first get summarised data
			// and then get more recent data
			array('query' => "SELECT * FROM external_status WHERE job_type=:job_type
				AND created_at >= DATE_SUB(NOW(), INTERVAL " . ($days + $extra_days) . " DAY)
				ORDER BY is_recent ASC, created_at DESC", 'key' => 'created_at', 'balance_key' => 'balance'),
		);

		$args = array(
			'job_type' => $this->job_type,
		);

		$data = array();
		$last_updated = false;
		foreach ($sources as $source) {
			$q = db()->prepare($source['query']);
			$q->execute($args);
			while ($ticker = $q->fetch()) {
				$data_key = date('Y-m-d', strtotime($ticker[$source['key']]));
				$data[$data_key] = array(
					graph_number_format(100 * (1 - ($ticker['job_errors'] / $ticker['job_count'])))
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
