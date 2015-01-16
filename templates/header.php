<?php

use \Pages\PageRenderer;

$page_id = $id;

header('Content-type: text/html; charset=utf-8');

$html_classes = array();
if (has_required_admin()) {
  $html_classes[] = "body_admin";
}
$html_classes[] = get_site_config('site_id');
if (is_admin()) {
  $html_classes[] = "is_admin";
}

?>
<!DOCTYPE HTML>
<html<?php echo " class=\"" . implode(" ", $html_classes) . "\""; ?>>
<head>
  <title><?php echo htmlspecialchars($title); ?></title>

  <?php \Pages\PageRenderer::includeStylesheets(); ?>
  <?php \Pages\PageRenderer::includeJavascripts(); ?>

  <?php if (has_required_admin()) { ?>
  <link rel="stylesheet" type="text/css" href="<?php echo htmlspecialchars(url_for('admin.css' . '?' . get_site_config('openclerk_version'))); ?>" />
  <?php } ?>
  <?php if (isset($options["refresh"])) { ?>
  <meta http-equiv="refresh" content="<?php echo htmlspecialchars($options['refresh']); ?>">
  <?php } ?>

  <script type="text/javascript">
  <?php
  $user = user_logged_in() ? get_user(user_id()) : false;
  if ($user) {
    if ($user['disable_graph_refresh'] || (isset($graph_type['no_refresh']) && $graph_type['no_refresh'])) {
      $timeout = 0; // disable refresh
    } else {
      $timeout = get_premium_value(get_user(user_id()), 'graph_refresh');
    }
  } else {
    $timeout = get_site_config('graph_refresh_public');
  }
  // TODO move this into a more helpful location rather than in the template head
?>window.UserGraphRefresh = <?php echo $timeout * 1000 * 60; ?>;  // ms
  </script>

  <?php if (isset($options["js"]) && $options["js"]) {
    if (!is_array($options['js'])) $options['js'] = array($options['js']);
    foreach ($options['js'] as $js) {
      $js_hash = "";
      if (strpos($js, "?") !== false) {
        $js_hash = "&" . substr($js, strpos($js, "?") + 1);
        $js = substr($js, 0, strpos($js, "?"));
      }
      ?>
  <script type="text/javascript" src="<?php echo htmlspecialchars(url_for('js/' . $js . '.js' . '?' . get_site_config('openclerk_version') . $js_hash)); ?>"></script>
  <?php }
  } ?>

  <?php PageRenderer::requireTemplate("templates_head"); ?>

  <?php
  $head_compiled = __DIR__ . "/../site/head-compiled.html";
  if (file_exists($head_compiled)) {
    require($head_compiled);
  } else {
    // fix relative paths
    $input = file_get_contents(__DIR__ . "/../layout/head.html");
    $input = str_replace("src=\"", "src=\"" . htmlspecialchars(calculate_relative_path()), $input);
    echo $input;
  }
  ?>
</head>
<body<?php if ($page_id) echo ' id="' . $page_id . '"'; ?><?php if (isset($options['class'])) echo " class=\"" . htmlspecialchars($options['class']) . "\""; ?>>
<div class="body_wrapper">

<?php PageRenderer::requireTemplate("templates_header"); ?>

<?php PageRenderer::requireTemplate("navigation"); ?>

<?php PageRenderer::requireTemplate("autologin"); ?>

  <div id="page_content">

<?php
  // always display messages on every page as necessary
  PageRenderer::requireTemplate("messages");
