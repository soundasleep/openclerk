<?php

abstract class GraphRenderer {

	function __construct() {
		// does nothing
	}

	/**
	 * @return an array of (columns => [column], data => [(date, value)], last_updated => (date or false))
	 */
	abstract function getData($days);

	/**
	 * Get the title of this graph
	 * @see #getTitleArgs()
	 */
	abstract function getTitle();

	/**
	 * Get any localisation (i18n) string arguments for the title given in {@link #getTitle()}.
	 * By default, returns an empty array.
	 */
	function getTitleArgs() {
		return array();
	}

	/**
	 * Get the URL that the title of this graph should link to, or {@code false} if it
	 * should not link anywhere
	 */
	function getURL() {
		return false;
	}

	/**
	 * Get the label that should be associated with the {@link #getURL()}, or
	 * {@code false} if there shouldn't be any.
	 * Should be wrapped in {@link ct()}.
	 */
	function getLabel() {
		return false;
	}

	/**
	 * Does this graph have a subheading? By default, returns {@code true}.
	 * @see #getCustomSubheading()
	 */
	function hasSubheading() {
		return true;
	}

	/**
	 * Calculate the custom subheading value for this graph, or {@code false} if
	 * this graph does not have a custom subheading defined (and subheadings calculated
	 * through {@link #hasSubheading()} will use default sum/array logic).
	 * By default, returns {@code false}.
	 * @see #hasSubheading()
	 */
	function getCustomSubheading() {
		return false;
	}

	/**
	 * What type of chart is this rendered as?
	 * By default, returns {@code linechart}.
	 */
	function getChartType() {
		return "linechart";
	}

	/**
	 * Can this graph have technicals?
	 * If this returns {@code true}, then the resulting data will always be sorted.
	 * By default, returns {@code true}.
	 */
	function canHaveTechnicals() {
		return true;
	}

	function getClasses() {
		return "";
	}

	/**
	 * Does rendering this graph require a user?
	 * By default, returns {@code false}.
	 */
	function requiresUser() {
		return $this->requiresAdmin();
	}

	/**
	 * Does rendering this graph require an admin user?
	 * By default, returns {@code false}.
	 */
	function requiresAdmin() {
		return false;
	}

	/**
	 * Does this function require a days parameter?
	 * If {@code true}, then the returned data will be stripped based on the keys returned in
	 * the data - assumes that the data uses dates as keys.
	 * By default, returns {@code true}.
	 */
	function usesDays() {
		return true;
	}

	/**
	 * Does this graph use summaries which may be out of date for a user?
	 * By default, returns {@code false}.
	 */
	function usesSummaries() {
		return false;
	}

	var $user_id = null;

	function setUser($user_id) {
		$this->user_id = $user_id;
	}

	function getUser() {
		if ($this->user_id === null) {
			throw new GraphException("Expected user to be set");
		}
		return $this->user_id;
	}

	/**
	 * Relabel all columns to have ' %' prefix,
	 * and reformat all data to be proportional based on the sum of each row.
	 * Uses the output of {@link #getData()}.
	 * @see #getData()
	 */
	public function convertGraphToProportional($original) {
		// relabel all columns to also have ' %' suffix
		foreach ($original['columns'] as $i => $column) {
			$original['columns'][$i]['title'] .= " %";
			$original['columns'][$i]['min'] = 0;
			$original['columns'][$i]['max'] = 100;
		}

		// reformat data to be proportional
		$data = array();
		foreach ($original['data'] as $date => $row) {
			$new_row = array();
			$total = 0;
			foreach ($row as $i => $value) {
				$total += $value;
			}
			foreach ($row as $i => $value) {
				if ($total == 0) {
					$new_row[$i] = 0;
				} else {
					$new_row[$i] = graph_number_format(($value / $total) * 100);
				}
			}
			$data[$date] = $new_row;
		}

		return array(
			'key' => $original['key'],
			'columns' => $original['columns'],
			'data' => $data,
			'last_updated' => $original['last_updated'],
		);

	}

}
