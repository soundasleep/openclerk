<?php

/**
 * Simply replaces the title.
 */
class GraphRenderer_SummaryGraphConvertedExchange extends GraphRenderer_SummaryGraph {

	var $exchange;

	function getTitle() {
		$bits = explode("_", $this->summary_type, 2);
		$this->exchange = $bits[1];
		return ct("Converted :currency (:exchange)");
	}

	function getTitleArgs() {
		return array(
			':currency' => get_currency_abbr($this->currency),
			':exchange' => get_exchange_name($this->exchange),
		);
	}

}
