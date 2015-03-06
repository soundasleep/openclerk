<?php

/**
 * External APIs status.
 */

require(__DIR__ . "/../layout/templates.php");
page_header(t("External API Status"), "page_external");

$last_updated = false;
$q = db()->prepare("SELECT * FROM external_status WHERE is_recent=1");
$q->execute();
$external = array();
$first_first = 0;
$sample_size = -1;
while ($e = $q->fetch()) {
  $external[$e['job_type']] = $e;
  if ($first_first == 0 || strtotime($e['job_first']) < strtotime($first_first)) {
    $first_first = $e['job_first'];
  }
  $sample_size = $e['sample_size'];
  $last_updated = $e['created_at'];
}

?>
<h1><?php echo ht("External API Status"); ?></h1>

<p>
<?php echo t(":site_name relies on the output of many external APIs.
  This page lists the current status of each of these APIs, as collected over the last :time (:samples samples).", array(
    ':time' => recent_format($first_first, "", ""),
    ':samples' => number_format($sample_size),
  )); ?>
</p>

<ul class="external_list">
<?php
$external_apis = get_external_apis();

function get_error_class($n) {
  if ($n <= 0.1) {
    // 0%
    return "perfect";
  } else if ($n <= 5) {
    return "good";
  } else if ($n <= 10) {
    return "ok";
  } else if ($n <= 20) {
    return "poor";
  } else if ($n <= 50) {
    return "bad";
  } else if ($n <= 75) {
    return "broken";
  } else {
    return "dead";
  }
}

foreach ($external_apis as $group_name => $group) {
  echo "<li><b>" . ht($group_name) . "</b><ul>\n";
  foreach ($group as $key => $title) {
    if (!isset($external[$key]) && !is_admin()) {
      continue;
    }
    echo "<li><span class=\"title\">" . $title['link'] . "</span> ";
    if (isset($external[$key])) {
      $percent = "<span class=\"status_percent " . get_error_class(($external[$key]['job_errors'] / $external[$key]['job_count']) * 100) . "\">" .
        number_format((1 - ($external[$key]['job_errors'] / $external[$key]['job_count'])) * 100, 0) . "%" .
        "</span>";
      echo t(":percent requests successful", array(':percent' => $percent));
    } else {
      echo "<i class=\"no_data\">" . t("no data") . "</i>";
    }
    echo " (<a href=\"" . htmlspecialchars(url_for('external_historical', array('type' => $key))) . "\">" . ht("history") . "</a>)";
    if (isset($title['package']) && $title['package']) {
      echo " ";
      echo link_to("https://github.com/" . $title['package'], "contribute", array('class' => 'github'));
    }
    echo "</li>\n";
  }
  if ($group_name == "Other") {
    $q = db()->prepare("SELECT * FROM site_statistics WHERE is_recent=1 LIMIT 1");
    $q->execute();
    $stats = $q->fetch();
    if ($stats) {
      echo "<li><span class=\"title\">" . t("Free user job delay") . "</span> ";
      echo "<span class=\"status_percent " . get_error_class((($stats['free_delay_minutes'] / 60) / (get_site_config('refresh_queue_hours') * 3)) * 100) . "\">";
      echo expected_delay_html($stats['free_delay_minutes']);
      echo "</span></li>\n";

      echo "<li><span class=\"title\">" . t(":premium_user job delay", array(':premium_user' => link_to(url_for('premium'), t("Premium user")))) . "</span> ";
      echo "<span class=\"status_percent " . get_error_class((($stats['premium_delay_minutes'] / 60) / (get_site_config('refresh_queue_hours_premium') * 3)) * 100) . "\">";
      echo expected_delay_html($stats['premium_delay_minutes']);
      echo "</span></li>\n";
    }
  }
  echo "</ul></li>\n";
}
?>
</ul>

<p>
<?php echo t("This data is refreshed automatically once per hour (last updated :ago).", array(':ago' => recent_format_html($last_updated))); ?>
</p>

<?php
page_footer();
