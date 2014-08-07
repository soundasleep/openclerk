<?php

class GraphRenderer_AdminMetrics extends GraphRenderer {

	public static function getMetrics() {
		return array(
			'db_slow_queries_graph' => array(
				'report_type' => 'db_slow_queries',
				'report_table' => 'performance_report_slow_queries',
				'report_ref_table' => 'performance_metrics_queries',
				'report_reference' => 'query_id',
				'key_prefix' => 'query',
				'title' => ct("Slowest DB queries (ms)"),
				'label' => t("Slowest DB queries (graph)"),
				'description' => t("The slowest database queries represented as a graph over time."),
			),
			'curl_slow_urls_graph' => array(
				'report_type' => 'curl_slow_urls',
				'report_table' => 'performance_report_slow_urls',
				'report_ref_table' => 'performance_metrics_urls',
				'report_reference' => 'url_id',
				'key_prefix' => 'url',
				'title' => ct("Slowest CURL URLs (ms)"),
				'label' => t("Slowest CURL URLs (graph)"),
				'description' => t("The slowest CURL requests represented as a graph over time."),
			),
			'slow_jobs_graph' => array(
				'report_type' => 'jobs_slow',
				'report_table' => 'performance_report_slow_jobs',
				'key_prefix' => 'job',
				'key' => 'job_type',
				'title' => ct("Slowest jobs (ms)"),
				'label' => t("Slowest jobs (graph)"),
				'description' => t("The slowest jobs represented as a graph over time."),
			),
			'slow_jobs_database_graph' => array(
				'report_type' => 'jobs_slow',
				'report_table' => 'performance_report_slow_jobs',
				'key' => 'job_type',
				'actual_value_key' => 'job_database',
				'title' => ct("Slowest jobs database time (ms)"),
				'label' => t("Slowest jobs database time (graph)"),
				'description' => t("The time spent in the database on the slowest jobs represented as a graph over time."),
			),
			'slow_pages_graph' => array(
				'report_type' => 'pages_slow',
				'report_table' => 'performance_report_slow_pages',
				'key_prefix' => 'page',
				'key' => 'script_name',
				'title' => ct("Slowest pages (ms)"),
				'label' => t("Slowest pages (graph)"),
				'description' => t("The slowest pages represented as a graph over time."),
			),
			'slow_pages_database_graph' => array(
				'report_type' => 'pages_slow',
				'report_table' => 'performance_report_slow_pages',
				'key' => 'script_name',
				'actual_value_key' => 'page_database',
				'title' => ct("Slowest pages database time (ms)"),
				'label' => t("Slowest pages database time (graph)"),
				'description' => t("The time spent in the database on the slowest pages represented as a graph over time."),
			),
			'slow_graphs_graph' => array(
				'report_type' => 'graphs_slow',
				'report_table' => 'performance_report_slow_graphs',
				'key_prefix' => 'graph',
				'key' => 'graph_type',
				'title' => ct("Slowest graphs (ms)"),
				'label' => t("Slowest graphs (graph)"),
				'description' => t("The slowest graphs represented as a graph over time."),
			),
			'slow_graphs_database_graph' => array(
				'report_type' => 'graphs_slow',
				'report_table' => 'performance_report_slow_graphs',
				'key' => 'graph_type',
				'actual_value_key' => 'graph_database',
				'title' => ct("Slowest graphs database time (ms)"),
				'label' => t("Slowest graphs database time (graph)"),
				'description' => t("The time spent in the database on the slowest graphs represented as a graph over time."),
			),
			'slow_graphs_count_graph' => array(
				'report_type' => 'graphs_slow',
				'report_table' => 'performance_report_slow_graphs',
				'key' => 'graph_type',
				'actual_value_key' => 'graph_count',
				'title' => ct("Slowest graphs frequency"),
				'label' => t("Slowest graphs frequency (graph)"),
				'description' => t("The frequency that the slowest graphs are requested, represented as a graph over time."),
			),
			'jobs_frequency_graph' => array(
				'report_type' => 'jobs_frequency',
				'report_table' => 'performance_report_job_frequency',
				'key' => 'job_type',
				'actual_value_key' => 'jobs_per_hour',
				'title' => ct("Job frequency (jobs/hour)"),
				'label' => t("Job frequency (graph)"),
				'description' => t("The frequency of particular jobs per hour, represented as a graph over time."),
			),
		);
	}

	var $report_type;
	var $report_table;
	var $report_ref_table;		// may be null
	var $report_reference;		// may be null
	var $key_prefix;			// may be null, if key is not null
	var $key;					// may be equal to key_prefix
	var $actual_value_key;		// may be null
	var $title;

	public function __construct($key) {
		parent::__construct();
		$data = GraphRenderer_AdminMetrics::getMetrics();
		if (!isset($data[$key])) {
			throw new GraphException("Could not find any key '$key' in available metrics");
		}
		$data = $data[$key];

		$this->report_type = $data['report_type'];
		$this->report_table = $data['report_table'];
		$this->report_ref_table = isset($data['report_ref_table']) ? $data['report_ref_table'] : null;
		$this->report_reference = isset($data['report_reference']) ? $data['report_reference'] : null;
		$this->key_prefix = isset($data['key_prefix']) ? $data['key_prefix'] : null;
		$this->key = isset($data['key']) ? $data['key'] : $this->key_prefix;
		$this->actual_value_key = isset($data['actual_value_key']) ? $data['actual_value_key'] : null;
		$this->title = $data['title'];

		if ($this->key === null && $this->key_prefix === null) {
			throw new GraphException("Cannot render AdminMetrics with a null key and key_prefix");
		}
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
					if ($query[$key_prefix . '_count'] == 0) {
						// prevent division by 0
						$row[$keys[$query[$key]]] = graph_number_format(0);
					} else {
						$row[$keys[$query[$key]]] = graph_number_format($query[$key_prefix . '_time'] / $query[$key_prefix . '_count']);
					}
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
