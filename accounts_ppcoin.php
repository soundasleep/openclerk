<?php

require("inc/global.php");
require_login();

require("layout/templates.php");

$account_data = array(
	'premium_group' => 'ppcoin',
	'title' => 'PPC address',
	'titles' => 'PPC addresses',
	'table' => 'addresses',
	'currency' => 'ppc',
	'callback' => 'is_valid_ppc_address',
	'url' => 'accounts_ppcoin',
	'job_type' => 'ppcoin',
	'address_callback' => 'ppc_address',
);

require("_accounts_blockchain.php");
