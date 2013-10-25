<?php

function get_knowledge_base_title($q) {
	$kb = get_knowledge_base();
	foreach (get_knowledge_base() as $label => $group) {
		foreach ($group as $key => $data) {
			if ($key == $q) {
				return is_array($data) ? $data['title'] : $data;
			}
		}
	}
	return "(Unknown kb article '" . htmlspecialchars($q) . "')";
}

function get_knowledge_base() {
	$kb = array(
		'Concepts' => array(
			'cryptocurrencies' => array('title' => "What are cryptocurrencies?", 'inline' => 'inline_cryptocurrencies'),
		),
		'Interface' => array(
			'bitcoin_csv' => "How do I upload a Bitcoin-Qt CSV file?",
			'litecoin_csv' => "How do I upload a Litecoin-Qt CSV file?",
			'managed_graphs' => array('title' => "How are graphs automatically managed?", 'inline' => 'inline_managed_graphs'),
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
		"Individual Securities" => 'securities',
		"Other" => '',
	);
	foreach (account_data_grouped() as $label => $group) {
		if (isset($wizards[$label])) {
			foreach ($group as $key => $data) {
				if ($label == 'Individual Securities') {
					$title = 'How do I add individual ' . get_exchange_name($data['exchange']) . (isset($data['suffix']) ? $data['suffix'] : '') . ($wizards[$label] ? ' ' . $wizards[$label] : '') . '?';
				} else {
					$title = 'How do I add a ' . get_exchange_name($key) . (isset($data['suffix']) ? $data['suffix'] : '') . ($wizards[$label] ? ' ' . $wizards[$label] : '') . '?';
				}
				$kb['Accounts'][$key] = array(
					'title' => $title,
					'inline' => 'inline_accounts_' . $key,
				);
			}
		}
	}

	// sort each section by title
	foreach ($kb as $label => $group) {
		uasort($kb[$label], '_sort_get_knowledge_base');
	}

	return $kb;
}

function _sort_get_knowledge_base($a, $b) {
	return strcmp(strtolower(isset($a['title']) ? $a['title'] : $a), strtolower(isset($b['title']) ? $b['title'] : $b));
}
