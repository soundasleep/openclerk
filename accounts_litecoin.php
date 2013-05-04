<?php

require("inc/global.php");
require_login();

require("layout/templates.php");

$account_data = array(
	'premium_group' => 'litecoin',
	'title' => 'LTC address',
	'titles' => 'LTC addresses',
	'table' => 'addresses',
	'currency' => 'ltc',
	'callback' => 'is_valid_ltc_address',
	'url' => 'accounts_litecoin',
	'job_type' => 'litecoin',
	'address_callback' => 'ltc_address',
);

require("_accounts_blockchain.php");
