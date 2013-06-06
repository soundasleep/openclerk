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
	'client' => 'Litecoin-Qt',
	'step1' => 'litecoinqt1.png',
	'step2' => 'litecoinqt2.png',
);

require("_accounts_blockchain.php");
