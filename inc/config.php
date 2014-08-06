<?php

if (file_exists(__DIR__ . "/../config/config.php")) {
	require(__DIR__ . "/../config/config.php");
}

$global_get_site_config = null;

/**
 * This function provides the configuration for an Openclerk instance,
 * based on {@link #get_default_site_config()} and {@link #get_site_config_ext()}
 * It can be extended by defining a function 'get_site_config_ext()'
 * and providing a new map of keys to values.
 *
 * Config values here will never change between calls so can be cached.
 */
function get_site_config($key = null, $fail_if_missing = true) {
	global $global_get_site_config;
	if ($global_get_site_config === null) {
		$global_get_site_config = array();
		if (function_exists('get_site_config_ext')) {
			$global_get_site_config += get_site_config_ext();
		}
		$global_get_site_config += get_default_site_config();
	}

	if (!$fail_if_missing && !isset($global_get_site_config[$key])) {
		return false;	// don't display an error
	}
	if (!isset($global_get_site_config[$key])) {
		throw new Exception("Unknown site config variable '" . htmlspecialchars($key) . "'");
	}

	return $global_get_site_config[$key];
}

/**
 * This function provides the default configuration for an Openclerk instance (#136).
 * It can be extended by defining a function 'get_site_config_ext()'
 * and providing a new map of keys to values.
 *
 * Config values here will never change between calls so can be cached.
 */
