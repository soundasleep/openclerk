<?php

function recaptcha_insert() {
	require_once('inc/recaptcha/recaptchalib.php');
	$publickey = get_site_config('recaptcha_public_key'); // you got this from the signup page
	echo recaptcha_get_html($publickey);
}

function recaptcha_valid() {
	require_once('inc/recaptcha/recaptchalib.php');
	$privatekey = get_site_config('recaptcha_private_key');
	$resp = recaptcha_check_answer($privatekey,
								$_SERVER["REMOTE_ADDR"],
								$_POST["recaptcha_challenge_field"],
								$_POST["recaptcha_response_field"]);

	return $resp->is_valid;
}
