<?php

/**
 * Defines templating helper functions.
 */

use \Pages\PageRenderer;
use \Openclerk\Router;

/**
 * Use {@link PageRenderer#requireTemplate} without having to declare the namespace
 * in a template as well.
 */
function require_template($id, $args = array()) {
  PageRenderer::requireTemplate($id, $args);
}

/**
 * Display any errors or messages, including those passed through temporary_messages/errors.
 */
function display_messages() {
  global $messages;
  global $errors;

  if (!isset($messages)) $messages = array();
  if (!isset($errors)) $errors = array();

  if (get_temporary_messages()) {
    $messages = array_join($messages, get_temporary_messages());
  }
  if (get_temporary_errors()) {
    $errors = array_join($errors, get_temporary_errors());
  }
  // if admin, load any admin messages
  if (is_admin()) {
    $q = db()->prepare("SELECT * FROM admin_messages WHERE is_read=0 ORDER BY created_at ASC");
    $q->execute();
    while ($message = $q->fetch()) {
      $messages[] = "Admin message: " . $message['message'] /* assumes encoded */ . " (<a href=\"" . htmlspecialchars(url_for('admin_message', array('id' => $message['id']))) . "\">hide</a>)";
    }
  }

  if ($messages) { ?>
<div class="message">
<ul>
  <?php foreach ($messages as $m) { echo "<li>" . $m . "</li>"; } /* do NOT accept user input for messages! */ ?>
</ul>
</div>
<?php }
  if ($errors) { ?>
<div class="error">
<ul>
  <?php foreach ($errors as $m) { echo "<li>" . $m . "</li>"; } /* do NOT accept user input for messages! */ ?>
</ul>
</div>
<?php }

}

/**
 * Return a HTML link for inspecting a given cryptocurrency address.
 */
function crypto_address($currency, $address) {
  foreach (\DiscoveredComponents\Currencies::getAddressCurrencies() as $cur) {
    if ($cur === $currency) {
      $instance = \DiscoveredComponents\Currencies::getInstance($cur);
      return "<span class=\"address " . $currency . "_address\"><code>" . htmlspecialchars($address) . "</code>
        <a class=\"inspect\" href=\"" . htmlspecialchars($instance->getBalanceURL($address)) . "\" title=\"Inspect with " . htmlspecialchars($instance->getExplorerName()) . "\">?</a>
      </span>";
    }
  }

  foreach (get_blockchain_currencies() as $explorer => $currencies) {
    foreach ($currencies as $cur) {
      if ($cur == $currency) {
        return "<span class=\"address " . $currency . "_address\"><code>" . htmlspecialchars($address) . "</code>
          <a class=\"inspect\" href=\"" . htmlspecialchars(sprintf(get_site_config($currency . "_address_url"), $address)) . "\" title=\"Inspect with " . htmlspecialchars($explorer) . "\">?</a>
        </span>";
      }
    }
  }

  return htmlspecialchars($address);
}
