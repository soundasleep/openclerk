<?php

/**
 * This page does all of the real hard work - taking database values and translating them
 * into graphs and such.
 */

require(__DIR__ . "/../layout/graphs.php");
require_login();

require(__DIR__ . "/../layout/templates.php");

$user = get_user(user_id());
require_user($user);

$messages = array();
$errors = array();

// is there a command to the page?
// TODO eventually replace this with ajax stuff
$enable_editing = false;
require(__DIR__ . "/_profile_move.php");

// do we need to replace/update managed graphs?
require(__DIR__ . "/../graphs/managed.php");
if ($user['needs_managed_update']) {
  update_user_managed_graphs($user);
}

require(__DIR__ . "/_profile_common.php");

// a user might not have any pages displayed
$page_title_prefix = t("Your Reports:") . " ";
$enable_editing_dialog = true;
if ($pages) {
  if (require_get("securities", false)) {
    // displaying securities page?

    $graphs = array();
    $page_title = t("Your Securities");
    $page_id = "securities";
    $page_title_prefix = "";
    $enable_editing_dialog = false;

    $id_counter = 0;    // $graph[id] needs to be set for unique graph HTML IDs

    // premium check
    if (get_premium_value($user, 'your_securities')) {

      // assumes each securities_XXX table has a 'name'
      $tables = get_security_exchange_tables();
      $account_data = account_data_grouped();

      foreach (get_security_exchange_tables() as $exchange => $table) {
        $q = db()->prepare("SELECT *, ss.id AS security_id FROM user_securities AS t
          JOIN security_exchange_securities AS ss ON t.security=ss.security
          WHERE t.exchange=? AND user_id=? AND is_recent=1");
        $q->execute(array($exchange, user_id()));
        $securities = $q->fetchAll();

        // and individual securities
        if (isset($account_data['Individual Securities']['individual_' . $exchange])) {
          // TODO temporary hack fix
          if ($exchange != "btct" && $exchange != "havelock") {
            continue;
          }

          $ind_account = $account_data['Individual Securities']['individual_' . $exchange];
          $table_name = $ind_account['table'];
          $q = db()->prepare("SELECT t.*, ss.id AS security_id FROM $table_name AS t
            JOIN security_exchange_securities AS ss ON t.security=ss.security AND ss.exchange=?
            WHERE t.user_id=?");
          $q->execute(array($exchange, user_id()));
          $securities2 = $q->fetchAll();
          $securities = array_merge($securities, $securities2);
        }

        if ($securities) {
          // insert heading (also functions as linebreak)
          $graphs[] = array(
            'id' => $id_counter++,
            'graph_type' => 'heading',
            'string0' => get_exchange_name($exchange),
            'page_order' => 0,
            'public' => false,    // headings are actually private graphs to help with layout
            'width' => 1,
            'height' => 1,
            'days' => 0,
            'delta' => '',
            'arg0' => 0,
            'no_technicals' => true,
          );

          foreach ($securities as $sec) {
            $graphs[] = array(
              'id' => $id_counter++,
              'graph_type' => 'securities_' . $exchange,
              'arg0' => $sec['security_id'],
              'width' => 4,
              'height' => 2,
              'public' => true,
              'page_order' => 0,
              'days' => 45,
              'delta' => '',
              'no_technicals' => true,
            );
          }

        }

        // no need to make a fake graph_page - add graph form will never be shown

      }

    }

  } else {
    // get this current page's graphs
    $page_id = require_get("page", $pages[0]['id']);
    $q = db()->prepare("SELECT * FROM graph_pages
      JOIN graphs ON graph_pages.id=graphs.page_id
      WHERE graph_pages.user_id=? AND graphs.page_id=? AND graphs.is_removed=0
      ORDER BY graphs.page_order ASC, graphs.id ASC");
    $q->execute(array(user_id(), $page_id));
    $graphs = $q->fetchAll();

    if (!$graphs) {
      // make sure this is actually our page
      $is_mine = false;
      foreach ($pages as $page) {
        if ($page['id'] == $page_id) {
          $is_mine = true;
          break;
        }
      }

      if (!$is_mine) {
        $errors[] = t("Unknown page.");
        set_temporary_messages($messages);
        set_temporary_errors($errors);
        redirect(url_for('profile')); // redirect back to our home page
      }
    }

    $page_title = t("Unknown");
    foreach ($pages as $p) {
      if ($p['id'] == $page_id) {
        $page_title = $p['title'];
        $graph_page = $p;
      }
    }

  }

  // issue #58: we need to generate a hash of (user_id, selected currencies) so that the add_graph js is
  // correctly refreshed when necessary
  $hash = md5($user['id'] . ":" . $user['last_summaries_update']);

  page_header($page_title_prefix . $page_title, "page_profile", array('jsapi' => true, 'js' => array('profile', 'calculator', 'add_graph?hash=' . $hash), 'class' => 'report_page'));

?>

<div id="page<?php echo htmlspecialchars($page_id); ?>">

<!-- page list -->
<?php require(__DIR__ . "/_profile_pages.php"); ?>

<?php if ($enable_editing_dialog) { ?>
<div class="enable_editing">
  <label><input type="checkbox" id="enable_editing"<?php if ($enable_editing) echo " checked"; ?>> <?php echo ht("Enable layout editing"); ?></label>
</div>
<?php } ?>

<!-- graphs for this page -->
<div class="graph_collection">
<?php foreach ($graphs as $graph) {

if ($graph['graph_type'] == "linebreak" || $graph['graph_type'] == "heading") { ?>
  <?php if ($graph['graph_type'] == 'heading') {
    echo "<h2 class=\"graph_heading\">" . htmlspecialchars($graph['string0']) . "</h2>\n";
  } ?>
<div style="clear:both;">
<div class="graph_controls">
<?php } ?>
<?php

// load technicals from the database as necessary
// (we can only load one at this stage)
$q = db()->prepare("SELECT * FROM graph_technicals WHERE graph_id=? LIMIT 1");
$q->execute(array($graph['id']));
if ($technical = $q->fetch()) {
  $graph['technical_type'] = $technical['technical_type'];
  $graph['technical_period'] = $technical['technical_period'];
}

render_graph_new($graph, true /* TODO we should not force user data unless we actually need it */);

?>
<?php if ($graph['graph_type'] == "linebreak" || $graph['graph_type'] == "heading") { ?>
</div>
</div>
<?php } ?>
<?php }

if (!$graphs) { ?>
  <div class="graph_collection_empty">
    <?php if (require_get("securities", false)) {
      if (get_premium_value($user, 'your_securities')) {
        echo t("No securities to display! You might want to add details about :your_securities, if you have any.",
          array(
            ':your_securities' => link_to(url_for('wizard_accounts_securities'), t("your securities")),
          ));
      } else {
        echo t('To display historical value graphs of your securities, please :upgrade, or add them as
            normal "security value" graphs on one of your other :reports.',
          array(
            ':upgrade' => link_to(url_for('premium'), t("purchase a premium account")),
            ':reports' => link_to(url_for('profile'), t("report pages")),
          ));
      }
    } else {
      echo t("No graphs to display! You might want to add one below.");
    } ?>
  </div>

  <?php if (require_get("securities", false)) { ?>
  <div class="graph_collection_screenshot"><a href="<?php echo htmlspecialchars(url_for('screenshots#screenshots_profile_summary')); ?>" title="Illustration of Your Securities page"><img src="<?php echo htmlspecialchars(url_for('img/screenshots/profile_securities.png')); ?>" class="screenshot_image"></a></div>
  <?php } ?>
<?php } ?>
</div>

</div>

<div style="clear:both;"></div><?php /* try and fix tab linebreak on Android web browser */ ?>

<?php if ($enable_editing_dialog) { ?>

<div class="tabs" id="tabs_profile">
  <ul class="tab_list">
    <?php /* each <li> must not have any whitespace between them otherwise whitespace will appear when rendered */ ?>
    <li id="tab_profile_addgraph"><?php echo t("Add Graph"); ?></li><li id="tab_profile_addpage"><?php echo t("Add Page"); ?></li><?php if (!$graph_page['is_managed']) { ?><li id="tab_profile_deletepage"><?php echo t("Remove Page"); ?></li><?php } ?><li id="tab_profile_reset"><?php echo t("Reset"); ?></li><?php if (is_admin()) { ?><li id="tab_profile_addall"><?php echo t("Add All Graphs"); ?></li><?php } ?>
  </ul>

  <ul class="tab_groups">
    <li id="tab_profile_addgraph_tab">

      <div class="add_graph">
      <h2><?php echo t("Add new graph"); ?></h2>

<?php if ($graph_page['is_managed'] && $user['graph_managed_type'] == 'auto') { ?>
  <div><?php echo t("These graphs are currently :managed.", array(':managed' => link_to(url_for('wizard_reports'), t("managed automatically")))); ?></div>
<?php } else { ?>
  <?php require(__DIR__ . "/_profile_add_graph.php"); ?>
<?php } ?>

      </div>

    </li>

<?php } ?>

<?php } else {
  /* no pages */

  page_header(t("Your Reports"), "page_profile", array('jsapi' => true, 'js' => 'profile', 'class' => 'report_page'));
  ?>

<div class="message">
<ul>
  <li><?php echo ht("You have not defined any report pages - you should add a new page, or reset your graphs and pages to the site default."); ?></li>
</ul>
</div>

<div class="tabs" id="tabs_profile">
  <ul class="tab_list">
    <?php /* each <li> must not have any whitespace between them otherwise whitespace will appear when rendered */ ?>
    <li id="tab_profile_addpage"><?php echo t("Add Page"); ?></li><li id="tab_profile_reset"><?php echo t("Reset"); ?></li>
  </ul>

  <ul class="tab_groups">

<?php } ?>

<?php if ($enable_editing_dialog) { ?>

<?php require(__DIR__ . "/_profile_add_page.php"); ?>

<li id="tab_profile_reset_tab" style="display:none;">
<h2><?php echo ht("Reset User Graphs"); ?></h2>

<p>
  <?php echo ht("Using the button below, you can reset the layout of graphs and all graph pages to the site default. This action is permanent, but will not delete
  any historical summary data associated with this account."); ?>
</p>

<form action="<?php echo htmlspecialchars(url_for('reset_graphs')); ?>" method="post">
<table class="form">
<tr>
  <td>
  <label>
    <input type="checkbox" name="confirm" value="1"> <?php echo ht("Reset all of my graphs and pages."); ?>
  </label>
  </td>
</tr>
<tr>
  <td class="buttons">
  <input type="submit" value="<?php echo ht("Reset graphs and pages"); ?>">
  </td>
</tr>
</table>
</form>
</li>
<?php if (is_admin()) { ?>
<li id="tab_profile_addall_tab" style="display:none;">
<h2><?php echo ht("Add All Graphs"); ?></h2>

<p>
  <?php echo ht("Using the button below, you can reset this page and add a collection of example graphs."); ?>
</p>

<form action="<?php echo htmlspecialchars(url_for('add_all_graphs')); ?>" method="post">
  <input type="hidden" name="page" value="<?php echo htmlspecialchars($page_id); ?>">
  <input type="submit" value="<?php echo ht("Reset page with all graphs"); ?>">
</form>
</li>
<?php } /* is_admin */ ?>
</ul>

<?php } ?>

<?php

if (require_get("move_up", false) || require_get("move_down", false) || require_get("graph", false)) {
?>
<script type="text/javascript">
$(document).ready(function() {
  var e = $("#graph_<?php echo htmlspecialchars(require_get("move_up", require_get("move_down", require_get("graph", false)))); ?>");
  if (e) {
    e.css({ backgroundColor: '#221111' });
    // can't fade backgroundColor without JQuery color plugin
    window.scrollTo(e.position().left, e.position().top);
  }
});
</script>
<?php
}

page_footer();
