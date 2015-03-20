<?php

// get all pages
$q = db()->prepare("SELECT * FROM graph_pages WHERE user_id=? AND is_removed=0 ORDER BY page_order ASC, id ASC");
$q->execute(array(user_id()));
$pages = $q->fetchAll();

// how many securities do we have?
// we have a new job 'securities_count' to update the count of securities for each user, otherwise we'd have to recalculate this on every profile page load
$securities_count = $user['securities_count'];

if (get_site_config('new_user_premium_update_hours') && strtotime($user['created_at']) >= strtotime("-" . get_site_config('new_user_premium_update_hours') . " hour")) {
  // does a non-zero report exist yet for this user?
  // this query shouldn't be too slow for new users, since the user_id index will be generally empty
  $q = db()->prepare("SELECT * FROM summary_instances WHERE user_id=? AND is_recent=1 AND balance > 0 LIMIT 1");
  $q->execute(array(user_id()));
  if (!($non_zero = $q->fetch())) {
    $q = db()->prepare("SELECT premium_delay_minutes FROM site_statistics WHERE is_recent=1 LIMIT 1");
    $q->execute();
    $stats = $q->fetch();
    if ($stats) {
      $messages[] = t("As a new user, it will take :minutes for your :accounts to be updated and
        your first reports to be generated.", array(
          ':minutes' => expected_delay_html($stats['premium_delay_minutes']),
          ':accounts' => link_to(url_for('wizard_accounts'), ht("accounts and addresses")),
        ));
    }
  } else {
    $messages[] = t("As a new user, your addresses and accounts will be updated more frequently
      (every :frequency) for the next :hours.", array(
        ':frequency' => plural("hour", get_site_config('refresh_queue_hours_premium')),
        ':hours' => plural("hour", (int) (get_site_config('new_user_premium_update_hours') - ((time() - strtotime($user['created_at']))) / (60 * 60))),
      ));
  }
}

