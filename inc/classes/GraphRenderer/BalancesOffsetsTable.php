<?php

/**
 *
 */
class GraphRenderer_BalancesOffsetsTable extends GraphRenderer {

	public function __construct() {
		parent::__construct();
	}

	public function requiresUser() {
		return true;
	}

	public function getTitle() {
		return ct("Converted fiat");
	}

	function usesSummaries() {
		return true;
	}

	public function canHaveTechnicals() {
		// do not try to calculate technicals; this also resorts the data by first key
		return false;
	}

	public function getChartType() {
		return "vertical";
	}

	function usesDays() {
		return false;
	}

	public function hasSubheading() {
		// do not try to calculate subheadings!
		return false;
	}

	public function getData($days) {

		$key_column = array('type' => 'string', 'title' => ct("Currency"));
		$columns = array();

		$columns[] = array('type' => 'string', 'title' => ct("Currency"), 'heading' => true);
		$columns[] = array('type' => 'string', 'title' => ct("Balance"));
		$columns[] = array('type' => 'html', 'title' => ct("Offset"));
		$columns[] = array('type' => 'string', 'title' => ct("Total"));

		// a table of each currency, along with an offset field
		$balances = get_all_summary_instances($this->getUser());
		$summaries = get_all_summary_currencies($this->getUser());
		$offsets = get_all_offset_instances($this->getUser());
		$currencies = get_all_currencies();
		$last_updated = find_latest_created_at($balances, "total");

		// create data
		$data = array();
		foreach ($currencies as $c) {
			if (isset($summaries[$c])) {
				$balance = demo_scale(isset($balances['total'.$c]) ? $balances['total'.$c]['balance'] : 0);
				$offset = demo_scale(isset($offsets[$c]) ? $offsets[$c]['balance'] : 0);
				$data[] = array(
					get_currency_abbr($c),
					currency_format($c, $balance - $offset, 4),
					// HTML quirk: "When there is only one single-line text input field in a form, the user agent should accept Enter in that field as a request to submit the form."
					'<form action="' . htmlspecialchars(url_for('set_offset', array('wizard' => true))) . '" method="post">' .
						'<input type="text" name="' . htmlspecialchars($c) . '" value="' . htmlspecialchars($offset == 0 ? '' : number_format_autoprecision($offset)) . '" maxlength="32">' .
						'</form>',
					currency_format($c, $balance /* balance includes offset */, 4),
				);
			}
		}

		return array(
			'key' => $key_column,
			'columns' => $columns,
			'data' => $data,
			'last_updated' => $last_updated,

			// display 'add more currencies' text
			'add_more_currencies' => true,
		);

	}

}