function get_default_site_config() {
	return array(
		// framework-specific
		"site_name" => "Openclerk",
		"site_email" => "support@example.com",
		"site_id" => "openclerk",
		"openid_host" => "localhost",
		"absolute_url" => "http://localhost/clerk/",
		"openclerk_version" => "0.27",		// this is just a label displayed at the bottom of each page

		"database_url" => "mysql:host=localhost;dbname=clerk",
		"database_username" => "clerk",
		"database_password" => "clerk",
		"database_timezone" => false,	// e.g. "-08:00" if your DB is in UTC and your web server is in PST

		// issue #188: if true, then non-write queries will go to this database by default
		"database_slave" => false,
		"database_slave_url" => "mysql:host=localhost;dbname=clerk",
		"database_slave_username" => "clerk",
		"database_slave_password" => "clerk",
		"database_slave_timezone" => false,			// this should really be the same as `database_timezone`

		"phpmailer_host" => "mail.example.com",
		"phpmailer_username" => "sync",
		"phpmailer_password" => base64_decode("xxx"),
		"phpmailer_from" => "example@example.com",
		"phpmailer_from_name" => "example@example.com",
		"phpmailer_reply_to" => "example@example.com",
		"phpmailer_bcc" => "example@example.com",		// if set, send a copy of all emails to this address

		"admin_email" => "example@gmail.com",
		"password_salt" => "abc123",
		"password_reset_salt" => "abc456",
		"unsubscribe_salt" => "123abc",
		"google_analytics_account" => "UA-12345678-1",	// _setAccount
		"google_analytics_domain" => "example.com",	// _setDomainName

		// site-specific config values go here
		"automated_key" => "abc123",
		"refresh_queue_hours" => 2, // should be 24 for production, in hours
		"refresh_queue_hours_premium" => 1, // can be whatever, in hours
		"refresh_queue_hours_system" => 0.1, // should be frequent for litecoin payments and ticker retrieval, in hours
		"refresh_queue_hours_ticker" => 0.1, // ticker jobs can have different queue times
		"system_user_id" => 100,
		"get_contents_timeout" => 5, // in seconds
		"get_openid_timeout" => 5,	// in seconds, should be <= heavy_requests_seconds
		"default_login" => 'user',		// default login destination
		"signup_login" => 'wizard_currencies',		// login destination when signing up; overridden by premium_welcome -> 'welcome'
		"autologin_expire_days" => 30,
		"autologin_cookie_seconds" => 60 * 60 * 24 * 30,
		"external_sample_size" => 10000,	// what is the max number of jobs to sample when updating the external API page?
		"default_cache_seconds" => 60 * 60 * 24 * 7,		// allow things to be cached for up to a week by default
		"vote_coins_multiplier" => 10,		// voting multiplier

		// issue #12: allow unsafe accounts?
		'allow_unsafe' => false,

		// debug control
		"timed_sql" => false,
		"timed_curl" => false,
		"show_i18n" => false,				// shows all i18n keys as [key], only to admins
		"log_missing_i18n" => false,		// log any missing i18n keys to uncaught_exceptions
		"allow_fake_login" => false,		// see admin_login.php

		// performance metrics control
		// performance metrics also rely on timed_sql if available
		"performance_metrics_enabled" => false,
		"performance_metrics_slow_query" => 250,	// keep track of queries that take on average longer than this many ms
		"performance_metrics_repeated_query" => 5,	// keep track of queries that occur more than this many times in a page/request
		"performance_metrics_slow_curl" => 2000,	// keep track of curl URLs that take on average longer than this many ms
		"performance_metrics_repeated_curl" => 2,	// keep track of curl URLs that occur more than this many times in a page/request
		// job control
		"jobs_enabled" => true,		// disable when performing upgrades
		"maximum_jobs_running" => 20,		// issue #128: don't run more than this many jobs at once (except for forced jobs)
		"sleep_btce_ticker" => 5,	// how many seconds to wait between repeated btc-e ticker queries
		"sleep_bit2c_ticker" => 5,
		"sleep_cexio_ticker" => 5,
		"sleep_virtex_ticker" => 5,
		"sleep_mtgox_ticker" => 5,
		"sleep_vircurex_balance" => 5,
		"sleep_cryptostocks_balance" => 5,
		"sleep_themoneyconverter_ticker" => 5,
		"sleep_crypto-trade_ticker" => 1,
		"sleep_givemecoins" => 0,
		"sleep_vaultofsatoshi_ticker" => 5,
		"sleep_kraken_ticker" => 1,
		"sleep_bitmarket_pl_ticker" => 5,
		"sleep_givemecoins" => 0,
		"sleep_mupool_balance" => 5,
		"sleep_anxpro_ticker" => 1,
		"sleep_itbit_ticker" => 1,
		"max_job_executions" => 5,	// if a job fails to run more than this number of times, then explicitly mark it as failed
		"throttle_btcguild" => 30,	// only execute this job once every X seconds
		"throttle_blockchain" => 5,
		"external_sample_size" => 10000,	// what is the max number of jobs to sample when updating the external API page?

		"default_job_priority" => 10,
		"premium_job_priority" => 5,	// used in crontab and in premium upgrade job
		"job_test_priority" => 5,	// when an account is 'test'ed manually by a user, what priority will it have?

		// DDoS/abuse control
		"heavy_requests_seconds" => 10,

		// layout properties
		"default_graph_width" => 110, // in px, default *2
		"default_graph_height" => 110, // in px, default *2
		"default_user_graph_width" => 4,	// see _profile_add_graph.php
		"default_user_graph_height" => 2,	// see _profile_add_graph.php
		"default_user_graph_days" => 45,
		"graph_refresh_public" => 30,		// in minutes
		"graph_refresh_free" => 30,		// in minutes
		"graph_refresh_premium" => 1,		// in minutes

		// technical data
		"technical_period_max" => 365,

		// external URLs
		"ltc_address_url" => "http://explorer.litecoin.net/address/",
		"ltc_block_url" => "http://explorer.litecoin.net/chain/Litecoin/q/getblockcount",
		"btc_address_url" => "http://blockchain.info/address/",
		"ftc_address_url" => "http://explorer.feathercoin.com/address/",
		"ftc_block_url" => "http://explorer.feathercoin.com/chain/Feathercoin/q/getblockcount",
		"ppc_address_url" => "http://ppc.blockr.io/api/v1/address/info/",
		"ppc_block_url" => "http://ppc.blockr.io/api/v1/block/info/last",
		"nvc_address_url" => "https://explorer.novaco.in/address/",
		// not implemented yet!
		// "nvc_block_url" => "http://nvc.cryptocoinexplorer.com/chain/Novacoin/q/getblockcount",
		"nvc_block_url_html" => "https://explorer.novaco.in/",		// for obtaining block count manually
		"xpm_address_url" => "http://xpm.cryptocoinexplorer.com/address/",
		// not implemented yet!
		// "xpm_block_url" => "http://xpm.cryptocoinexplorer.com/chain/Primecoin/q/getblockcount",
		"xpm_block_url_html" => "http://xpm.cryptocoinexplorer.com/block/-1",		// for obtaining block count manually
		"trc_address_url" => "http://trc.cryptocoinexplorer.com/address/",
		// not implemented yet!
		// "trc_block_url" => "http://trc.cryptocoinexplorer.com/chain/Terracoin/q/getblockcount",
		"trc_block_url_html" => "http://trc.cryptocoinexplorer.com/block/-1",		// for obtaining block count manually
		"dog_address_url" => "http://dogechain.info//address/",
		"dog_block_url" => "http://dogechain.info//chain/Dogecoin/q/getblockcount",
		"mec_address_url" => "http://mega.rapta.net:2750/address/",
		"mec_block_url" => "http://mega.rapta.net:2750/chain/Megacoin/q/getblockcount",
		"xrp_address_url" => "https://ripple.com/graph/#",
		"nmc_address_url" => "http://namecha.in/address/",
		"nmc_block_url_html" => "http://namecha.in/",
		"dgc_address_url" => "http://dgc.blockr.io/api/v1/address/info/",
		"dgc_block_url" => "http://dgc.blockr.io/api/v1/block/info/last",
		"wdc_address_url" => "http://www.worldcoinexplorer.com/api/address/",
		"wdc_block_url" => "http://www.worldcoinexplorer.com/api/coindetails",
		"ixc_address_url" => "http://block.al.tcoin.info/address/",
		"ixc_block_url" => "http://block.al.tcoin.info/chain/Ixcoin/q/getblockcount",
		"vtc_address_url" => "http://vertexplorer.com/address/",
		"vtc_balance_url" => "http://vertexplorer.com/api/addr/",
		"net_address_url" => "http://explorer.netcoinfoundation.org/address/",
		"net_block_url" => "http://explorer.netcoinfoundation.org/chain/Netcoin/q/getblockcount",
		"hbn_address_url" => "http://162.217.249.198:1080/address/",
		"hbn_block_url" => "http://162.217.249.198:1080/chain/Hobonickels/q/getblockcount",
		"bc1_address_url" => "http://blackcha.in/address/",
		"bc1_balance_url" => "http://blackcha.in/api/address/",
		"bc1_block_url" => "http://blackcha.in/api/status?q=getInfo",
		"btc_confirmations" => 6,
		"ltc_confirmations" => 6,
		"ftc_confirmations" => 6,
		"ppc_confirmations" => 6,
		"nvc_confirmations" => 6,
		// "xpm_confirmations" => 6, -- not implemented yet!
		"trc_confirmations" => 6,
		"dog_confirmations" => 6,
		"mec_confirmations" => 6,
		// "xrp_confirmations" => 6, -- not supported!
		"nmc_confirmations" => 6,
		"dgc_confirmations" => 6,
		// "wdc_confirmations" => 6, -- not supported! #238
		"ixc_confirmations" => 6,
		// "vtc_confirmations" => 6, -- not supported
		"net_confirmations" => 6,
		"hbn_confirmations" => 6,

		// register Coinbase Applications through https://coinbase.com/oauth/applications
		"coinbase_client_id" => 'xxx',
		"coinbase_client_secret" => 'xxx',

		// application data for APIs
		"btce_example_api_key" => '...',
		"btce_example_api_secret" => '...',
		"vaultofsatoshi_info_currency_api_key" => '...',
		"vaultofsatoshi_info_currency_api_secret" => '...',
		"blockchain_api_key" => false,		// if you have one, optional
		"bitmarket_pl_example_api_key" => '...',
		"bitmarket_pl_example_api_secret" => '...',
		"anxpro_example_api_key" => '...',
		"anxpro_example_api_secret" => '...',

		// premium properties
		"premium_currencies" => array('btc', 'ltc'),
		"premium_btc_monthly" => 0.02,
		"premium_btc_yearly" => 0.2,
		"premium_ltc_monthly" => 1,
		"premium_ltc_yearly" => 10,
		"premium_reminder_days" => 7,	// when premium will expire in X days, send a reminder email
		"outstanding_reminder_hours" => 24,		// when a payment is outstanding, send a reminder every X hours
		"outstanding_abandon_days" => 7,		// when a payment is outstanding, abandon it after X days
		"premium_user_votes" => 10,		// how many extra votes do premium users get?

		"premium_btc_discount" => 0, // in %, e.g. 1=100%
		"premium_ltc_discount" => 0, // in %
		"premium_welcome" => false,		// redirect to the welcome page 'welcome' for premium users
		"new_user_premium_update_hours" => 24,	// how many hours to give a new user premium-level updates, or false for none
		"user_expiry_days" => 30,		// a free user must login every X days to prevent account being disabled
		"taxable_countries" => array(),		// a list of country codes to calculate 'taxable income' on /admin_financial

		// archive settings
		"archive_ticker_data" => "-31 days",
		"archive_summary_data" => "-31 days",
		"archive_balances_data" => "-31 days",

		// custom template settings
		"default_css" => "styles/default.css",
		"custom_css" => false,
		"forum_link" => "http://bitcointalk.org/",
		"blog_link" => "http://blog.cryptfolio.com/",
		"google_groups_announce" => "openclerk-announce",
		"version_history_link" => "https://groups.google.com/forum/#!forum/cryptfolio-announce",

	);

}

