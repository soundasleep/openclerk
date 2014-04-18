<?php

require(__DIR__ . "/../inc/global.php");

require(__DIR__ . "/../layout/templates.php");

$q = require_get("q");
if (!is_string($q)) {
	set_temporary_errors(array("Invalid article key."));
	redirect(url_for('help'));
}

// we define all knowledge base articles ourselves, so that there's no chance
// of a security breach/injection
$knowledge = get_knowledge_base();

$title = false;
foreach ($knowledge as $label => $a) {
	if (isset($a[$q])) {
		$title = $a[$q];
	}
}
if (!$title) {
	set_temporary_errors(array("No such knowledge base article '" . htmlspecialchars($q) . "'."));
	redirect(url_for('help'));
}
if (is_array($title)) {
	$kb_inline = $title['inline'];
	$title = $title['title'];
	$q = 'inline';
}

page_header("Knowledge Base: " . $title, "page_kb");

require_template("kb_" . $q);

page_footer();
