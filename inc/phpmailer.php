<?php

require(__DIR__ . "/phpmailer/class.phpmailer.php");

function phpmailer($to, $to_name, $subject, $message) {
  $mail = new PHPMailer();

  $mail->IsSMTP();                                      // set mailer to use SMTP
  $mail->Host = get_site_config('phpmailer_host');  // specify main and backup server
  $mail->SMTPAuth = true;     // turn on SMTP authentication
  $mail->Username = get_site_config('phpmailer_username');  // SMTP username
  $mail->Password = get_site_config('phpmailer_password'); // SMTP password

  $mail->From = get_site_config('phpmailer_from');
  $mail->FromName = get_site_config('phpmailer_from_name');
  $mail->Sender = get_site_config('phpmailer_from');
  $mail->AddAddress($to, $to_name);
  if (get_site_config('phpmailer_reply_to')) {
  	$mail->AddReplyTo(get_site_config('phpmailer_reply_to'));
  }

  if (get_site_config('phpmailer_bcc', false)) {
  	$mail->AddBCC(get_site_config('phpmailer_bcc'));
  }

  $mail->Subject = $subject;
  $mail->Body    = $message;

  // set language
  /*
  init_phpmailer_language();
  global $PHPMAILER_LANG;
  $mail->language = $PHPMAILER_LANG;
  */

  if(!$mail->Send()) {
  	throw new MailerException("Message could not be sent: " . $mail->ErrorInfo);
  }
}

$PHPMAILER_LANG = array(); // will load later

/**
 * Initialise the phpmailer language properties as necessary. Uses values from the database.
 */
function init_phpmailer_language() {

	global $PHPMAILER_LANG;
	if ($PHPMAILER_LANG)
		return;

	$keys = array("provide_address", "mailer_not_supported", "execute", "instantiate",
		"authenticate", "from_failed", "recipients_failed", "data_not_accepted",
		"connect_host", "file_access", "file_open", "encoding");

	foreach ($keys as $key) {
		$PHPMAILER_LANG[$key] = language("phpmailer." . $key, 'en');
	}

}

class MailerException extends Exception { }
