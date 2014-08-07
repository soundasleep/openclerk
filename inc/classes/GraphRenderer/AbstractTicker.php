<?php

/**
 * This line graph renderer abstracts away common functionality for graphs that
 * - get data from the database
 *   - from a number of sources, which have to be collated together
 * - returns data based on dates
 * - returns 1..2 values
 */
abstract class GraphRenderer_AbstractTicker extends GraphRenderer {

	/**
	 * @return an array of columns e.g. (type, title, args)
	 */
	abstract function getTickerColumns();

	/**
	 * The sources must return 'created_at' column as well, for last_updated
	 * @return an array of queries e.g. (query, key = created_at/data_date)
	 */
	abstract function getTickerSources($days, $extra_days);

	/**
	 * @return an array of arguments passed to each {@link #getTickerSources()}
	 */
	abstract function getTickerArgs();

	/**
	 * @return an array of 1..2 values of the values for the particular row,
	 * 			maybe formatted with {@link #graph_number_format()}.
	 */
	abstract function getTickerData($row);

	/**
	 * @return true if data should be limited to days, or false if it can have any resolution.
	 *			defaults to false
	 */
	public function isDaily() {
		return false;
	}

	public function getData($days) {
		$columns = array();

		$key_column = array('type' => 'date', 'title' => ct("Date"));

		$columns = $this->getTickerColumns();

		// TODO extra_days_necessary
		$extra_days = 10;

		$sources = $this->getTickerSources($days, $extra_days);

		$args = $this->getTickerArgs();

		$data = array();
		$last_updated = false;
		foreach ($sources as $source) {
			$q = db()->prepare($source['query']);
			$q->execute($args);
			while ($ticker = $q->fetch()) {
				$data_key = date($this->isDaily() ? 'Y-m-d' : 'Y-m-d H:i:s', strtotime($ticker[$source['key']]));
				$data[$data_key] = $this->getTickerData($ticker);
				$last_updated = max($last_updated, strtotime($ticker['created_at']));
			}
		}

		// sort by key, but we only want values
		uksort($data, 'cmp_time_reverse');

		return array(
			'key' => $key_column,
			'columns' => $columns,
			'data' => $data,
			'last_updated' => $last_updated,
		);

	}

}
