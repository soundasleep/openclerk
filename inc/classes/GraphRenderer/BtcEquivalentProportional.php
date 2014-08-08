<?php

class GraphRenderer_BtcEquivalentProportional extends GraphRenderer_BtcEquivalentGraph {

	public function getChartType() {
		return "stacked";
	}

	public function getData($days) {
		return $this->convertGraphToProportional(parent::getData($days));
	}

	public function hasSubheading() {
		// the only valid subheading would be '100%', it wouldn't make sense
		return false;
	}

}
