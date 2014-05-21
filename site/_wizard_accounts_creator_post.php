<?php

// process 'disable_creator'
if (require_post('disable_creator', false) && require_post('id', false)) {
	// does one exist?
	$q = db()->prepare("SELECT * FROM transaction_creators WHERE user_id=? AND exchange=? AND account_id=?");
	$q->execute(array(user_id(), $account_data['exchange'], require_post("id")));
	if ($q->fetch()) {
		// disable the existing one
		$q = db()->prepare("UPDATE transaction_creators SET is_disabled=1,is_disabled_manually=1 WHERE user_id=? AND exchange=? AND account_id=?");
		$q->execute(array(user_id(), $account_data['exchange'], require_post("id")));
	} else {
		// insert a new one that's disabled
		$q = db()->prepare("INSERT INTO transaction_creators SET is_disabled=1,is_disabled_manually=1,user_id=?,exchange=?,account_id=?");
		$q->execute(array(user_id(), $account_data['exchange'], require_post("id")));
	}

	$messages[] = t("Disabled transaction creation for :title.", array(':title' => $account_data['title']));

	set_temporary_messages($messages);
	redirect(url_for(require_post("callback")));
}

// process 'enable_creator'
if (require_post('enable_creator', false) && require_post('id', false)) {
	// does one exist?
	$q = db()->prepare("SELECT * FROM transaction_creators WHERE user_id=? AND exchange=? AND account_id=?");
	$q->execute(array(user_id(), $account_data['exchange'], require_post("id")));
	if ($q->fetch()) {
		// enable the existing one
		$q = db()->prepare("UPDATE transaction_creators SET is_disabled=0,is_disabled_manually=0 WHERE user_id=? AND exchange=? AND account_id=?");
		$q->execute(array(user_id(), $account_data['exchange'], require_post("id")));
	} else {
		// insert a new one that's enabled
		$q = db()->prepare("INSERT INTO transaction_creators SET user_id=?,exchange=?,account_id=?");
		$q->execute(array(user_id(), $account_data['exchange'], require_post("id")));
	}

	$messages[] = t("Enabled transaction creation for :title.", array(':title' => $account_data['title']));

	set_temporary_messages($messages);
	redirect(url_for(require_post("callback")));
}

// process 'reset_creator'
if (require_post('reset_creator', false) && require_post('id', false)) {
	// delete all existing creators
	$q = db()->prepare("DELETE FROM transaction_creators WHERE user_id=? AND exchange=? AND account_id=?");
	$q->execute(array(user_id(), $account_data['exchange'], require_post("id")));

	// delete all existing transactions
	$q = db()->prepare("DELETE FROM transactions WHERE user_id=? AND exchange=? AND account_id=?");
	$q->execute(array(user_id(), $account_data['exchange'], require_post("id")));

	$messages[] = t("Reset transactions for :title; historical transactions will soon be generated automatically.", array(':title' => $account_data['title']));

	set_temporary_messages($messages);
	redirect(url_for(require_post("callback")));
}
