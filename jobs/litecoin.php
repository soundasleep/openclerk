<?php

/**
 * Litecoin Explorer job (LTC).
 * The litecoin explorer (through Abe) does not provide an expressive enough API
 * for things like /address/x/balance?confirmations=6
 * So, we need to emulate this by periodically querying Explorer for the current block number,
 * and HTML parsing /address pages for transactions, and reversing any transactions within
 * a block number less than the current block number (minus some number).
 * We can go zero-confirmations for users, but for payment we need to have confirmations.
 */

$abe_data = array(
	'currency' => 'ltc',
	'block_table' => 'litecoin_blocks',
	'block_job' => 'litecoin_block',
	'explorer_url' => get_site_config('ltc_address_url'),
	'confirmations' => get_site_config('ltc_confirmations'),
);

require(__DIR__ . "/_abe_blockchain.php");
