<?php

$email = require_get("email", false);
$hash = require_get("hash", false);

// check hash
if ($hash !== md5(get_site_config('unsubscribe_salt') . $email)) {
  throw new Exception(t("Invalid hash - please recheck the link in your e-mail."));
}

// if any accounts have a password enabled, they simply cannot unsubscribe until they have at least one
// openid identity
$q = db()->prepare("SELECT * FROM users WHERE email=?");
$q->execute(array($email));
$users = $q->fetchAll();
$has_identity = false;

foreach ($users as $user) {
  if ($user['password_hash']) {
    $q = db()->prepare("SELECT * FROM openid_identities WHERE user_id=? LIMIT 1");
    $q->execute(array($user['id']));

    $has_identity = $q->fetch();
    if (!$has_identity) {

      page_header(t("Unsubscribe unsuccessful"), "page_unsubscribe");

      ?>
      <h1><?php echo ht("Unsubscribe unsuccessful"); ?></h1>

      <p class="error">
        <?php
        echo t("Your e-mail address, :email, cannot be removed from this site, as this e-mail address is being used as login information for an account.",
          array(
            ':email' => '<a href="mailto:' . htmlspecialchars($email) . '">' . htmlspecialchars($email) . '</a>',
          ));
        ?>
      </p>

      <p>
        <?php
        echo t("You will need to :add_openid to this account in order to remove this e-mail address.",
          array(
            ':add_openid' => link_to(url_for('user#user_openid'), t("add an OpenID identity")),
          ));
        ?>
      </p>

      <?php
      page_footer();

      return;

    }
  }
}

$query = db()->prepare("UPDATE users SET email=NULL,updated_at=NOW() where email=? AND ISNULL(password_hash)=1");
$query->execute(array($email));

page_header(t("Unsubscribe"), "page_unsubscribe");

?>
<h1><?php echo ht("Unsubscribe"); ?></h1>

<p class="success">
  <?php
  echo t("Your e-mail address, :email, has been completely removed from this site, and you will no longer receive any information or notifications via e-mail.",
    array(
      ':email' => '<a href="mailto:' . htmlspecialchars($email) . '">' . htmlspecialchars($email) . '</a>',
    ));
  ?>
</p>

<p>
  <?php
  echo t("If you have accidentally removed your e-mail from your account, you will need to login and :add_it_back, in order to resume e-mail notifications.",
    array(
      ':add_it_back' => link_to(url_for('user'), t("add your e-mail address back to your profile")),
    ));
  ?>
</p>

<?php
page_footer();
