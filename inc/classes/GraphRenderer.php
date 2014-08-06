<?php

abstract class GraphRenderer {

	/**
	 * @return an array of (columns => [column], data => [(date, value)], last_updated => (date or false))
	 */
	abstract function getData($days);

	/**
	 * Get the title of this graph
	 */
	abstract function getTitle();

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
	 */
	function hasSubheading() {
		return true;
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

}
