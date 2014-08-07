<?php

class GraphRenderer_AdminStatistics extends GraphRenderer {

	public function __construct() {
		parent::__construct();
	}

	public function requiresAdmin() {
		return true;
	}

	public function getTitle() {
		return ct("Site status");
	}

	public function getTitleArgs() {
		return array(
		);
	}

	public function hasSubheading() {
		// do not try to calculate subheadings!
		return false;
	}

	public function canHaveTechnicals() {
		// do not try to calculate technicals; this also resorts the data by first key
		return false;
	}

	public function getChartType() {
		return "vertical";
	}

	public function getData($days) {
		$columns = array();

		$key_column = array('type' => 'string', 'title' => ct("Key"));
		$columns[] = array('type' => 'string', 'title' => ct("Title"), 'heading' => true);
		$columns[] = array('type' => 'string align-right', 'title' => ct("Total"));
		$columns[] = array('type' => 'string align-right', 'title' => ct("Last week"));
		$columns[] = array('type' => 'string align-right', 'title' => ct("Last day"));
		$columns[] = array('type' => 'string align-right', 'title' => ct("Last hour"));

		$last_updated = time();

		$summary = array(
			'users' => array('title' => ct('Users'), 'extra' => array('is_disabled=1' => ct('Disabled'))),
			'addresses' => array('title' => ct('Addresses')),
			'jobs' => array('title' => ct('Jobs'), 'extra' => array('is_executed=0' => ct('Pending'))),
			'outstanding_premiums' => array('title' => ct('Premiums'), 'extra' => array('is_paid=1' => ct('Paid'))),
			'uncaught_exceptions' => array('title' => ct('Uncaught exceptions')),
			'ticker' => array('title' => ct('Ticker instances')),
		);
		$result = array();
		foreach ($summary as $key => $data) {
			$row = array();
			$row[0] = $data['title'];
			if (isset($data['extra'])) {
				foreach ($data['extra'] as $extra_key => $extra_title) {
					$row[0] .= " ($extra_title)";
				}
			}
			$parts = array(
				'1',
				'created_at >= date_sub(now(), interval 7 day)',
				'created_at >= date_sub(now(), interval 1 day)',
				'created_at >= date_sub(now(), interval 1 hour)',
			);
			foreach ($parts as $query) {
				$q = db()->prepare("SELECT COUNT(*) AS c FROM $key WHERE $query");
				$q->execute();
				$c = $q->fetch();
				$row[] = number_format($c['c']);

				if (isset($data['extra'])) {
					foreach ($data['extra'] as $extra_key => $extra_title) {
						$q = db()->prepare("SELECT COUNT(*) AS c FROM $key WHERE $query AND $extra_key");
						$q->execute();
						$c = $q->fetch();
						$row[count($row)-1] .= " (" . number_format($c['c']) . ")";
					}
				}

			}
			$result[$key] = $row;
		}

		$row = array(ct("Unused premium addresses"));
		$q = db()->prepare("SELECT currency, COUNT(*) AS c FROM premium_addresses WHERE is_used=0 GROUP BY currency");
		$q->execute();
		while ($c = $q->fetch()) {
			$row[] = number_format($c['c']) . " (" . get_currency_abbr($c['currency']) . ")";
		}
		$result['unused_premium_addresses'] = $row;

		return array(
			'key' => $key_column,
			'columns' => $columns,
			'data' => $result,
			'last_updated' => $last_updated,
		);


	}

}