$global_get_premium_config = null;

/**
 * This function provides the premium account configuration for an Openclerk instance,
 * based on {@link #get_default_premium_config()} and {@link #get_premium_config_ext()}
 * It can be extended by defining a function 'get_premium_config_ext()'
 * and providing a new map of keys to values.
 *
 * Config values here will never change between calls so can be cached.
 */
function get_premium_config($key = null, $fail_if_missing = true) {
	global $global_get_premium_config;
	if ($global_get_premium_config === null) {
		$global_get_premium_config = array();
		if (function_exists('get_premium_config_ext')) {
			$global_get_premium_config += get_premium_config_ext();
		}
		$global_get_premium_config += get_default_premium_config();
	}

	if (!$fail_if_missing && !isset($global_get_premium_config[$key])) {
		return false;	// don't display an error
	}
	if (!isset($global_get_premium_config[$key])) {
		throw new Exception("Unknown premium config variable '" . htmlspecialchars($key) . "'");
	}

	return $global_get_premium_config[$key];
}

/**
 * This function provides the default premium account configuration for an Openclerk instance (#136).
 * It can be extended by defining a function 'get_premium_config_ext()'
 * and providing a new map of keys to values.
 *
 * Config values here will never change between calls so can be cached.
 */
function get_default_premium_config() {
	return array(
		"addresses_free" => 20,
		"addresses_premium" => 200,

		"accounts_free" => 5,
		"accounts_premium" => 200,

		"securities_free" => 2,
		"securities_premium" => 100,

		"graph_pages_free" => 1,
		"graph_pages_premium" => 20,

		"graphs_per_page_free" => 10,
		"graphs_per_page_premium" => 40,

		"graph_refresh_free" => get_site_config('graph_refresh_free'),	// in minutes
		"graph_refresh_premium" => get_site_config('graph_refresh_premium'),	// in minutes

		"refresh_queue_hours_free" => get_site_config('refresh_queue_hours'), // in hours
		"refresh_queue_hours_premium" => get_site_config('refresh_queue_hours_premium'), // in hours

		"summaries_free" => 5,
		"summaries_premium" => 100,

		"your_securities_free" => false,
		"your_securities_premium" => true,

		"notifications_free" => 2,
		"notifications_premium" => 100,

		"max_failures_free" => 5 * 5,			// remember a job executes up to 5 times
		"max_failures_premium" => 20 * 5,		// increase this value (e.g. 10x) because premium users are exposed to more errors

		// finance features
		"finance_accounts_free" => 20,
		"finance_accounts_premium" => 500,

		"finance_categories_free" => 10,
		"finance_categories_premium" => 100,
	);

}
