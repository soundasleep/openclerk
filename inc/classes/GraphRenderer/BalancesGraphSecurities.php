<?php

class GraphRenderer_BalancesGraphSecurities extends GraphRenderer_BalancesGraph {

	public function requiresUser() {
		return false;
	}

	public function getUser() {
		return get_site_config('system_user_id');
	}

	public function getTitleArgs() {
		$bits = explode("_", $this->exchange, 2);
		if ($bits[0] != "securities") {
			throw new GraphException("Expected exchange of securities_exchange format");
		}

		$instances = get_security_instances($bits[1], $this->currency);
		foreach ($instances as $instance) {
			if ($instance['id'] == $this->account_id) {
				return array(
					':exchange' => $instance['title'],
				);
			}
		}

		return "(could not find security)";
	}

}
