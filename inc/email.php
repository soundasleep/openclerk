<?php

/**
 * Temporary mock User object until we have integrated openclerk/users.
 */
class MockEmailUser {
  function __construct($email, $name) {
    $this->email = $email;
    $this->name = $name;
  }

  function getEmail() {
    return $this->email;
  }

  function getName() {
    return $this->name;
  }
}

/**
 * The subject of the e-mail is obtained from the first line of the e-mail template.
 * Uses openclerk/emails for sending emails.
 *
 * @param $to an e-mail address or an Object that returns getEmail() (and optionally getName())
 * @throws MailerException if the mail could not be immediately sent (e.g. technical error, invalid e-mail address...)
 */
function send_email($to, $template_id, $args = array()) {
  // additionam
  $args['site_name'] = config('site_name');
  $args['site_url'] = config('absolute_url');
  $args['site_email'] = config('phpmailer_from');

  if (is_array($to)) {
    $to = new MockEmailUser($to["email"], $to["name"]);
  } else if (is_string($to)) {
    $to = new MockEmailUser($to, $to);
  }

  // TODO mock mailing

  Emails\Email::send($to, $template_id, $args);
}

/**
 * Wraps sending e-mails for a particular e-mail, so we can
 * - keep track of emails sent
 * - set locales eventually
 */
function send_user_email($user, $template_id, $args = array()) {
  $args["name"] = $user["name"] ? $user["name"] : $user["email"];

  send_email($user, $template_id, $args);

  if (isset($user['id'])) {
    $q = db()->prepare("UPDATE users SET emails_sent=emails_sent+1 WHERE id=?");
    $q->execute(array($user['id']));
  }
}
