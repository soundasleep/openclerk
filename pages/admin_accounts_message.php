<?php

/**
 * Admin page for displaying the status of accounts in the system, allowing us to see
 * if particular classes of accounts are failing.
 */

require_admin();

require(__DIR__ . "/../layout/templates.php");
require(__DIR__ . "/../layout/graphs.php");

$messages = array();
$errors = array();

$exchange = require_post("exchange");
$message = require_post("message", "");

// enabling accounts?
if ($exchange && $message) {
  $account_data = get_account_data($exchange);

  // we re-enable ALL accounts, not just accounts belonging to active users, so that when a disabled user
  // logs back in, they automatically get their disabled accounts disabled as well
  $q = db()->prepare("SELECT t.*, users.email, users.name AS users_name, users.is_disabled AS user_is_disabled FROM " . $account_data['table'] . " t
    JOIN users ON t.user_id=users.id
    WHERE t.is_disabled=1");
  $q->execute();
  $count = 0;
  $accounts = $q->fetchAll();
  foreach ($accounts as $account) {
    // email the user if their account is not disabled
    if (!$account['user_is_disabled']) {
      if ($account['email']) {
        $user_temp = array('email' => $account['email'], 'name' => $account['users_name']);

        send_user_email($user_temp, "account_failed_message", array(
          "name" => ($account['users_name'] ? $account['users_name'] : $account['email']),
          "exchange" => get_exchange_name($exchange),
          "message" => $message,
          "label" => $account_data['label'],
          "labels" => $account_data['labels'],
          "title" => (isset($account['title']) && $account['title']) ? "\"" . $account['title'] . "\"" : "untitled",
          "url" => absolute_url(url_for("wizard_accounts")),
        ));
        $messages[] = "Sent message to " . htmlspecialchars($account['email']);

      }
    }
    $count++;
  }

  $messages[] = "Sent messages to " . plural("account", $count) . ".";
}

page_header("Admin: Message Failed Accounts", "page_admin_accounts_message", array('js' => array('accounts')));

?>

<h1>Message Failed Accounts</h1>

<p class="backlink"><a href="<?php echo htmlspecialchars(url_for('admin_accounts')); ?>">&lt; Back to Manage Accounts</a></p>

<form action="<?php echo htmlspecialchars(url_for('admin_accounts_message')); ?>" method="post">
<table class="form">
<tr>
  <th>Exchange:</th>
  <td><?php echo get_exchange_name($exchange); ?></td>
</tr>
<tr>
  <th>Message:</th>
  <td><input type="text" name="message" size="64" value="<?php echo htmlspecialchars($message); ?>"></td>
</tr>
<tr>
  <td colspan="2" class="buttons">
    <input type="hidden" name="exchange" value="<?php echo htmlspecialchars($exchange); ?>">
    <input type="submit" value="Send message to failed accounts">
  </td>
</tr>
</table>
</form>

<?php
page_footer();
