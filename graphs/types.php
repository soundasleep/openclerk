<?php


/**
 * Get all of the defined public graph types. These are then included into graph_types()
 * as necessary.
 */
function graph_types_public($summaries = array()) {

	$data = array();

	$data['category_exchanges'] = array(
		'title' => 'Exchanges',
		'category' => true,
	);

	// we can generate a list of daily graphs from all of the exchanges that we support
	// but we'll only want to display currency pairs that we're interested in
	foreach (get_exchange_pairs() as $key => $pairs) {
		$data['subcategory_exchanges_' . $key] = array(
			'title' => get_exchange_name($key),
			'subcategory' => true,
		);

		foreach ($pairs as $pair) {
			$pp = get_currency_abbr($pair[0]) . "/" . get_currency_abbr($pair[1]);
			$data[$key . "_" . $pair[0] . $pair[1] . "_daily"] = array(
				'title' => get_exchange_name($key) . " historical $pp (graph)",
				'heading' => get_exchange_name($key) . " $pp",
				'description' => "A line graph displaying the historical bid/ask values for $pp on " . get_exchange_name($key) . ".",
				'pairs' => $pair,
				'hide' => !(isset($summaries[$pair[0]]) && isset($summaries[$pair[1]])),
				'public' => true, /* can be displayed publicly */
				'days' => true,
				'technical' => true, /* allow technical indicators */
				'delta' => true,	/* allow deltas */
				'historical' => 'get_exchange_historical',
				'historical_arg0' => array('key' => $key, 'pair' => $pair),
				'exchange' => $key,
			);
		}
	}

	$data['category_securities'] = array(
		'title' => 'Securities',
		'category' => true,
	);
	$data['subcategory_securities'] = array(
		'title' => 'Security values',
		'subcategory' => true,
	);

	// get all securities
	foreach (get_security_exchange_pairs() as $key => $currencies) {

		foreach ($currencies as $c) {
			$data['securities_' . $key . '_' . $c] = array(
				'title' => get_exchange_name($key) . " " . get_currency_abbr($c) . " security value (graph)",
				'heading' => get_exchange_name($key) . " security",
				'description' => 'A line graph displaying the historical value of a particular ' . get_exchange_name($key) . ' security.',
				'hide' => !isset($summaries[$c]),	// only show securities in currencies we're interested in
				'days' => true,
				'delta' => true,	/* allow deltas */
				'arg0' => 'get_security_instances_keys',
				'arg0_title' => 'Security:',
				'param0' => $key,
				'param1' => $c,
				'technical' => true,
				'historical' => 'get_security_instances_historical',
				'historical_param0' => $key,
				'historical_param1' => $c,
				'title_callback' => 'get_security_instance_title',
			);
		}
	}

	$data['category_tools'] = array(
		'title' => 'Tools',
		'category' => true,
	);
	$data['subcategory_tools'] = array(
		'title' => 'Tools',
		'subcategory' => true,
	);

	$data['calculator'] = array(
		'title' => 'Currency converter (calculator)',
		'heading' => 'Currency converter',
		'description' => 'A <a href="' . htmlspecialchars(url_for('calculator')) . '">simple calculator</a> to convert any currency into any other currency, using the most recent exchange rates.',
		'technical' => false,
		'no_refresh' => true,		// do not refresh the calculator graph, or we will lose entered values
	);

	$data['external_historical'] = array(
		'title' => 'External API status (graph)',
		'heading' => 'External API status',
		'description' => 'A line graph displaying the historical status of an external API, by displaying the percentage of failing samples.',
		'days' => true,
		'arg0' => 'get_external_status_titles',
		'arg0_title' => 'External API:',
		'technical' => false,
		'historical' => 'get_external_status_historical',
	);

	$data['statistics_queue'] = array(
		'title' => "Job queue delay (graph)",
		'heading' => "Job queue delay (hours)",
		'description' => 'The job queue delay for free and premium users.',
		'hide' => true,		// should only be accessible by admins
		'admin' => true,	// should only be accessible by admins
	);

	$data['metrics_db_slow_queries'] = array(
		'title' => "Slowest DB queries",
		'heading' => "Slowest DB queries",
		'description' => 'The slowest database queries.',
		'hide' => true,		// should only be accessible by admins
		'admin' => true,	// should only be accessible by admins
	);

	$data['metrics_db_slow_queries_graph'] = array(
		'title' => "Slowest DB queries (graph)",
		'heading' => "Slowest DB queries (ms)",
		'description' => 'The slowest database queries represented as a graph over time.',
		'hide' => true,		// should only be accessible by admins
		'admin' => true,	// should only be accessible by admins
	);

	$data['metrics_curl_slow_urls'] = array(
		'title' => "Slowest CURL URLs",
		'heading' => "Slowest CURL URLs",
		'description' => 'The slowest CURL requests.',
		'hide' => true,		// should only be accessible by admins
		'admin' => true,	// should only be accessible by admins
	);

	$data['metrics_curl_slow_urls_graph'] = array(
		'title' => "Slowest CURL URLs (graph)",
		'heading' => "Slowest CURL URLs (ms)",
		'description' => 'The slowest CURL requests represented as a graph over time.',
		'hide' => true,		// should only be accessible by admins
		'admin' => true,	// should only be accessible by admins
	);

	$data['metrics_curl_slow_jobs_graph'] = array(
		'title' => "Slowest jobs (graph)",
		'heading' => "Slowest jobs (ms)",
		'description' => 'The slowest jobs represented as a graph over time.',
		'hide' => true,		// should only be accessible by admins
		'admin' => true,	// should only be accessible by admins
	);

	return $data;
}

