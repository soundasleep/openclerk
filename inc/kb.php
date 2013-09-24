<?php

function get_knowledge_base() {
	$kb = array(
		'Interface' => array(
			'bitcoin_csv' => "How do I upload a Bitcoin-Qt CSV file?",
			'litecoin_csv' => "How do I upload a Litecoin-Qt CSV file?",
		),
		'Accounts' => array(
		),
	);

	// automatically construct KB for adding accounts through the wizards
	$wizards = array(
		// group label => kb account title
		"Mining pools" => 'mining pool account',
		"Exchanges" => 'exchange account',
		"Securities" => 'securities exchange account',
	);
	foreach (account_data_grouped() as $label => $group) {
		if (isset($wizards[$label])) {
			foreach ($group as $key => $data) {
				$kb['Accounts'][$key] = array(
					'title' => 'How do I add a ' . get_exchange_name($key) . (isset($data['suffix']) ? $data['suffix'] : '') . ' ' . $wizards[$label] . '?',
					'inline' => 'inline_accounts_' . $key,
				);
			}
		}
	}

	// sort each section by title
	foreach ($kb as $label => $group) {
		asort($kb[$label]);
	}

	return $kb;
}

function _sort_get_knowledge_base($a, $b) {
	return strcmp(get_exchange_name($a), get_exchange_name($b));
}
