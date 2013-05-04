<?php

require("inc/global.php");
require_login();

require("layout/templates.php");

$account_data = array(
	'premium_group' => 'blockchain',
	'title' => 'BTC address',
	'titles' => 'BTC addresses',
	'table' => 'addresses',
	'currency' => 'btc',
	'callback' => 'is_valid_btc_address',
	'url' => 'accounts_blockchain',
	'job_type' => 'blockchain',
	'address_callback' => 'btc_address',
);

require("_accounts_blockchain.php");