/**
 * Return some text describing the default exchanges used for the given currencies.
 * For example:
 *  array('ltc', 'ftc', 'usd', 'ghs') => 'BTC-e for LTC/FTC, Mt.Gox for USD, CEX.io for GHS'
 * @see get_default_currency_exchange()
 */
function get_default_exchange_text($currencies) {
	$result = array();
	foreach ($currencies as $c) {
		$default = get_default_currency_exchange($c);
		if (!isset($result[$default])) {
			$result[$default] = array();
		}
		$result[$default][] = get_currency_abbr($c);
	}
	$result2 = array();
	foreach ($result as $exchange => $currencies) {
		$result2[] = get_exchange_name($exchange) . " for " . implode("/", $currencies);
	}
	return implode(", ", $result2);
}

/**
 * Get all of the defined graph types. Used for display and validation.
 */
function graph_types() {
	$total_fiat_currencies = array();
	foreach (get_total_conversion_summary_types() as $c) {
		$total_fiat_currencies[] = $c['title'];
	}
	$total_fiat_currencies = implode_english($total_fiat_currencies);

	$data = array(
		'category_general' => array('title' => 'General', 'category' => true),
		'subcategory_general' => array('title' => 'General graphs', 'subcategory' => true),
		'btc_equivalent' => array('title' => 'Equivalent BTC balances (pie)', 'heading' => 'Equivalent BTC', 'description' => 'A pie chart representing the overall proportional value of all currencies if they were all converted into BTC.<p>Exchanges used: ' . get_default_exchange_text(array_diff(get_all_currencies(), array('btc'))) . '.', 'default_width' => get_site_config('default_user_graph_height')),
		'btc_equivalent_graph' => array('title' => 'Equivalent BTC balances (graph)', 'heading' => 'Equivalent BTC', 'description' => 'A line graph displaying the historical value of all currencies if they were all converted into BTC.<p>Exchanges used: ' . get_default_exchange_text(array_diff(get_all_currencies(), array('btc'))) . '.', 'days' => true),
		'btc_equivalent_stacked' => array('title' => 'Equivalent BTC balances (stacked)', 'heading' => 'Equivalent BTC', 'description' => 'A stacked area graph displaying the historical value of all currencies if they were all converted into BTC.<p>Exchanges used: ' . get_default_exchange_text(array_diff(get_all_currencies(), array('btc'))) . '.', 'days' => true),
		'btc_equivalent_proportional' => array('title' => 'Equivalent BTC balances (proportional)', 'heading' => 'Equivalent BTC', 'description' => 'A stacked area graph displaying the proportional historical value of all currencies if they were all converted into BTC.<p>Exchanges used: ' . get_default_exchange_text(array_diff(get_all_currencies(), array('btc'))) . '.', 'days' => true),
		'mtgox_btc_table' => array('title' => 'Mt.Gox USD/BTC (table)', 'heading' => 'Mt.Gox', 'description' => 'A simple table displaying the current bid/ask USD/BTC price on Mt.Gox.', 'default_width' => get_site_config('default_user_graph_height')),
		'ticker_matrix' => array('title' => 'All currencies exchange rates (matrix)', 'heading' => 'All exchanges', 'description' => 'A matrix displaying the current bid/ask of all of the currencies and exchanges <a href="' . htmlspecialchars(url_for('wizard_currencies')) .'">you are interested in</a>.'),
		'balances_table' => array('title' => 'Total balances (table)', 'heading' => 'Total balances', 'description' => 'A table displaying the current sum of all your currencies (before any conversions).', 'default_width' => get_site_config('default_user_graph_height')),
		'total_converted_table' => array('title' => 'Total converted fiat balances (table)', 'heading' => 'Converted fiat', 'description' => 'A table displaying the equivalent value of all cryptocurrencies and fiat currencies if they were immediately converted into fiat currencies. Cryptocurrencies are converted via BTC.<p>Supports ' . $total_fiat_currencies . '.<p>Exchanges used: ' . get_default_exchange_text(array_diff(get_all_currencies(), array('btc'))) . '.', 'default_width' => get_site_config('default_user_graph_height')),
		'crypto_converted_table' => array('title' => 'Total converted crypto balances (table)', 'heading' => 'Converted crypto', 'description' => 'A table displaying the equivalent value of all cryptocurrencies - but not fiat currencies - if they were immediately converted into other cryptocurrencies.<p>Exchanges used: ' . get_default_exchange_text(array_diff(get_all_cryptocurrencies(), array('btc'))) . '.', 'default_width' => get_site_config('default_user_graph_height')),
		'balances_offset_table' => array('title' => 'Total balances with offsets (table)', 'heading' => 'Total balances', 'description' => 'A table displaying the current sum of all currencies (before any conversions), along with text fields to set offset values for each currency directly.'),
	);

	$summaries = get_all_summary_currencies();
	$conversions = get_all_conversion_currencies();

	$data['category_summaries'] = array(
		'title' => 'Your summaries',
		'category' => true,
	);

	$data['subcategory_summaries_total'] = array(
		'title' => 'Historical currency value',
		'subcategory' => true,
	);

	// we can generate a list of summary daily graphs from all the currencies that we support
	foreach (get_summary_types() as $key => $summary) {
		$cur = $summary['currency'];
		$data["total_" . $cur . "_daily"] = array(
			'title' => "Total " . get_currency_name($cur) . " historical (graph)",
			'heading' => "Total " . get_currency_abbr($cur),
			'description' => "A line graph displaying the historical sum of your " . get_currency_name($cur) . " (before any conversions).",
			'hide' => !isset($summaries[$cur]),
			'days' => true,
			'delta' => true,	/* allow deltas */
			'technical' => true,
		);
	}

	$data['subcategory_summaries_crypto2'] = array(
		'title' => 'Historical converted value',
		'subcategory' => true,
	);

	foreach (get_crypto_conversion_summary_types() as $key => $summary) {
		$cur = $summary['currency'];
		$data["crypto2" . $key . "_daily"] = array(
			'title' => 'Converted ' . $summary['title'] . " historical (graph)",
			'heading' => 'Converted ' . $summary['short_title'],
			'description' => "A line graph displaying the historical equivalent value of all cryptocurrencies - and not other fiat currencies - if they were immediately converted to " . $summary['title'] . ".",
			'hide' => !isset($conversions['summary_' . $key]),
			'days' => true,
			'delta' => true,	/* allow deltas */
			'technical' => true,
		);
	}

	/*
	 * Issue #112 reported that 'all2CUR' was not correctly converting fiat currencies other than CUR.
	 * Rather than renaming 'all2CUR' as 'all cryptocurrencies and CUR', which doesn't seem to be particularly useful
	 * - and it will mean we'll have to track two new summaries for every currency -
	 * as of 0.19 this will now correctly be calculated as 'all cryptocurrencies and fiat currencies'. This means that there
	 * will be a jump in the value of data when deployed.
	 */
	foreach (get_total_conversion_summary_types() as $key => $summary) {
		$cur = $summary['currency'];
		$data["all2" . $key . "_daily"] = array(
			'title' => 'Converted ' . $summary['title'] . " historical (graph)",
			'heading' => 'Converted ' . $summary['short_title'],
			'description' => "A line graph displaying the historical equivalent value of all cryptocurrencies and fiat currencies if they were immediately converted to " . $summary['title'] . " (where possible).",
			'hide' => !isset($conversions['summary_' . $key]),
			'days' => true,
			'delta' => true,	/* allow deltas */
			'technical' => true,
		);
	}

	$data['subcategory_summaries_composition'] = array(
		'title' => 'Total balance composition',
		'subcategory' => true,
	);

	// we can generate a list of composition graphs from all of the currencies that we support
	foreach (get_all_currencies() as $currency) {
		$data["composition_" . $currency . "_pie"] = array(
			'title' => "Total " . get_currency_name($currency) . " balance composition (pie)",
			'heading' => "Total " . get_currency_abbr($currency),
			'description' => "A pie chart representing all of the sources of your total " . get_currency_name($currency) . " balance (before any conversions).",
			'hide' => !isset($summaries[$currency]),
			'default_width' => get_site_config('default_user_graph_height'),
		);
	}

	$data['subcategory_summaries_graph'] = array(
		'title' => 'All balances (graph)',
		'subcategory' => true,
	);

	foreach (get_all_currencies() as $currency) {
		$data["composition_" . $currency . "_daily"] = array(
			'title' => "All " . get_currency_name($currency) . " balances (graph)",
			'heading' => "All " . get_currency_abbr($currency) . " balances",
			'description' => "A line graph representing all of the sources of your total " . get_currency_name($currency) . " balance (before any conversions).",
			'days' => true,
			'hide' => !isset($summaries[$currency]),
		);
	}

	$data['subcategory_summaries_table'] = array(
		'title' => 'All balances (table)',
		'subcategory' => true,
	);

	foreach (get_all_currencies() as $currency) {
		$data["composition_" . $currency . "_table"] = array(
			'title' => "Your " . get_currency_name($currency) . " balances (table)",
			'heading' => "Your " . get_currency_abbr($currency) . " balances",
			'description' => "A table displaying all of your " . get_currency_name($currency) . " balances and the total balance (before any conversions).",
			'hide' => !isset($summaries[$currency]),
		);
	}

	$data['subcategory_summaries_stacked'] = array(
		'title' => 'All balances (stacked)',
		'subcategory' => true,
	);

	foreach (get_all_currencies() as $currency) {
		$data["composition_" . $currency . "_stacked"] = array(
			'title' => "All " . get_currency_name($currency) . " balances (stacked)",
			'heading' => "All " . get_currency_abbr($currency) . " balances",
			'description' => "A stacked area graph displaying the historical value of your total " . get_currency_name($currency) . " balance (before any conversions).",
			'days' => true,
			'hide' => !isset($summaries[$currency]),
		);
	}

	$data['subcategory_summaries_proportional'] = array(
		'title' => 'All balances (proportional)',
		'subcategory' => true,
	);

	foreach (get_all_currencies() as $currency) {
		$data["composition_" . $currency . "_proportional"] = array(
			'title' => "All " . get_currency_name($currency) . " balances (proportional)",
			'heading' => "All " . get_currency_abbr($currency) . " balances",
			'description' => "A stacked area graph displaying the proportional historical value of your total " . get_currency_name($currency) . " balance (before any conversions).",
			'days' => true,
			'hide' => !isset($summaries[$currency]),
		);
	}

	$data['category_hashrate'] = array(
		'title' => 'Your mining',
		'category' => true,
	);

	$data['category_hashrate_hashrate'] = array(
		'title' => 'Historical hashrates',
		'subcategory' => true,
	);

	// and for each cryptocurrency that can be hashed
	foreach (get_all_hashrate_currencies() as $cur) {
		$data["hashrate_" . $cur . "_daily"] = array(
			'title' => get_currency_name($cur) . " historical MHash/s (graph)",
			'heading' => get_currency_abbr($cur) . " MHash/s",
			'description' => "A line graph displaying the historical hashrate sum of all workers mining " . get_currency_name($cur) . " across all pools (in MHash/s).",
			'hide' => !isset($summaries[$cur]),
			'days' => true,
			'delta' => true,	/* allow deltas */
			'technical' => true,
		);
	}

	// merge in graph_types_public() here
	foreach (graph_types_public($summaries) as $key => $public_data) {
		// but add 'hide' parameter to hide irrelevant currencies
		if (isset($public_data['pairs'])) {
			$pairs = $public_data['pairs'];
			$public_data['hide'] = !(isset($summaries[$pairs[0]]) && isset($summaries[$pairs[1]]));
		}
		$data[$key] = $public_data;
	}

	$data['subcategory_layout'] = array(
		'title' => 'Layout tools',
		'subcategory' => true,
	);

	$data['linebreak'] = array(
		'title' => 'Line break',
		'description' => 'Forces a line break at a particular location. Select \'Enable layout editing\' to move it.',
		'heading' => 'Line break',		// not actually rendered
	);
	$data['heading'] = array(
		'title' => 'Heading',
		'description' => 'Displays a line of text as a heading at a particular location. Also functions as a line break. Select \'Enable layout editing\' to move it.',
		'string0' => "Example heading",			// sample text
		'heading' => 'Heading',		// not actually rendered
	);

	// add sample images
	$images = array(
		'btc_equivalent' => 'btc_equivalent.png',
		'composition_btc_pie' => 'composition_btc_pie.png',
		'composition_ltc_pie' => 'composition_ltc_pie.png',
		'composition_nmc_pie' => 'composition_nmc_pie.png',
		'btce_btcnmc_daily' => 'btce_btcnmc_daily.png',
		'btce_btcftc_daily' => 'btce_btcftc_daily.png',
		'btce_btcltc_daily' => 'btce_btcltc_daily.png',
		'mtgox_usdbtc_daily' => 'mtgox_usdbtc_daily.png',
		'mtgox_audbtc_daily' => 'mtgox_audbtc_daily.png',
		'mtgox_cadbtc_daily' => 'mtgox_cadbtc_daily.png',
		'mtgox_gbpbtc_daily' => 'mtgox_gbpbtc_daily.png',
		'mtgox_plnbtc_daily' => 'mtgox_plnbtc_daily.png',
		'bitstamp_usdbtc_daily' => 'bitstamp_usdbtc_daily.png',
		'bitnz_nzdbtc_daily' => 'bitnz_nzdbtc_daily.png',
		'btcchina_cnybtc_daily' => 'btcchina_cnybtc_daily.png',
		'cexio_btcghs_daily' => 'cexio_btcghs_daily.png',
		'vircurex_btcltc_daily' => 'vircurex_btcltc_daily.png',
		'vircurex_btcdog_daily' => 'vircurex_btcdog_daily.png',
		'themoneyconverter_usdeur_daily' => 'themoneyconverter_usdeur_daily.png',
		'themoneyconverter_usdaud_daily' => 'themoneyconverter_usdaud_daily.png',
		'themoneyconverter_usdcad_daily' => 'themoneyconverter_usdcad_daily.png',
		'themoneyconverter_usdnzd_daily' => 'themoneyconverter_usdnzd_daily.png',
		'crypto2btc_daily' => 'crypto2btc_daily.png',
		'crypto2ltc_daily' => 'crypto2ltc_daily.png',
		'crypto2nmc_daily' => 'crypto2nmc_daily.png',
		'crypto2dog_daily' => 'crypto2dog_daily.png',
		'all2usd_mtgox_daily' => 'all2usd_mtgox_daily.png',
		'all2nzd_bitnz_daily' => 'all2nzd_bitnz_daily.png',
		'all2aud_mtgox_daily' => 'all2aud_mtgox_daily.png',
		'all2eur_mtgox_daily' => 'all2eur_mtgox_daily.png',
		'all2cad_virtex_daily' => 'all2cad_virtex_daily.png',
		'all2usd_bitstamp_daily' => 'all2usd_bitstamp_daily.png',
		'all2usd_btce_daily' => 'all2usd_btce_daily.png',
		'btc_equivalent_graph' => 'btc_equivalent_graph.png',
		'btc_equivalent_proportional' => 'btc_equivalent_proportional.png',
		'btc_equivalent_stacked' => 'btc_equivalent_stacked.png',
		'total_btc_daily' => 'total_btc_daily.png',
		'total_ltc_daily' => 'total_ltc_daily.png',
		'total_nmc_daily' => 'total_nmc_daily.png',
		'total_ghs_daily' => 'total_ghs_daily.png',
		'hashrate_ltc_daily' => 'hashrate_ltc_daily.png',
		'balances_table' => 'balances_table.png',
		'balances_offset_table' => 'balances_offset_table.png',
		'crypto_converted_table' => 'crypto_converted_table.png',
		'total_converted_table' => 'total_converted_table.png',
		'composition_btc_daily' => 'composition_btc_daily.png',
		'composition_ltc_daily' => 'composition_ltc_daily.png',
		'composition_nmc_daily' => 'composition_ltc_daily.png',
		'composition_ftc_daily' => 'composition_ltc_daily.png',
		'composition_ppc_daily' => 'composition_ltc_daily.png',
		'composition_nvc_daily' => 'composition_ltc_daily.png',
		'composition_dog_daily' => 'composition_dog_daily.png',
		'composition_btc_table' => 'composition_btc_table.png',
		'composition_ltc_table' => 'composition_ltc_table.png',
		'composition_nmc_table' => 'composition_nmc_table.png',
		'composition_ftc_table' => 'composition_ltc_table.png',
		'composition_ppc_table' => 'composition_ltc_table.png',
		'composition_nvc_table' => 'composition_ltc_table.png',
		'composition_dog_table' => 'composition_dog_table.png',
		'composition_btc_proportional' => 'composition_btc_proportional.png',
		'composition_ltc_proportional' => 'composition_ltc_proportional.png',
		'composition_nmc_proportional' => 'composition_nmc_proportional.png',
		'composition_ftc_proportional' => 'composition_ltc_proportional.png',
		'composition_ppc_proportional' => 'composition_ltc_proportional.png',
		'composition_nvc_proportional' => 'composition_ltc_proportional.png',
		'composition_btc_stacked' => 'composition_btc_stacked.png',
		'composition_ltc_stacked' => 'composition_ltc_stacked.png',
		'composition_nmc_stacked' => 'composition_ltc_stacked.png',
		'composition_ftc_stacked' => 'composition_ltc_stacked.png',
		'composition_ppc_stacked' => 'composition_ltc_stacked.png',
		'composition_nvc_stacked' => 'composition_ltc_stacked.png',
		'composition_ghs_stacked' => 'composition_ghs_stacked.png',
		'ticker_matrix' => 'ticker_matrix.png',
		'calculator' => 'calculator.png',
	);
	$data = add_example_images($data, $images);

	return $data;
}

function graph_technical_types() {
	$data = array(
		"sma" => array('title' => 'Simple moving average (SMA)', 'period' => true, 'premium' => false, 'title_short' => 'SMA',
			'description' => 'A simple moving average of the price - or midpoint between buy and sell - over the last <i>n</i> days.'),
	);
	foreach (graph_premium_technical_types() as $key => $value) {
		$data[$key] = $value;
	}

	// add sample images
	$images = array(
		'sma' => 'technical_sma.png',
	);
	$data = add_example_images($data, $images);

	return $data;
}

function add_example_images($data, $images) {
	// add sample images
	$example_prefix = "<div class=\"example\"><div>Example:</div><img src=\"img/graphs/";
	$example_suffix = "\"></div>";

	foreach ($data as $key => $value) {
		if (isset($images[$key])) {
			$data[$key]['description'] .= "<div class=\"example\"><div>Example:</div><img src=\"img/graphs/" .
					htmlspecialchars($images[$key]) . "\"></div>";
		}
	}

	return $data;
}
