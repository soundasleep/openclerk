<?php

function get_knowledge_base() {
	$kb = array(
		'Interface' => array(
			'bitcoin_csv' => "How do I upload a Bitcoin-Qt CSV file?",
			'litecoin_csv' => "How do I upload a Litecoin-Qt CSV file?",
		),
	);

	// automatically construct KB for adding accounts through the wizards
	foreach (account_data_grouped() as $label => $group) {
		if ($label == "Mining pools") {
			// sort by name
			uksort($group, '_sort_get_knowledge_base');

			foreach ($group as $key => $data) {
				$kb['Accounts'][$key] = array(
					'title' => 'How do I add a ' . get_exchange_name($key) . (isset($data['suffix']) ? $data['suffix'] : '') . ' mining pool account?',
					'inline' => 'inline_accounts_' . $key,
				);
			}
		}
	}

	return $kb;
}

function _sort_get_knowledge_base($a, $b) {
	return strcmp(get_exchange_name($a), get_exchange_name($b));
}
