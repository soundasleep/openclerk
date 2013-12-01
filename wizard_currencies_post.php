<?php

/**
 * Process selected currencies and redirect to the next wizard page if successful.
 */

require(__DIR__ . "/inc/global.php");
require_login();

$user = get_user(user_id());
require_user($user);

$errors = array();
$messages = array();

// get all of our accounts
$accounts = user_limits_summary(user_id());

$currencies = require_post("currencies", array() /* in case no cryptocurrencies are selected (which wouldn't make much sense probably) */);
$exchanges = require_post("exchanges", array() /* in case no fiat currencies are selected */);

$cryptos = get_all_cryptocurrencies();
$fiats = get_all_fiat_currencies();
$commodities = get_all_commodity_currencies();

// go through all fiat currencies and, if no exchange is selected, select a default one
foreach ($fiats as $c) {
	if (in_array($c, $currencies)) {
		$found = false;
		foreach ($exchanges as $e) {
			$prefix = "summary_" . $c . "_";
			if (substr($e, 0, strlen($prefix)) == $prefix) {
				// found one
				$found = true;
			}
		}
		if (!$found) {
			$exchanges[] = "summary_" . $c . "_" . get_default_currency_exchange($c);
		}
	} else {
		// or, if the currency isn't selected, remove any default exchanges
		$result = array();
		foreach ($exchanges as $e) {
			$prefix = "summary_" . $c . "_";
			if (!(substr($e, 0, strlen($prefix)) == $prefix)) {
				$result[] = $e;
			}
		}
		$exchanges = $result;
	}
}

// go through all crypto currencies and add just summary_CUR
foreach ($currencies as $c) {
	$exchanges[] = "summary_" . $c;
}

// don't do this with commodity currencies, since summary_CUR is actually a valid currency

// strip out any invalid exchanges
$exchanges = array_intersect($exchanges, array_keys(get_summary_types()));

// and make it unique
$exchanges = array_unique($exchanges);

// make sure this user can have this many summaries
if (count($exchanges) > get_premium_value($user, 'summaries')) {
	$errors[] = "Could not update currencies: too many currencies selected (" . number_format(count($exchanges)) . " selected out of a maximum of " . number_format(get_premium_value($user, 'summaries')) . ")." .
			($user['is_premium'] ? "" : " To add more currencies, upgrade to a <a href=\"" . htmlspecialchars(url_for('premium')) . "\">premium account</a>.");
	set_temporary_messages($messages);
	set_temporary_errors($errors);
	redirect(url_for('wizard_currencies'));	// go back
}

// get all the currencies we're currently interested in
// (so we know if we need to reset managed graphs)
$q = db()->prepare("SELECT * FROM summaries WHERE user_id=?");
$q->execute(array(user_id()));
$existing = array();
while ($summary = $q->fetch()) {
	$existing[] = $summary['summary_type'];
}

// delete any old currencies that no longer exist
$q = db()->prepare("SELECT id, summary_type FROM summaries WHERE user_id=?");
$q->execute(array(user_id()));
$to_delete = array();
while ($summary = $q->fetch()) {
	$key = array_search($summary['summary_type'], $exchanges);
	if ($key === false) {
		$to_delete[] = $summary['id'];
	} else {
		// remove it from the list of currencies to add
		unset($exchanges[$key]);
	}
}
foreach ($to_delete as $id) {
	$q = db()->prepare("DELETE FROM summaries WHERE user_id=? AND id=?");
	$q->execute(array(user_id(), $id));
	// TODO delete old summary_instances? add summary_id to summary_instances?
}

// insert in remaining currencies
foreach ($exchanges as $type) {
	$q = db()->prepare("INSERT INTO summaries SET user_id=?, summary_type=?, created_at=NOW()");
	$q->execute(array(user_id(), $type));
}

// if we've changed our summary types, then we should update our managed graphs if necessary
if (!array_equals($existing, $exchanges) && ($user['graph_managed_type'] == 'auto' || $user['graph_managed_type'] == 'managed')) {
	$q = db()->prepare("UPDATE users SET needs_managed_update=1 WHERE id=?");
	$q->execute(array(user_id()));
}

// $messages[] =
set_temporary_errors($errors);
redirect(url_for('wizard_accounts'));	// go forward
