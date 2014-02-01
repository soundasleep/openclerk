<?php

/**
 * Callback URI for Coinbase OAuth2.
 *
 * This is a little hack to allow /coinbase (which will be called as a GET)
 * to repopulate everything and pass it along to wizard_accounts_post
 */

$_POST['title'] = isset($_GET["title"]) ? $_GET["title"] : null;
$_POST['callback'] = 'wizard_accounts_exchanges';
$_POST['type'] = 'coinbase';
$_POST['add'] = true;

require(__DIR__ . "/wizard_accounts_post.php");
