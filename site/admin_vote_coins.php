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

page_header("Admin Vote Coins", "page_admin_vote_coins");

?>

<h1>Administer Coin Voting</h1>

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

<?php
page_footer();
