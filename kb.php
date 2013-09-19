<?php

require("inc/global.php");

require("layout/templates.php");

$q = require_get("q");

// we define all knowledge base articles ourselves, so that there's no chance
// of a security breach/injection
require("inc/kb.php");
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

page_header("Knowledge Base: " . $title, "page_kb");

require_template("kb_" . $q);

page_footer();
