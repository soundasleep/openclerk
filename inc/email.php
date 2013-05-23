<?php

require("inc/phpmailer.php");

/**
 * The subject of the e-mail is obtained from the first line of the e-mail template.
 *
 * @throws MailerException if the mail could not be immediately sent (e.g. technical error, invalid e-mail address...)
 */
function send_email($to_email, $to_name, $template_id, $args = array()) {
	if (!file_exists("emails/" . $template_id . ".txt")) {
		throw new Exception("Email template $template_id does not exist");
	}

	$template = file_get_contents("emails/" . $template_id . ".txt");

	// replace variables
	// TODO add escaping
	$args["site_name"] = get_site_config('site_name');
	$args["site_url"] = absolute_url("");
	$args["site_email"] = get_site_config('site_email');
	foreach ($args as $key => $value) {
		$template = str_replace('{$' . $key . '}', $value, $template);
	}

	// strip out the subject
	$template = explode("\n", $template, 2);
	$subject = $template[0];
	$template = $template[1];

	// now send the email
	// may throw MailerException
	phpmailer($to_email, $to_name, $subject, $template);

	// TODO maybe insert key into database
}
