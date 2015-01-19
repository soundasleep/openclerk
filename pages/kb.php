<?php

$q = require_get("q");
if (!is_string($q)) {
  set_temporary_errors(array(t("Invalid article key.")));
  redirect(url_for('help'));
}
if (!$q) {
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
  set_temporary_errors(array(t("No such knowledge base article ':key'.", array(':key' => htmlspecialchars($q)))));
  redirect(url_for('help'));
}
if (is_array($title)) {
  global $kb_inline;
  $kb_inline = $title['inline'];
  $title = $title['title'];
  $q = 'inline';
}

page_header(t("Knowledge Base: :title", array(":title" => $title)), "page_kb");

require_template("kb_" . $q);

page_footer();
