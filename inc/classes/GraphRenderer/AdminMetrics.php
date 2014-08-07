<?php

class GraphRenderer_AdminMetrics extends GraphRenderer {

	/*

function render_metrics_db_slow_queries_graph($graph) {
	return render_metrics_graph($graph, 'db_slow_queries', 'performance_report_slow_queries', 'performance_metrics_queries', 'query_id', 'query');
}

function render_metrics_curl_slow_urls_graph($graph) {
	return render_metrics_graph($graph, 'curl_slow_urls', 'performance_report_slow_urls', 'performance_metrics_urls', 'url_id', 'url');
}

function render_metrics_graph($graph, $report_type, $report_table, $report_ref_table, $report_reference, $key_prefix, $key = null, $actual_value_key = null) {

	*/

	public static function getMetrics() {
		return array(
			'db_slow_queries_graph' => array(
				'report_type' => 'db_slow_queries',
				'report_table' => 'performance_report_slow_queries',
				'report_ref_table' => 'performance_metrics_queries',
				'report_reference' => 'query_id',
				'key_prefix' => 'query',
				'key' => null,
				'actual_value_key' => null,
				'title' => ct("Slowest DB queries (ms)"),
			),
		);
	}

	public function __construct($key) {
		$data = GraphRenderer_AdminMetrics::getMetrics();
		if (!isset($data[$key])) {
			throw new GraphException("Could not find any key '$key' in available metrics");
		}
		$data = $data[$key];

		$this->report_type = $data['report_type'];
		$this->report_table = $data['report_table'];
		$this->report_ref_table = $data['report_ref_table'];
		$this->report_reference = $data['report_reference'];
		$this->key_prefix = $data['key_prefix'];
		$this->key = $data['key'];
		if (!$this->key) {
			$this->key = $this->key_prefix;
		}
		$this->actual_value_key = $data['actual_value_key'];
		$this->title = $data['title'];
	}

	public function requiresAdmin() {
		return true;
	}

	public function getTitle() {
		return $this->title;
	}

	public function getTitleArgs() {
		return array(
		);
	}

	/*
	public function hasSubheading() {
		// do not try to calculate subheadings!
		return false;
	}

	public function canHaveTechnicals() {
		// do not try to calculate technicals; this also resorts the data by first key
		return false;
	}
	*/

	public function getChartType() {
		return "linechart";
	}

	public function getData($days) {
		$columns = array();

		$key_column = array('type' => 'date', 'title' => ct("Date"));

		$report_type = $this->report_type;
		$report_table = $this->report_table;
		$report_ref_table = $this->report_ref_table;
		$report_reference = $this->report_reference;
		$key_prefix = $this->key_prefix;
		$key = $this->key;
		$actual_value_key = $this->actual_value_key;

		$q = db()->prepare("SELECT * FROM performance_reports WHERE report_type=? ORDER BY id DESC LIMIT 30");
		$q->execute(array($report_type));
		$reports = $q->fetchAll();
		if (!$reports) {
			return render_text($graph, "No report $report_type found.");
		}

		// construct an array of (date => )
		$data = array();

		$keys = array();
		$last_updated = false;

		foreach ($reports as $report) {
			// get all queries
			$q = db()->prepare("SELECT * FROM $report_table AS r " .
					($report_ref_table ? "JOIN $report_ref_table AS q ON r.$report_reference=q.id " : "") .
					"WHERE report_id=?");
			$q->execute(array($report['id']));
			$date = date('Y-m-d H:i:s', strtotime($report['created_at']));
			$row = array();
			while ($query = $q->fetch()) {
				if (!isset($keys[$query[$key]])) {
					$keys[$query[$key]] = count($keys);
					$columns[] = array('type' => 'number', 'title' => $query[$key]);
				}
				if ($actual_value_key === null) {
					$row[$keys[$query[$key]]] = graph_number_format($query[$key_prefix . '_time'] / $query[$key_prefix . '_count']);
				} else {
					$row[$keys[$query[$key]]] = graph_number_format($query[$actual_value_key]);
				}
			}
			$data[$date] = $row;
			$last_updated = max($last_updated, strtotime($report['created_at']));
		}

		// fill in any missing rows, e.g. queries that may not have featured in certain reports
		foreach ($data as $date => $row) {
			foreach ($keys as $id) {
				if (!isset($row[$id])) $data[$date][$id] = 0;
			}

			// reindex everything to be numeric arrays, so they aren't output as JSON objects
			$data[$date] = array_values($data[$date]);
		}

		return array(
			'key' => $key_column,
			'columns' => $columns,
			'data' => $data,
			'last_updated' => $last_updated,
		);


	}

}
