<?php

/**
 * Interface for custom categories.
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
	$q = db()->prepare("DELETE FROM finance_categories WHERE user_id=? AND id=?");
	$q->execute(array(user_id(), $id));

	$q = db()->prepare("UPDATE transactions SET category_id=null WHERE user_id=? AND exchange=? AND category_id=?");
	$q->execute(array(user_id(), 'account', $id));

	$messages[] = t("Deleted finance category.");
}

if (require_post("title", false)) {
	$title = (string) require_post("title");
	$description = (string) require_post("description", "");
	$gst = (string) require_post("gst", "");

	// make sure no existing title exists
	$q = db()->prepare("SELECT * FROM finance_categories WHERE user_id=? AND title=?");
	$q->execute(array(user_id(), $title));
	if ($q->fetch()) {
		$errors[] = t("A category with the title ':title' already exists.", array(":title" => $title));
	}

	if (!can_user_add($user, "finance_categories")) {
		$errors[] = t("Cannot add finance category: too many existing finance categories.") .
				($user['is_premium'] ? "" : " " . t("To add more finance categories, upgrade to a :premium_account.", array(':premium_account' => link_to(url_for('premium'), t('premium account')))));
	}

	if (!$errors) {
		$q = db()->prepare("INSERT INTO finance_categories SET title=:title, description=:description, user_id=:user_id");
		$q->execute(array(
			'title' => $title,
			'description' => $description,
			'user_id' => user_id(),
		));

		$messages[] = t("Added new finance category.");
	}
}

page_header(t("Your Finance Categories"), "page_finance_categories", array('js' => array('accounts', 'transactions'), 'class' => 'report_page page_finance'));

$q = db()->prepare("SELECT * FROM finance_categories WHERE user_id=?");
$q->execute(array(user_id()));
$categories = $q->fetchAll();

?>

<!-- page list -->
<?php
$page_id = -1;
$page_finance_categories = true;
require(__DIR__ . "/_finance_pages.php");
?>

<div style="clear:both;"></div>

<div class="report-content">

<?php require_template('finance_categories'); ?>

<?php require(__DIR__ . "/_sort_buttons.php"); ?>

<div class="your-accounts">
<table class="standard standard_account_list">
<thead>
	<tr>
		<th class="default_sort_down"><?php echo t("Title"); ?></th>
		<th class="number"><?php echo t("Transactions"); ?></th>
		<th class=""><?php echo t("Description"); ?></th>
		<th class="buttons"></th>
	</tr>
</thead>
<tbody>
<?php
$count = 0;
foreach ($categories as $category) {
	$count++;

	?>
	<tr class="<?php echo $count % 2 == 0 ? "odd" : "even"; ?>">
		<td>
			<a href="<?php echo htmlspecialchars(url_for('your_transactions', array('exchange' => 'account', 'category_id' => $category['id']))); ?>"><?php echo htmlspecialchars($category['title']); ?></a>
		</td>
		<td class="number">
			<?php
			$q = db()->prepare("SELECT COUNT(*) AS c FROM transactions WHERE user_id=? AND exchange=? AND category_id=?");
			$q->execute(array(user_id(), 'account', $category['id']));
			$c = $q->fetch();
			echo number_format($c['c']);
			?>
		</td>
		<td><?php echo htmlspecialchars($category['description']); ?></td>
		<td class="buttons">
			<form action="<?php echo htmlspecialchars(url_for('finance_categories')); ?>" method="post">
				<input type="hidden" name="id" value="<?php echo htmlspecialchars($category['id']); ?>">
				<input type="submit" name="delete" value="<?php echo ht("Delete"); ?>" class="delete" title="<?php echo ht("Delete this category"); ?>" onclick="return confirm('<?php echo ht("Are you sure you want to delete this category?"); ?>');">
			</form>
		</td>
	</tr>
<?php } ?>
<?php if (!$categories) { ?>
	<tr><td colspan="4"><i><?php echo ht("No finance categories found."); ?></td></tr>
<?php } ?>
</tbody>
</table>
</div>

<div class="finance-form">

<h2><?php echo ht("Add Category"); ?></h2>

<?php

$category = array(
	'title' => require_get('title', ""),
	'description' => require_get('description', ""),
);

?>

<form action="<?php echo htmlspecialchars(url_for('finance_categories')); ?>" method="post">
<table>
<tr>
	<th><?php echo ht("Title:"); ?></th>
	<td><input type="text" name="title" size="32" value="<?php echo htmlspecialchars($category['title']); ?>"> <span class="required">*</span></td>
</tr>
<tr>
	<th><?php echo ht("Description:"); ?></th>
	<td><input type="text" name="description" size="64" value="<?php echo htmlspecialchars($category['description']); ?>"></td>
</tr>
<tr>
	<td colspan="2" class="buttons">
		<input type="submit" value="<?php echo ht("Add category"); ?>">
	</td>
</tr>
</table>
</form>
</div>

</div>

</div>

<?php

page_footer();
