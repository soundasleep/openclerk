<div class="tip tip_float">
<?php
// is this user a new user?
$user['is_new'] = get_site_config('new_user_premium_update_hours') && strtotime($user['created_at']) > strtotime('-' . get_site_config('new_user_premium_update_hours') . ' hour');
?>
As a <?php echo $user['is_premium'] ? "premium user" : ($user['is_new'] ? "new user" : "<a href=\"" . htmlspecialchars(url_for('premium')) . "\">free user</a>"); ?>, your
<?php echo htmlspecialchars($account_data['titles']); ?> should be updated
at least once every <?php echo plural($user['is_new'] ? get_site_config('refresh_queue_hours_premium') : get_premium_value($user, "refresh_queue_hours"), 'hour');
if ($user['is_new'] && !$user['is_premium']) echo " (for the next " . plural(
	(int) (get_site_config('new_user_premium_update_hours') - ((time() - strtotime($user['created_at']))) / (60 * 60))
	, "hour") . ")"; ?>.
</div>
