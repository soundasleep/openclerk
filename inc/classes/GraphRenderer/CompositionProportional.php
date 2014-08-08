<?php

class GraphRenderer_CompositionProportional extends GraphRenderer_CompositionGraph {

	public function getChartType() {
		return "stacked";
	}

	public function getData($days) {
		$original = parent::getData($days);

		// TODO make all columns min..max = 0..100

		// relabel all columns to also have ' %' suffix
		foreach ($original['columns'] as $i => $column) {
			$original['columns'][$i]['title'] .= " %";
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
