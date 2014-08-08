<?php

class GraphRenderer_BalancesGraphSecurities extends GraphRenderer_BalancesGraph {

	public function __construct($exchange, $account_id, $currency, $arg0_resolved) {
		parent::__construct($exchange, $account_id, $currency);
		if (!$this->account_id) {
			$bits = explode("_", $this->exchange, 2);
			if ($bits[0] != "securities") {
				throw new GraphException("Expected exchange of securities_exchange format");
			}

			$instances = get_security_instances($bits[1], $this->currency);
			foreach ($instances as $instance) {
				if ($instance['title'] == $arg0_resolved) {
					$this->account_id = $instance['id'];
				}
			}

			if (!$this->account_id) {
				throw new GraphException("Could not find security '" . $arg0_resolved . "'");
			}
		}
	}

	public function requiresUser() {
		return false;
	}

	function usesSummaries() {
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
