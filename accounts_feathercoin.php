<?php

require("inc/global.php");
require_login();

require("layout/templates.php");

$account_data = array(
	'premium_group' => 'feathercoin',
	'title' => 'FTC address',
	'titles' => 'FTC addresses',
	'table' => 'addresses',
	'currency' => 'ftc',
	'callback' => 'is_valid_ftc_address',
	'url' => 'accounts_feathercoin',
	'job_type' => 'feathercoin',
	'address_callback' => 'ftc_address',
);

require("_accounts_blockchain.php");
