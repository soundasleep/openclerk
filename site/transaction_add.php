<?php

// add a manual transaction

require(__DIR__ . "/../inc/global.php");
require_login();

$date = (string) require_post("date");
$account = (int) require_post("account", false);
$category = (int) require_post("category", false);
$description = (string) require_post("description", "");
$reference = (string) require_post("reference", "");
$value1 = (string) require_post("value1");
$currency1 = (string) require_post("currency1");
$value2 = (string) require_post("value2", false);
$currency2 = (string) require_post("currency2", false);

if (!$value2) {
	$value2 = null;
}
if (!$currency2) {
	$currency2 = null;
}

$page_args = require_post("page_args", false);

$messages = array();
$errors = array();

if (!in_array($currency1, get_all_currencies())) {
	$errors[] = t("':currency' is not a valid currency", array(':currency' => $currency1));
}
if (!strtotime($date)) {
	$errors[] = t("':date' is not a valid date", array(':date' => $date));
}
if (!$value1) {
	$errors[] = t("You need to specify a transaction value.");
}
if ($value2 && !$currency2) {
	$errors[] = t("You need to select a second currency in order to add a second transaction amount.");
}

// insert
if (!$errors) {
	$q = db()->prepare("INSERT INTO transactions SET user_id=:user_id, is_automatic=0, transaction_date=:date,
			exchange=:exchange, account_id=:account_id, category_id=:category_id, description=:description,
			reference=:reference, value1=:value1, currency1=:currency1, value2=:value2, currency2=:currency2,
			transaction_date_day=to_days(:date)");
	$q->execute(array(
		'user_id' => user_id(),
		'date' => $date,
		'exchange' => 'account',
		'account_id' => $account,
		'category_id' => $category,
		'description' => $description,
		'reference' => $reference,
		'value1' => $value1,
		'currency1' => $currency1,
		'value2' => $value2,
		'currency2' => $currency2,
	));
	$id = db()->lastInsertId();

	$messages[] = t("Added transaction.");
}

set_temporary_messages($messages);
set_temporary_errors($errors);

$args = array();
if (is_array($page_args)) {
	foreach ($page_args as $key => $value) {
		$args[$key] = $value;
	}
}

if ($errors) {
	$args += array(
		'date' => $date,
		'account' => $account,
		'category' => $category,
		'description' => $description,
		'reference' => $reference,
		'value1' => $value1,
		'currency1' => $currency1,
		'value2' => $value2,
		'currency2' => $currency2,
	);
	redirect(url_for("your_transactions", $args));
} else {
	$args['highlight'] = $id;
	redirect(url_for('your_transactions#transaction_' . $id, $args));
}
