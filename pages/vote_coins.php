<?php

/**
 * Interface for allowing users to vote for coins (#264).
 */

require(__DIR__ . "/../graphs/util.php");

if (user_logged_in()) {
  $user = get_user(user_id());
}

$messages = array();
$errors = array();

// get all coins
$q = db()->prepare("SELECT * FROM vote_coins ORDER BY code ASC");
$q->execute(array());
$coins = array();
$last_calculated = false;
while ($coin = $q->fetch()) {
  $coins[$coin['id']] = $coin;
  $last_calculated = max($last_calculated, $coin['last_updated']);
}

$my_coins = array();
if (user_logged_in()) {
  // perform post logic
  if (require_post("update_votes", false)) {
    // delete all existing votes for this user
    $q = db()->prepare("DELETE FROM vote_coins_votes WHERE user_id=?");
    $q->execute(array(user_id()));

    // create new votes
    $my_coins = require_post("coins", array());
    foreach ($my_coins as $id) {
      if (isset($coins[$id])) {
        $q = db()->prepare("INSERT INTO vote_coins_votes SET user_id=?,coin_id=?,created_at=NOW()");
        $q->execute(array(user_id(), $id));
      } else {
        $errors[] = t("Unknown coin :id.", array(':id' => $id));
      }
    }

    $messages[] = t("Updated your votes.");
  } else {
    // get my voted coins
    $q = db()->prepare("SELECT * FROM vote_coins_votes WHERE user_id=?");
    $q->execute(array(user_id()));
    $my_coins = array();
    while ($coin = $q->fetch()) {
      $my_coins[] = $coin['coin_id'];
    }
  }
}

page_header(t("Coin Voting"), "page_vote_coins", array('js' => array('accounts')));

?>

<?php require_template('vote_coins'); ?>

<?php require(__DIR__ . "/_sort_buttons.php"); ?>

<form action="<?php echo htmlspecialchars(url_for('vote_coins')); ?>" method="post">
<table class="standard standard_account_list">
<thead>
  <tr>
    <th class=""><?php echo ht("Currency"); ?></th>
    <th class=""><?php echo ht("Title"); ?></th>
    <th class="number default_sort_down"><?php echo ht("Votes"); ?></th>
    <th class="number"><?php echo ht("Users"); ?></th>
    <?php if (user_logged_in()) { ?>
    <th class="buttons"><?php echo ht("Your Vote"); ?></th>
    <?php } ?>
  </tr>
</thead>
<tbody>
  <?php foreach ($coins as $coin) { ?>
  <tr class="<?php echo in_array($coin['id'], $my_coins) ? "voted-coin" : ""; ?>">
    <td class=""><?php echo htmlspecialchars($coin['code']); ?></td>
    <td class=""><?php echo htmlspecialchars($coin['title']); ?></td>
    <td class="number"><?php echo number_format($coin['total_votes'] * get_site_config('vote_coins_multiplier')); ?></td>
    <td class="number"><?php echo number_format($coin['total_users']); ?></td>
    <?php if (user_logged_in()) { ?>
    <td class="buttons">
      <input type="checkbox" name="coins[]" value="<?php echo htmlspecialchars($coin['id']); ?>"<?php echo in_array($coin['id'], $my_coins) ? " checked " : ""; ?>>
    </td>
    <?php } ?>
  </tr>
  <?php } ?>
</tbody>
<?php if (user_logged_in()) { ?>
<tfoot>
  <tr class="buttons">
    <td colspan="5">
      <input type="submit" name="update_votes" value="<?php echo ht("Update votes"); ?>">
    </td>
  </tr>
</tfoot>
<?php } ?>
</table>
</form>


<?php

page_footer();
