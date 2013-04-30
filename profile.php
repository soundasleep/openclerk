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

// get all pages
$q = db()->prepare("SELECT * FROM graph_pages WHERE user_id=? AND is_removed=0 ORDER BY page_order ASC, id ASC");
$q->execute(array(user_id()));
$pages = $q->fetchAll();

page_header("Your Profile", "page_profile", array('jsapi' => true, 'jquery' => true, 'js' => 'profile'));

// reset stats
if (get_site_config('timed_sql')) {
	echo "<!-- " . db()->stats() . " -->\n";
}

// a user might not have any pages displayed
if ($pages) {
	// get this current page's graphs
	$page_id = require_get("page", $pages[0]['id']);
	$q = db()->prepare("SELECT * FROM graph_pages
		JOIN graphs ON graph_pages.id=graphs.page_id
		WHERE graph_pages.user_id=? AND graphs.page_id=? AND graphs.is_removed=0
		ORDER BY graphs.page_order ASC, graphs.id ASC");
	$q->execute(array(user_id(), $page_id));
	$graphs = $q->fetchAll();

?>

<div id="page<?php echo htmlspecialchars($page_id); ?>">

<!-- list of pages -->
<ul class="page_list">
<?php foreach ($pages as $page) { ?>
	<li class="page_tab<?php echo htmlspecialchars($page['id']); ?>"><a href="<?php echo htmlspecialchars(url_for('profile', array('page' => $page['id']))); ?>">
		<?php echo htmlspecialchars($page['title']); ?>
	</a></li>
<?php } ?>
</ul>

<label><input type="checkbox" id="enable_editing"<?php if ($enable_editing) echo " checked"; ?>> Enable layout editing</label>

<!-- graphs for this page -->
<div class="graph_collection">
<?php foreach ($graphs as $graph) {

if ($graph['graph_type'] == "linebreak") { ?>
<div style="clear:both;">
<div class="graph_controls">
<?php } ?>
<div class="graph graph_<?php echo htmlspecialchars($graph['graph_type']); ?>" id="graph<?php echo htmlspecialchars($graph['id']); ?>">
	<?php render_graph($graph); ?>
</div>
<?php if ($graph['graph_type'] == "linebreak") { ?>
</div>
</div>
<?php } ?>
<?php } ?>
</div>

</div>

<?php require("_profile_add_graph.php"); ?>

<?php } else {
	/* no pages */ ?>

<p><i>No pages to display.</i></p>

<?php } ?>

<?php require("_profile_add_page.php"); ?>

<h3>Reset User Graphs</h3>

<p>
	Using the button below, you can reset the layout of graphs and all graph pages to the site default. This action is permanent, but will not delete
	any historical summary data associated with this account.
</p>

<form action="<?php echo htmlspecialchars(url_for('reset_graphs')); ?>" method="post">
<label>
	<input type="checkbox" name="confirm" value="1"> Reset all of my graphs and pages.
</label>
<input type="submit" value="Reset graphs and pages">
</form>

<?php
page_footer();
