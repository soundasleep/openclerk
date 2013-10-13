<?php

require(__DIR__ . "/inc/global.php");
require(__DIR__ . "/layout/graphs.php");
require_login();

// adding a new graph type?
$graph_type = require_post("type");
$width = require_post("width");
$height = require_post("height");
$page_id = require_post("page");
$graph_id = require_post("id", false);	// if set, then we're editing an existing graph
$is_edit = $graph_id || false;
$days = require_post("days", false);
$arg0 = (int) require_post("arg0", false);
$string0 = substr(require_post("string0", false), 0, 128);	// trim to 128 chars

// make sure this is actually our page
$q = db()->prepare("SELECT * FROM graph_pages WHERE user_id=? AND id=?");
$q->execute(array(user_id(), $page_id));
if (!$q->fetch()) {
	throw new Exception("Cannot find page " . htmlspecialchars($page_id));
}

$errors = array();
$messages = array();

$user = get_user(user_id());
require_user($user);

// check premium account limits (unless we're editing a graph)
if (!$graph_id) {
	$q = db()->prepare("SELECT COUNT(*) AS c FROM graphs WHERE page_id=? AND is_removed=0 AND graph_type <> 'linebreak'");
	$q->execute(array($page_id));
	$count = $q->fetch();
	$count = $count['c'];

	if ($count >= get_premium_value($user, 'graphs_per_page')) {
		$errors[] = "Cannot add graph: too many existing graphs on this page." .
				($user['is_premium'] ? "" : " To add more graphs on this page, upgrade to a <a href=\"" . htmlspecialchars(url_for('premium')) . "\">premium account</a>.");
		set_temporary_errors($errors);
		redirect(url_for('profile', array('page' => $page_id)));
	}
}

// only permit valid values
$graph_types = graph_types();
$permitted_days = array();
foreach (get_permitted_days() as $key => $data) {
	$permitted_days[] = $data['days'];
}
if (!isset($graph_types[$graph_type])) {
	throw new Exception("Invalid graph type '" . htmlspecialchars($graph_type) . "'");
} else if (!is_numeric($width) || $width < 1 || $width > 16) {
	throw new Exception("Invalid width '" . htmlspecialchars($width) . "'");
} else if (!is_numeric($height) || $height < 1 || $height > 16) {
	throw new Exception("Invalid height '" . htmlspecialchars($height) . "'");
} else if ($days && !in_array($days, $permitted_days)) {
	throw new Exception("Invalid days '" . htmlspecialchars($day) . "'");
} else {
	// it's OK - let's add a new one
	// first get the highest page order graph so far on this page
	$q = db()->prepare("SELECT * FROM graphs WHERE page_id=? ORDER BY page_order DESC LIMIT 1");	// including is_removed (in case of restore)
	$q->execute(array($page_id));
	$highest = $q->fetch();
	$new_order = $highest ? ($highest['page_order'] + 1) : 1;

	// if this graph doesn't define days, don't insert in invalid days data
	if (!(isset($graph_types[$graph_type]['days']) && $graph_types[$graph_type]['days'])) {
		$days = 0;
	}

	// now insert or update it
	if ($graph_id) {
		// make sure we actually have a graph to edit
		$q = db()->prepare("SELECT * FROM graphs WHERE page_id=? AND id=? LIMIT 1");
		$q->execute(array($page_id, $graph_id));
		if (!$q->fetch()) {
			throw new Exception("Could not find graph '" . htmlspecialchars($graph_id) . "' for the current user");
		}

		// we own this graph; edit it
		$q = db()->prepare("UPDATE graphs SET page_id=:page_id, graph_type=:graph_type, width=:width, height=:height, days=:days, arg0=:arg0, string0=:string0 WHERE id=:id");
		$q->execute(array(
			'page_id' => $page_id,
			// we don't change page_order
			'graph_type' => $graph_type,
			'width' => $width,
			'height' => $height,
			'days' => $days,
			'arg0' => $arg0,
			'string0' => $string0,
			'id' => $graph_id,
		));
	} else {
		$q = db()->prepare("INSERT INTO graphs SET page_id=:page_id, page_order=:page_order, graph_type=:graph_type, width=:width, height=:height, days=:days, arg0=:arg0, string0=:string0");
		$q->execute(array(
			'page_id' => $page_id,
			'page_order' => $new_order,
			'graph_type' => $graph_type,
			'width' => $width,
			'height' => $height,
			'days' => $days,
			'arg0' => $arg0,
			'string0' => $string0,
		));
		$graph_id = db()->lastInsertId();
	}

	// technical graphs?
	$technical = require_post("technical", false);
	$message_extra = "";
	if ($technical) {
		// make sure that we don't add technicals that are premium only
		$graph_technical_types = graph_technical_types();
		if (!isset($graph_technical_types[$technical])) {
			$errors[] = "Could not add technical type '" . htmlspecialchars($technical) . "' - no such technical type.";
		} else if ($graph_technical_types[$technical]['premium'] && !$user['is_premium']) {
			$errors[] = "Could not add technical type '" . htmlspecialchars($graph_technical_types[$technical]['title']) .
				"' - requires a <a href=\"" . htmlspecialchars(url_for('premium')) . "\">premium account</a>.";
		} else {
			// it's OK

			// delete any existing technicals (even if we're inserting, since this logic is used for edit too)
			// (we limit a graph to only have a single technical at the moment)
			$q = db()->prepare("DELETE FROM graph_technicals WHERE graph_id=?");
			$q->execute(array($graph_id));

			// insert a new technical
			$q = db()->prepare("INSERT INTO graph_technicals SET graph_id=:graph_id, technical_type=:type, technical_period=:period");
			$q->execute(array(
				'graph_id' => $graph_id,
				'type' => $technical,
				'period' => min(get_site_config('technical_period_max'), max(1, (int) require_post("period", 0))),
			));
			$message_extra = ", with " . htmlspecialchars($graph_technical_types[$technical]['title']);
		}
	} else {
		// otherwise, delete old technicals
		$q = db()->prepare("DELETE FROM graph_technicals WHERE graph_id=?");
		$q->execute(array($graph_id));

	}

	// redirect
	$messages[] = ($is_edit ? "Edited " : "Added new ") . $graph_types[$graph_type]['heading'] . " graph" . $message_extra . ".";
	set_temporary_messages($messages);
	set_temporary_errors($errors);
	redirect(url_for('profile', array('page' => $page_id, 'graph' => $graph_id)));
}
