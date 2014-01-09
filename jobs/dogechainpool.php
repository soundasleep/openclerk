<?php

/**
 * Dogechain Pool pool balance job.
 * Uses php-mpos mining pool.
 */

$exchange = "dogechainpool";
$currency = 'dog';
$table = "accounts_dogechainpool";
$api_url = "https://pool.dogechain.info/index.php?page=api&";

// must force SSL3: SSL2 causes an "Could not get reply: error:14077458:SSL routines:SSL23_GET_SERVER_HELLO:reason(1112)" error
// also see http://carnivore.it/2011/10/07/error_14077458_ssl_routines_ssl23_get_server_hello_reason_1112
$curl_options = array(
	CURLOPT_SSLVERSION => 3,
);

require(__DIR__ . "/_mpos_pool.php");
