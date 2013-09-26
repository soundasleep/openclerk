<?php

/**
 * This page does all of the real hard work - taking database values and translating them
 * into graphs and such.
 */

require("inc/global.php");
require("layout/graphs.php");
require_login();

require("layout/templates.php");

$user = get_user(user_id());
require_user($user);

$messages = array();
$errors = array();

// is there a command to the page?
// TODO eventually replace this with ajax stuff
$enable_editing = false;
require("_profile_move.php");

// do we need to replace/update managed graphs?
require("graphs/managed.php");
if ($user['needs_managed_update']) {
	update_user_managed_graphs($user);
}

// get all pages
$q = db()->prepare("SELECT * FROM graph_pages WHERE user_id=? AND is_removed=0 ORDER BY page_order ASC, id ASC");
$q->execute(array(user_id()));
$pages = $q->fetchAll();

// reset stats
if (get_site_config('timed_sql')) {
	echo "<!-- " . db()->stats() . " -->\n";
}

// how many securities do we have?
$q = db()->prepare("SELECT COUNT(*) AS c FROM securities WHERE user_id=? AND is_recent=1");
$q->execute(array(user_id()));
$count = $q->fetch();
$securities_count = $count['c'];

// a user might not have any pages displayed
if ($pages) {
	if (require_get("securities", false)) {
		// displaying securities page?

		$graphs = array();
		$page_title = "Your Securities";
		$page_id = "securities";

		$id_counter = 0;		// $graph[id] needs to be set for unique graph HTML IDs

		// premium check
		if (get_premium_value($user, 'your_securities')) {

			// assumes each securities_XXX table has a 'name'
			foreach (get_security_exchange_pairs() as $exchange => $currencies) {

				$q = db()->prepare("SELECT securities.* " . (count($currencies) > 1 ? ", ss.currency" : "") . " FROM securities
					JOIN securities_" . $exchange . " AS ss ON securities.security_id=ss.id
					WHERE exchange=? AND user_id=? AND is_recent=1 ORDER BY exchange ASC, ss.name ASC");
				$q->execute(array($exchange, user_id()));
				$securities = $q->fetchAll();

				if ($securities) {
					// insert heading (also functions as linebreak)
					$graphs[] = array(
						'id' => $id_counter++,
						'graph_type' => 'heading',
						'string0' => get_exchange_name($exchange),
						'page_order' => 0,
						'public' => true,
						'width' => 1,
						'height' => 1,
						'days' => 0,
						'arg0' => 0,
						'no_technicals' => true,
					);

					// go through each security
					foreach ($currencies as $c) {
						foreach ($securities as $sec) {
							if (!isset($sec['currency'])) {
								$sec['currency'] = $c;
							}
							if ($sec['currency'] == $c) {
								$graphs[] = array(
									'id' => $id_counter++,
									'graph_type' => 'securities_' . $exchange . '_' . $sec['currency'],
									'arg0' => $sec['security_id'],
									'width' => 4,
									'height' => 2,
									'public' => true,
									'page_order' => 0,
									'days' => 45,
									'no_technicals' => true,
								);
							}
						}
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
				$errors[] = "Unknown page.";
				set_temporary_messages($messages);
				set_temporary_errors($errors);
				redirect(url_for('profile'));	// redirect back to our home page
			}
		}

		$page_title = "Unknown";
		foreach ($pages as $p) {
			if ($p['id'] == $page_id) {
				$page_title = $p['title'];
				$graph_page = $p;
			}
		}

	}

	page_header(require_get("securities", false) ? $page_title : "Your Reports: " . $page_title, "page_profile", array('common_js' => true, 'jsapi' => true, 'jquery' => true, 'js' => 'profile'));

?>

<div id="page<?php echo htmlspecialchars($page_id); ?>">

<!-- list of pages -->
<ul class="page_list">
<?php $first = true; foreach ($pages as $page) {
	$args = array('page' => $page['id']);
	if (require_get("demo", false)) {
		$args['demo'] = require_get("demo");
	} ?>
	<li class="page_tab<?php echo htmlspecialchars($page['id']); ?><?php if (!require_get("securities", false) && (!$page_id || $page['id'] == $page_id)) echo " page_current"; ?>"><a href="<?php echo htmlspecialchars(url_for('profile', $args)); ?>">
		<?php echo htmlspecialchars($page['title']); ?>
	</a></li>
<?php $first = false; } ?>
	<?php
	$args = array('securities' => 1);
	if (require_get("demo", false)) {
		$args['demo'] = require_get("demo");
	} ?>
	<li class="page_tabsecurities<?php if (require_get("securities", false)) echo " page_current"; ?> premium"><a href="<?php echo htmlspecialchars(url_for('profile', $args)); ?>">
		Your Securities (<?php echo number_format($securities_count); ?>)
	</a></li>
</ul>

<?php if (!require_get("securities", false)) { ?>
<div class="enable_editing">
	<label><input type="checkbox" id="enable_editing"<?php if ($enable_editing) echo " checked"; ?>> Enable layout editing</label>
</div>
<?php } ?>

<!-- graphs for this page -->
<div class="graph_collection">
<?php foreach ($graphs as $graph) {

if ($graph['graph_type'] == "linebreak" || $graph['graph_type'] == "heading") { ?>
	<?php if ($graph['graph_type'] == "heading") {
		echo "<h2 class=\"graph_heading\">" . htmlspecialchars($graph['string0']) . "</h2>\n";
	} ?>
<div style="clear:both;">
<div class="graph_controls">
<?php } ?>
<div class="graph graph_<?php echo htmlspecialchars($graph['graph_type']); ?>"
	id="graph<?php echo htmlspecialchars($graph['id']); ?>">
	<?php render_graph($graph); ?>
</div>
<?php if ($graph['graph_type'] == "linebreak" || $graph['graph_type'] == "heading") { ?>
</div>
</div>
<?php } ?>
<?php }

if (!$graphs) { ?>
	<div class="graph_collection_empty">
		<?php if (require_get("securities", false)) {
			if (get_premium_value($user, 'your_securities')) {
				echo "No securities to display! You might want to add details about <a href=\"" . htmlspecialchars(url_for('accounts')) . "\">your securities exchanges</a>, if you have any.";
			} else {
				echo "To display historical value graphs of your securities, please <a href=\"" . htmlspecialchars(url_for('premium')) . "\">purchase a premium account</a>, or
					add them as normal \"security value\" graphs on one of your other <a href=\"" . htmlspecialchars(url_for('profile')) . "\">report pages</a>.";
			}
		} else {
			echo "No graphs to display! You might want to add one below.";
		} ?>
	</div>

	<?php if (require_get("securities", false)) { ?>
	<div class="graph_collection_screenshot"><a href="<?php echo htmlspecialchars(url_for('screenshots#screenshots_profile_summary')); ?>" title="Illustration of Your Securities page"><img src="img/screenshots/profile_securities.png" class="screenshot_image"></a></div>
	<?php } ?>
<?php } ?>
</div>

</div>

<div style="clear:both;"></div><?php /* try and fix tab linebreak on Android web browser */ ?>

<?php if (!require_get("securities", false)) { ?>

<div class="tabs" id="tabs_profile">
	<ul class="tab_list">
		<?php /* each <li> must not have any whitespace between them otherwise whitespace will appear when rendered */ ?>
		<li id="tab_profile_addgraph">Add Graph</li><li id="tab_profile_addpage">Add Page</li><?php if (!$graph_page['is_managed']) { ?><li id="tab_profile_deletepage">Remove Page</li><?php } ?><li id="tab_profile_reset">Reset</li>
	</ul>

	<ul class="tab_groups">
		<li id="tab_profile_addgraph_tab">

			<div class="add_graph">
			<h2>Add new graph</h2>

<?php if ($graph_page['is_managed'] && $user['graph_managed_type'] == 'auto') { ?>
	<div>These graphs are currently <a href="<?php echo htmlspecialchars(url_for('wizard_reports')); ?>">managed automatically</a>.</div>
<?php } else { ?>
	<?php require("_profile_add_graph.php"); ?>
<?php } ?>

			</div>

		</li>

<?php } ?>

<?php } else {
	/* no pages */

	page_header("Your Reports", "page_profile", array('common_js' => true, 'jsapi' => true, 'jquery' => true, 'js' => 'profile'));
	?>

<div class="message">
<ul>
	<li>You have not defined any report pages - you should add a new page, or reset your graphs and pages to the site default.</li>
</ul>
</div>

<div class="tabs" id="tabs_profile">
	<ul class="tab_list">
		<?php /* each <li> must not have any whitespace between them otherwise whitespace will appear when rendered */ ?>
		<li id="tab_profile_addpage">Add Page</li><li id="tab_profile_reset">Reset</li>
	</ul>

	<ul class="tab_groups">

<?php } ?>

<?php if (!require_get("securities", false)) { ?>

<?php require("_profile_add_page.php"); ?>

<li id="tab_profile_reset_tab">
<h2>Reset User Graphs</h2>

<p>
	Using the button below, you can reset the layout of graphs and all graph pages to the site default. This action is permanent, but will not delete
	any historical summary data associated with this account.
</p>

<form action="<?php echo htmlspecialchars(url_for('reset_graphs')); ?>" method="post">
<table class="form">
<tr>
	<td>
	<label>
		<input type="checkbox" name="confirm" value="1"> Reset all of my graphs and pages.
	</label>
	</td>
</tr>
<tr>
	<td class="buttons">
	<input type="submit" value="Reset graphs and pages">
	</td>
</tr>
</table>
</form>
</li>
</ul>

<?php } ?>

<?php

if (require_get("move_up", false) || require_get("move_down", false) || require_get("graph", false)) {
?>
<script type="text/javascript">
$(document).ready(function() {
	var e = $("#graph<?php echo htmlspecialchars(require_get("move_up", require_get("move_down", require_get("graph", false)))); ?>");
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
