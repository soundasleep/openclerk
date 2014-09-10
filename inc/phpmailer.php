<?php

require(__DIR__ . "/../vendor/phpmailer/phpmailer/PHPMailerAutoload.php");

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

	if(!$mail->Send()) {
		throw new MailerException("Message could not be sent: " . $mail->ErrorInfo);
	}
}

class MailerException extends Exception { }
