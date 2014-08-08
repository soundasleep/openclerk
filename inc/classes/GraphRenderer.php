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

}
