<?php

/**
 * Simply replaces the first column with 'MH/s' and replaces the title.
 */
class GraphRenderer_SummaryGraphHashrate extends GraphRenderer_SummaryGraph {

	function getTitle() {
		return ct(":currency MHash/s");
	}

	function getData($days) {
		$original = parent::getData($days);

		$original['columns'][0]['title'] = ct("MH/s");

		return $original;
	}

}
