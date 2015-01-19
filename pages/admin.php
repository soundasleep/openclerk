<?php

/**
 * Admin status page.
 */

$empty_row = array(
  "dp" => 0,
  "suffix" => "",
  "class" => "",
);

$statistics = array();
$q = db()->prepare("SELECT * FROM site_statistics WHERE is_recent=1");
$q->execute();
if ($stats = $q->fetch()) {
  foreach ($stats as $key => $value) {
    if (is_numeric($key)) continue;

    $row = array(
      "key" => $key,
      "value" => $value,
    ) + $empty_row;

    if (substr($key, -strlen("disk_free_space")) == "disk_free_space") {
      $row['suffix'] = " GB";
      $row['value'] = $value / pow(1024, 3);
      if ($value < 0.5) {
        $row['class'] = "status_percent broken";
      } if ($value < 1) {
        $row['class'] = "status_percent bad";
      } else if ($value < 5) {
        $row['class'] = "status_percent poor";
      } else if ($value < 10) {
        $row['class'] = "status_percent good";
      } else {
        $row['class'] = "status_percent perfect";
      }
      $row['dp'] = 3;
    }
    if (strpos($key, "system_load") !== false) {
      $dp = 2;
      if ($value > 2) {
        $row['class'] = "status_percent bad";
      } else if ($value > 1) {
        $row['class'] = "status_percent poor";
      } else if ($value > 0.5) {
        $row['class'] = "status_percent ok";
      } else if ($value > 0.25) {
        $row['class'] = "status_percent good";
      } else {
        $row['class'] = "status_percent perfect";
      }
    }
    if ($key == "pending_subscriptions") {
      if ($value >= 90) {
        $row['class'] = "status_percent bad";
      } else if ($value >= 70) {
        $row['class'] = "status_percent poor";
      } else if ($value >= 50) {
        $row['class'] = "status_percent ok";
      }
    }

    $statistics[$key] = $row;
  }
}

if ($statistics['mysql_uptime']) {
  $statistics['mysql_qps (average)'] = array(
    'key' => 'mysql_qps (average)',
    'value' => $stats['mysql_questions'] / $stats['mysql_uptime'],
    'dp' => 2
  ) + $empty_row;
}

$value = $stats['mysql_locks_blocked'] / ($stats['mysql_locks_immediate'] + $stats['mysql_locks_blocked'] + 1 /* hack to prevent div/0 */);
if ($value > 0.1) {
  $class = "status_percent bad";
} else if ($value > 0.05) {
  $class = "status_percent poor";
} else if ($value > 0.025) {
  $class = "status_percent ok";
} else if ($value > 0.01) {
  $class = "status_percent good";
} else {
  $class = "status_percent perfect";
}

$statistics['locked out queries'] = array(
  'key' => 'locked out queries',
  'value' => 100 * $value,
  'class' => $class,
  'suffix' => '%',
  'dp' => 2,
) + $empty_row;

$value = $stats['mysql_slow_queries'] / ($stats['mysql_questions'] + 1 /* hack to prevent div/0 */);
if ($value > 0.05) {
  $class = "status_percent bad";
} else if ($value > 0.01) {
  $class = "status_percent poor";
} else if ($value > 0.005) {
  $class = "status_percent ok";
} else if ($value > 0.001) {
  $class = "status_percent good";
} else {
  $class = "status_percent perfect";
}

$statistics['slow queries'] = array(
  'key' => 'slow queries',
  'value' => 100 * $value,
  'class' => $class,
  'suffix' => '%',
  'dp' => 3,
) + $empty_row;

use \Pages\PageRenderer;

PageRenderer::header(array(
  "title" => t("Status"),
  "id" => "page_admin",
));
PageRenderer::requireTemplate("admin", array(
  'statistics_graph' => array(
    'graph_type' => 'admin_statistics',
    'width' => 5,
    'height' => 2,
    'page_order' => 0,
    'days' => 45,
    'delta' => '',
    'id' => 0,
    'public' => true,
  ),
  'queue_graph' => array(
    'graph_type' => 'statistics_queue',
    'width' => 6,
    'height' => 4,
    'page_order' => 0,
    'days' => 180,
    'delta' => '',
    'id' => 1,
    'public' => true,
  ),
  'statistics' => $statistics,
));
PageRenderer::footer();
