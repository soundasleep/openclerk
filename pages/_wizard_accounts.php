<?php

// we will have set $account_type already
if (!isset($account_type)) {
  throw new Exception("account_type needs to be set");
}

// get all of our accounts
global $add_types;
global $add_type_names;
$accounts = array();
$add_types = array();
$add_type_names = array();
$previous_data = isset($_SESSION['wizard_data']) ? $_SESSION['wizard_data'] : array();
unset($_SESSION['wizard_data']);

foreach (account_data_grouped() as $label => $data) {
  foreach ($data as $key => $value) {
    if (isset($value['wizard']) && $value['wizard'] == $account_type['wizard']) {
      // we've found a valid account type
      $account_data = get_accounts_wizard_config($key);
      if (!$value['disabled']) {
        $add_types[] = $key;
        $add_type_names[$key] = call_user_func($account_type['exchange_name_callback'], $key) . (isset($value['suffix']) ? $value['suffix'] : "");
        $add_type_help[$key] = call_user_func($account_type['help_filename_callback'], $key);
      }

      $query = $value['query'] ? $value['query'] : "";
      $q = db()->prepare("SELECT * FROM " . $account_data['table'] . "
        WHERE user_id=? $query ORDER BY title ASC");
      $q->execute(array(user_id()));
      while ($r = $q->fetch()) {
        $r['exchange'] = $key;
        $r['khash'] = $account_data['khash'];
        $accounts[] = $r;
      }
    }
  }
}

// sort add_types by name
function _sort_by_exchange_name($a, $b) {
  global $add_type_names;
  return strcmp(strtolower($add_type_names[$a]), strtolower($add_type_names[$b]));
}
usort($add_types, '_sort_by_exchange_name');

$account_data = null;

?>

<div class="page_accounts">

<?php if ($account_type['wizard'] != 'offsets') { ?>
  <p>
  <?php
  $extra_hours = (int) (get_site_config('new_user_premium_update_hours') - ((time() - strtotime($user['created_at']))) / (60 * 60));
  echo t("As a :user, your :titles should be updated at least once every :hours:extra.",
    array(
      ':user' => $user['is_premium'] ? ht("premium user") : (user_is_new($user) ? ht("new user") : link_to(url_for('premium'), t("free user"))),
      ':titles' => $account_type['accounts'],
      ':hours' => plural("hour", user_is_new($user) ? get_site_config('refresh_queue_hours_premium') : get_premium_value($user, "refresh_queue_hours")),
      ':extra' => (user_is_new($user) && !$user['is_premium']) ? " " . t("(for the next :hours)", array(':hours' => plural("hour", $extra_hours))) : "",
    ));
    ?>
  </p>
<?php } ?>

<h2><?php echo t("Your :titles", array(':titles' => $account_type['titles'])); ?></h2>

<?php require(__DIR__ . "/_sort_buttons.php"); ?>

<table class="standard standard_account_list">
<thead>
  <tr>
    <th class="type"><?php echo $account_type['first_heading']; ?></th>
    <th class="title"><?php echo ht("Title"); ?></th>
    <?php foreach ($account_type['display_headings'] as $value) { ?>
      <th class="headings"><?php echo htmlspecialchars($value); ?></th>
    <?php } ?>
    <th class="added"><?php echo ht("Added"); ?></th>
    <?php if ($account_type['has_balances']) { ?>
    <th class="last_checked"><?php echo ht("Checked"); ?></th>
    <th class="balances"><?php echo ht("Balances"); ?></th>
    <?php } ?>
    <?php if ($account_type['hashrate']) { echo "<th class=\"hashrate\">" . ht("Hashrate") . "</th>"; } ?>
    <?php if ($account_type['has_transactions']) { ?>
    <th class="transactions"><?php echo ht("Transactions"); ?></th>
    <?php } ?>
    <th class="buttons"></th>
  </tr>
</thead>
<tbody>
<?php
// uses $accounts to generate rows;
// this is in an include so we can also use it in wizard_accounts_callback
require(__DIR__ . "/_wizard_accounts_rows.php");
?>
</tbody>
</table>

<div class="columns2">
<div class="column">

<h2><?php echo ht("Add new :title", array(':title' => $account_type['title'])); ?></h2>

<form action="<?php echo htmlspecialchars(url_for('wizard_accounts_post')); ?>" method="post" class="wizard-add-account">
  <table class="standard" id="wizard_account_table">
  <tr>
    <th><label for="type"><?php echo ht($account_type['first_heading']); ?>:</label></th>
    <td>
      <select id="type" name="type">
      <?php foreach ($add_types as $exchange) {
        echo "<option value=\"" . htmlspecialchars($exchange) . "\"" . ($exchange == require_get("exchange", false) ? " selected" : "")  . ">";
        echo htmlspecialchars($add_type_names[$exchange]);
        echo "</option>\n";
      } ?>
      </select>
    </td>
  </tr>
  <tr>
    <th><label for="title"><?php echo ht("Title:"); ?></label></th>
    <td><input id="title" type="text" name="title" size="18" maxlength="64" value="<?php echo htmlspecialchars(require_get("title", "")); ?>"> <?php echo ht("(optional)"); ?></td>
  </tr>
  <tr id="add_account_template" style="display:none;">
    <th><label for="ignored">Parameter:</label></th>
    <td><input id="ignored" type="text" name="ignored" size="18" maxlength="64" value=""></td>
  </tr>
  <tr id="add_account_template_dropdown" style="display:none;">
    <th><label for="ignored">Parameter:</label></th>
    <td><select id="ignored" name="ignored">
      <option id="option_template"></option>
    </select></td>
  </tr>
  <tr id="add_account_template_checkbox" style="display:none;">
    <th></th>
    <td>
      <input id="ignored" type="checkbox" name="ignored" value="1">
      <label for="ignored">Parameter</label>
    </td>
  </tr>
  <tr id="add_account_note_template" style="display:none;" class="note-text">
    <th></th>
    <td><strong><?php echo t("NOTE:"); ?></strong> <label>Warning</label></td>
  </tr>
  <tr class="buttons">
    <td colspan="2" class="buttons">
      <input type="submit" name="add" value="<?php echo htmlspecialchars($account_type['add_label']); ?>" class="add">
      <input type="hidden" name="callback" value="<?php echo htmlspecialchars($account_type['url']); ?>">

      <?php if (isset($account_type['add_help'])) { ?>
      <div class="help">
        <a href="<?php echo htmlspecialchars(url_for('help/' . $account_type['add_help'])); ?>"><?php echo ht("Add :an_account not listed here", array(':an_account' => htmlspecialchars($account_type['a']) . " " . htmlspecialchars($account_type['title']))); ?></a>
      </div>
      <?php } ?>
    </td>
  </tr>
  </table>
</form>

<script type="text/javascript">
function available_exchanges() {
  return [
<?php foreach ($add_types as $exchange) {
  $config = get_accounts_wizard_config($exchange);
  echo "{ 'exchange' : " . json_encode($exchange) . ", \n";
  echo " 'inputs' : [";
  foreach ($config['inputs'] as $key => $input) {
    if (isset($input['interaction']) && $input['interaction']) {
      // we can fill this field in with user interaction; ignore
      continue;
    }

    echo "{ 'key': " . json_encode($key) . ", 'title' : " . json_encode($input['title']);
    if (isset($input['dropdown']) && $input['dropdown']) {
      $callback = $input['dropdown'];
      echo ", 'dropdown' : " . json_encode($callback());
    }
    if (isset($input['checkbox']) && $input['checkbox']) {
      echo ", 'checkbox' : " . json_encode(true);
    }
    if (isset($input['style_prefix']) && $input['style_prefix']) {
      echo ", 'style_prefix' : " . json_encode($input['style_prefix']);
    }
    if (isset($input['default']) && $input['default']) {
      echo ", 'default' : " . json_encode($input['default']);
    }
    if (isset($input['note']) && $input['note']) {
      echo ", 'note' : " . json_encode($input['note']);
    }
    echo ", 'length' : " . json_encode(isset($input['length']) ? $input['length'] : 64) . "},";
  }
  echo "]";
  echo "},\n";
} ?>
  ];
}
function previous_data() {
  return <?php echo json_encode($previous_data); ?>;
}
</script>

</div>
<div class="column">
<h2><?php echo ht("Help"); ?></h2>

<div id="accounts_help_target"><?php echo ht("Select an exchange to display help..."); ?></div>

</div>
</div>

<div style="display:none;" id="accounts_help">
<?php foreach ($add_types as $exchange) { ?>
  <div id="accounts_help_<?php echo htmlspecialchars($exchange); ?>">
  <?php require_template($add_type_help[$exchange]); ?>
  <?php if ($account_type['wizard'] != 'offsets') { ?>
    <span class="more_help"><a href="<?php echo htmlspecialchars(url_for('help/' . $exchange)); ?>"><?php echo ht("More help"); ?></a></span>
  <?php } ?>
  </div>
<?php } ?>
</div>

<div style="clear:both;"></div>

</div>
