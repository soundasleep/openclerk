<?php

/**
 * Interface for custom accounts.
 */

require(__DIR__ . "/../inc/global.php");
require_login();

require(__DIR__ . "/../layout/templates.php");
require(__DIR__ . "/../graphs/util.php");

$user = get_user(user_id());

$messages = array();
$errors = array();

// perform post logic
if (require_post("id", false)) {
	$id = (int) require_post("id");
	$q = db()->prepare("DELETE FROM finance_accounts WHERE user_id=? AND id=?");
	$q->execute(array(user_id(), $id));

	$q = db()->prepare("UPDATE transactions SET account_id=null WHERE user_id=? AND exchange=? AND account_id=?");
	$q->execute(array(user_id(), 'account', $id));

	$messages[] = t("Deleted finance account.");
}

if (require_post("title", false)) {
	$title = (string) require_post("title");
	$description = (string) require_post("description", "");
	$gst = (string) require_post("gst", "");

	// make sure no existing title exists
	$q = db()->prepare("SELECT * FROM finance_accounts WHERE user_id=? AND title=?");
	$q->execute(array(user_id(), $title));
	if ($q->fetch()) {
		$errors[] = t("An account with the title ':title' already exists.", array(":title" => $title));
	}

	if (!can_user_add($user, "finance_accounts")) {
		$errors[] = "Cannot add finance account: too many existing finance accounts." .
				($user['is_premium'] ? "" : " To add more finance accounts, upgrade to a <a href=\"" . htmlspecialchars(url_for('premium')) . "\">premium account</a>.");
	}

	if (!$errors) {
		$q = db()->prepare("INSERT INTO finance_accounts SET title=:title, description=:description, gst=:gst, user_id=:user_id");
		$q->execute(array(
			'title' => $title,
			'description' => $description,
			'gst' => $gst,
			'user_id' => user_id(),
		));

		$messages[] = t("Added new finance account.");
	}
}

page_header(t("Your Finance Accounts"), "page_finance_accounts", array('js' => array('accounts', 'transactions'), 'class' => 'report_page page_finance'));

$q = db()->prepare("SELECT * FROM finance_accounts WHERE user_id=?");
$q->execute(array(user_id()));
$accounts = $q->fetchAll();

?>

<!-- page list -->
<?php
$page_id = -1;
$page_finance_accounts = true;
require(__DIR__ . "/_finance_pages.php");
?>

<div style="clear:both;"></div>

<div class="report-content">

<?php require_template('finance_accounts'); ?>

<?php require(__DIR__ . "/_sort_buttons.php"); ?>

<div class="your-accounts">
<table class="standard standard_account_list">
<thead>
	<tr>
		<th class="default_sort_down"><?php echo ht("Title"); ?></th>
		<th class="number"><?php echo ht("Transactions"); ?></th>
		<th class=""><?php echo ht("Description"); ?></th>
		<th class="number"><?php echo ht("GST"); ?></th>
		<th class="buttons"></th>
	</tr>
</thead>
<tbody>
<?php
$count = 0;
foreach ($accounts as $account) {
	$count++;

	?>
	<tr class="<?php echo $count % 2 == 0 ? "odd" : "even"; ?>">
		<td>
			<a href="<?php echo htmlspecialchars(url_for('your_transactions', array('exchange' => 'account', 'account_id' => $account['id']))); ?>"><?php echo htmlspecialchars($account['title']); ?></a>
		</td>
		<td class="number">
			<?php
			$q = db()->prepare("SELECT COUNT(*) AS c FROM transactions WHERE user_id=? AND exchange=? AND account_id=?");
			$q->execute(array(user_id(), 'account', $account['id']));
			$c = $q->fetch();
			echo number_format($c['c']);
			?>
		</td>
		<td><?php echo htmlspecialchars($account['description']); ?></td>
		<td><?php echo htmlspecialchars($account['gst']); ?></td>
		<td class="buttons">
			<form action="<?php echo htmlspecialchars(url_for('finance_accounts')); ?>" method="post">
				<input type="hidden" name="id" value="<?php echo htmlspecialchars($account['id']); ?>">
				<input type="submit" name="delete" value="<?php echo ht("Delete"); ?>" class="delete" title="<?php echo ht("Delete this account"); ?>" onclick="return confirm('<?php echo ht("Are you sure you want to delete this account?"); ?>');">
			</form>
		</td>
	</tr>
<?php } ?>
<?php if (!$accounts) { ?>
	<tr><td colspan="5"><i><?php echo ht("No finance accounts found."); ?></td></tr>
<?php } ?>
</tbody>
</table>
</div>

<div class="finance-form">

<h2><?php echo ht("Add Account"); ?></h2>

<?php

$account = array(
	'title' => require_get('title', ""),
	'description' => require_get('description', ""),
	'gst' => require_get('gst', ""),
);

?>

<form action="<?php echo htmlspecialchars(url_for('finance_accounts')); ?>" method="post">
<table>
<tr>
	<th><?php echo ht("Title:"); ?></th>
	<td><input type="text" name="title" size="32" value="<?php echo htmlspecialchars($account['title']); ?>"> <span class="required">*</span></td>
</tr>
<tr>
	<th><?php echo ht("Description:"); ?></th>
	<td><input type="text" name="description" size="64" value="<?php echo htmlspecialchars($account['description']); ?>"></td>
</tr>
<tr>
	<th><?php echo ht("GST number:"); ?></th>
	<td><input type="text" name="gst" size="32" value="<?php echo htmlspecialchars($account['gst']); ?>"></td>
</tr>
<tr>
	<td colspan="2" class="buttons">
		<input type="submit" value="<?php echo ht("Add account"); ?>">
	</td>
</tr>
</table>
</form>
</div>

</div>

</div>

<?php

page_footer();
