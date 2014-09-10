<?php

/**
 * Admin vote coins page.
 */

require(__DIR__ . "/../inc/global.php");
require_admin();

require(__DIR__ . "/../layout/templates.php");
require(__DIR__ . "/../layout/graphs.php");

$messages = array();
$errors = array();

// process POST
if (require_post("code", false) && require_post("title", false)) {
	$q = db()->prepare("INSERT INTO vote_coins SET code=?, title=?");
	$q->execute(array(require_post("code"), require_post("title")));
	$messages[] = "Added coin " . require_post("code") . ".";
}

if (require_post("id", false)) {
	$q = db()->prepare("SELECT * FROM vote_coins WHERE id=?");
	$q->execute(array(require_post("id")));
	$vote = $q->fetch();
	if (!$vote) {
		$errors[] = "Could not find any such vote_coins";
	} else {
		$sent = 0;

		$q = db()->prepare("SELECT * FROM vote_coins_votes JOIN users ON vote_coins_votes.user_id=users.id WHERE coin_id=?");
		$q->execute(array($vote['id']));
		while ($user = $q->fetch()) {
			if ($user['email']) {
				send_user_email($user, "voted_coin", array(
					"name" => ($user['name'] ? $user['name'] : $user['email']),
					"code" => strtolower($vote['code']),
					"abbr" => get_currency_abbr(strtolower($vote['code'])),
					"title" => get_currency_name(strtolower($vote['code'])),
					"original_title" => $vote['title'],
					"total_users" => plural("other user", $vote['total_users']),
					"url" => absolute_url(url_for("vote_coins")),
					"wizard" => absolute_url(url_for("wizard_currencies")),
				));
				$sent++;
			}
		}

		$messages[] = "Sent notifications to " . plural("user", $sent) . ".";

		// remove vote_coins and vote_coins_votes entries
		$q = db()->prepare("DELETE FROM vote_coins WHERE id=?");
		$q->execute(array($vote['id']));

		$q = db()->prepare("DELETE FROM vote_coins_votes WHERE coin_id=?");
		$q->execute(array($vote['id']));

		$messages[] = "Removed voted coin.";
	}
}

page_header("Admin Vote Coins", "page_admin_vote_coins");

?>

<h1>Administer <a href="<?php echo htmlspecialchars(url_for('vote_coins')); ?>">Coin Voting</a></h1>

<p class="backlink"><a href="<?php echo htmlspecialchars(url_for('admin')); ?>">&lt; Back to Site Status</a></p>

<form action="<?php echo htmlspecialchars(url_for('admin_vote_coins')); ?>" method="post">
<table class="form">
<tr>
	<th><?php echo ht("Currency code:"); ?></th>
	<td><input name="code" maxlength="64"></td>
</tr>
<tr>
	<th><?php echo ht("Title:"); ?></th>
	<td><input name="title" maxlength="128"></td>
</tr>
<tr>
	<td colspan="2" class="buttons">
		<input type="submit" value="<?php echo ht("Add currency"); ?>">
	</td>
</tr>
</table>
</form>

<h2>Voted Coins that have been Added</h2>

<table class="fancy standard">
<thead>
	<tr>
		<th>Code</th>
		<th>Title</th>
		<th>Votes</th>
		<th>Users</th>
		<th></th>
	</tr>
</thead>
<tbody>
	<?php
	$q = db()->prepare("SELECT * FROM vote_coins WHERE total_votes > 0 ORDER BY code ASC");
	$q->execute();
	while ($vote = $q->fetch()) {
		if (in_array(strtolower($vote['code']), get_all_currencies())) {
			?>
			<tr>
				<td><?php echo htmlspecialchars($vote['code']); ?></td>
				<td><?php echo htmlspecialchars($vote['title']); ?></td>
				<td><?php echo htmlspecialchars($vote['total_votes']); ?></td>
				<td><?php echo htmlspecialchars($vote['total_users']); ?></td>
				<td>
					<form action="<?php echo htmlspecialchars(url_for('admin_vote_coins')); ?>" method="post">
						<input type="hidden" name="id" value="<?php echo htmlspecialchars($vote['id']); ?>">
						<input type="submit" value="<?php echo ht("Notify users and remove currency"); ?>">
					</form>
				</td>
			</tr>
			<?php
		}
	} ?>
</tbody>
</table>

<?php
page_footer();
