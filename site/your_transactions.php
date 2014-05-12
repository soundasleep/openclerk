<?php

/**
 * Issue #194: This page displays all of your transactions with tabs and the current values of each
 * type of balance.
 */

require(__DIR__ . "/../inc/global.php");
require_login();

require(__DIR__ . "/../layout/templates.php");
require(__DIR__ . "/../graphs/util.php");

$user = get_user(user_id());

$page_size = 50;

page_header("Your Transactions", "page_your_transactions", array('js' => array('accounts', 'transactions'), 'class' => 'report_page page_finance'));

function get_exchange_or_currency_name($exchange) {
	$account_data_grouped = account_data_grouped();
	if (isset($account_data_grouped['Addresses'][$exchange])) {
		return $account_data_grouped['Addresses'][$exchange]['title'];
	} else {
		return get_exchange_name($exchange);
	}
}

// get all possible exchanges and currencies
$q = db()->prepare("SELECT exchange FROM transactions WHERE user_id=? GROUP BY exchange ORDER BY exchange ASC");
$q->execute(array(user_id()));
$exchanges = $q->fetchAll();

$q = db()->prepare("SELECT * FROM finance_accounts WHERE user_id=? ORDER BY title ASC");
$q->execute(array(user_id()));
$finance_accounts = array();
while ($fa = $q->fetch()) {
	$finance_accounts[$fa['id']] = $fa;
}

// get all possible exchanges and currencies
$q = db()->prepare("SELECT currency1 AS currency FROM transactions WHERE user_id=? GROUP BY currency1 ORDER BY currency ASC");
$q->execute(array(user_id()));
$currencies = $q->fetchAll();

// get all possible transactions
$q = db()->prepare("SELECT exchange, account_id FROM transactions WHERE user_id=? GROUP BY exchange,account_id ORDER BY exchange ASC ,account_id ASC");
$q->execute(array(user_id()));
$accounts = $q->fetchAll();
// much too complicated to sort here

$page_args = array(
	'skip' => max(0, (int) require_get("skip", 0)),
	'exchange' => require_get('exchange', false),
	'currency' => require_get('currency', false),
	'account_id' => require_get('account_id', false),
	'show_automatic' => require_get('show_automatic', require_get("filter", false) ? false : true),
);

// TODO implement filtering
$extra_query = "";
$extra_args = array();

if ($page_args['exchange']) {
	$extra_query .= " AND exchange=?";
	$extra_args[] = $page_args['exchange'];
}

if ($page_args['currency']) {
	$extra_query .= " AND (currency1=? OR currency2=?)";
	$extra_args[] = $page_args['currency'];
	$extra_args[] = $page_args['currency'];
}

if ($page_args['account_id']) {
	$extra_query .= " AND account_id=?";
	$extra_args[] = $page_args['account_id'];
}

if (!$page_args['show_automatic']) {
	$extra_query .= " AND is_automatic=0";
}

$q = db()->prepare("SELECT * FROM transactions WHERE user_id=? $extra_query ORDER BY transaction_date_day DESC LIMIT " . $page_args['skip'] . ", $page_size");
$q->execute(array_merge(array(user_id()), $extra_args));
$transactions = $q->fetchAll();

?>

<!-- page list -->
<?php
$page_id = -1;
$your_transactions = true;
require(__DIR__ . "/_finance_pages.php");
?>

<div style="clear:both;"></div>

<div class="transaction-introduction">
	<div class="transaction-filter">
		<h2>Filter Transactions</h2>

		<form action="<?php echo htmlspecialchars(url_for('your_transactions')); ?>" method="get">
		<table class="standard">
		<tr>
			<th>Account Type</th>
			<td>
				<select name="exchange" id="exchange_list">
					<option value="">(all)</option>
					<?php
					foreach ($exchanges as $exchange) {
						if ($exchange['exchange'] == 'account') {
							echo "<option value=\"" . htmlspecialchars($exchange['exchange']) . "\"" .
								($page_args['exchange'] == $exchange['exchange'] ? " selected" : "") . ">" .
								"(" . t("finance accounts") . ")" .
								"</option>\n";
						} else {
							echo "<option value=\"" . htmlspecialchars($exchange['exchange']) . "\"" .
								($page_args['exchange'] == $exchange['exchange'] ? " selected" : "") . ">" .
								htmlspecialchars(get_exchange_or_currency_name($exchange['exchange'])) .
								"</option>\n";
						}
					} ?>
				</select>
			</td>
		</tr>
		<tr>
			<th>Account</th>
			<td>
				<select name="account_id" id="account_id_list">
					<option value="" class="all">(all)</option>
					<?php
					foreach ($accounts as $account) {
						$account_data = get_account_data($account['exchange'], false);
						if ($account_data) {
							$q = db()->prepare("SELECT * FROM " . $account_data['table'] . " WHERE id=?");
							$q->execute(array($account['account_id']));
							$account_full = $q->fetch();

							$title = false;
							if (!$title && $account_full && isset($account_full['title']) && $account_full['title']) {
								$title = $account_full['title'];
							}
							if (!$title && $account_full && isset($account_full['address']) && $account_full['address']) {
								$title = $account_full['address'];
							}

							echo "<option class=\"exchange-" . htmlspecialchars($account['exchange']) . "\" value=\"" . htmlspecialchars($account['account_id']) . "\"" .
								(($page_args['account_id'] == $account['account_id'] && $page_args['exchange'] == $account['exchange']) ? " selected" : "") . ">" .
								htmlspecialchars($title ? $title : "(untitled)") .
								"</option>\n";
						}
					} ?>
					<?php
					// list finance accounts
					foreach ($finance_accounts as $account) {
						$title = $account['title'];

						echo "<option class=\"exchange-account\" value=\"" . htmlspecialchars($account['id']) . "\"" .
							(($page_args['account_id'] == $account['id'] && $page_args['exchange'] == "account") ? " selected" : "") . ">" .
							htmlspecialchars($title ? $title : "(untitled)") .
							"</option>\n";
					} ?>
				</select>
			</td>
		</tr>
		<tr>
			<th>Currency</th>
			<td>
				<select name="currency" id="currency_list">
					<option value="">(all)</option>
					<?php
					foreach ($currencies as $currency) {
						echo "<option value=\"" . htmlspecialchars($currency['currency']) . "\"" . ($page_args['currency'] == $currency['currency'] ? " selected" : "") . ">" . htmlspecialchars(get_currency_abbr($currency['currency'])) . "</option>\n";
					} ?>
				</select>
			</td>
		</tr>
		<tr>
			<th></th>
			<td>
				<label>
				<input type="checkbox" name="show_automatic" value="1"<?php echo $page_args['show_automatic'] ? " checked" : ""; ?>>
				Include automatic transactions
				</label>
			</td>
		</tr>
		<tr class="buttons">
			<td colspan="2">
				<input type="submit" value="Filter">
				<input type="hidden" name="filter" value="1">
				<a href="<?php echo htmlspecialchars(url_for('your_transactions')); ?>">Show All</a>
			</td>
		</tr>
		</table>
		</form>
	</div>

	<h1>Your Transactions</h1>

	<p>
		This is a draft version of a page which will allow you to see the historical changes to your various accounts over time as daily transactions,
		generated automatically by <?php echo htmlspecialchars(get_site_config('site_name')); ?>.
		<a href="<?php echo htmlspecialchars(url_for('kb', array('q' => 'finance'))); ?>">Learn more</a>
	</p>

	<p>
		To prevent individual accounts from generating transactions, visit <a href="<?php echo htmlspecialchars(url_for('wizard_accounts')); ?>">your accounts wizard</a>
		and disable transaction generation.
	</p>

	<p>
		In the future, you will be able to edit or delete these transactions, create your own transactions, or export these transactions to CSV.
		Future functionality will also become limited to <a href="<?php echo htmlspecialchars(url_for('premium')); ?>">premium users</a>.
	</p>
</div>

<span style="display:none;" id="sort_buttons_template">
<!-- heading sort buttons -->
<span class="sort_up" title="Sort ascending">Asc</span><span class="sort_down" title="Sort descending">Desc</span>
</span>

<div class="your-transactions">
<table class="standard standard_account_list">
<thead>
	<tr>
		<th class="balance default_sort_down">Date</th>
		<th class="">Account ID</th>
		<th class="">Account</th>
		<th class="">Description</th>
		<th class="">Reference</th>
		<th class="number">Amount</th>
		<th class="buttons"></th>
	</tr>
</thead>
<tbody>
<?php
$count = 0;
$last_date = 0;
foreach ($transactions as $transaction) {
	$account_data = get_account_data($transaction['exchange'], false);
	$account_full = false;
	if ($account_data) {
		$q = db()->prepare("SELECT * FROM " . $account_data['table'] . " WHERE id=? LIMIT 1");
		$q->execute(array($transaction['account_id']));
		$account_full = $q->fetch();
	}

	$transaction_date = date("Y-m-d", strtotime($transaction['transaction_date']));

	?>
	<tr class="<?php echo $count % 2 == 0 ? "odd" : "even"; ?>">
		<td class="<?php echo $last_date == $transaction_date ? "repeated_date" : ""; ?>">
			<?php
			echo "<span title=\"" . htmlspecialchars($transaction_date) . "\">" . ($transaction_date) . "</span>";
			?>
		</td>
		<td>
			<?php echo htmlspecialchars($transaction['exchange'] . "-" . $transaction['account_id']); ?>
		</td>
		<td>
			<?php
			$url = url_for('your_transactions', array('exchange' => $transaction['exchange'], 'account_id' => $transaction['account_id']));
			if ($transaction['exchange'] == 'account') {
				$title = isset($finance_accounts[$transaction['account_id']]) ? $finance_accounts[$transaction['account_id']]['title'] : "(unknown)";
				echo "<a href=\"" . htmlspecialchars($url) . "\">" . htmlspecialchars($title) . "</a>";
			} else {
				echo $url ? "<a href=\"" . htmlspecialchars($url) . "\">" : "";
				echo get_exchange_or_currency_name($transaction['exchange']);
			}

			$title = false;
			if (!$title && $account_full && isset($account_full['title']) && $account_full['title']) {
				$title = $account_full['title'];
			}
			if (!$title && $account_full && isset($account_full['address']) && $account_full['address']) {
				$title = $account_full['address'];
			}

			if ($account && $title) {
				echo ": ";
				echo $title ? htmlspecialchars($title) : "<i>untitled</i>";
			}
			echo $url ? "</a>" : "";
		 	?>
		</td>
		<td>
			<?php if ($transaction['is_automatic']) {
				echo "(generated automatically)";
			} else {
				echo htmlspecialchars($transaction['description']);
			} ?>
		</td>
		<td>
			<?php if ($transaction['is_automatic']) {
				echo htmlspecialchars($transaction['id']);
			} else {
				echo htmlspecialchars($transaction['reference']);
			} ?>
		</td>
		<td class="number<?php echo $transaction['value1'] < 0 ? " negative" : ""; ?>">
			<span class="transaction_<?php echo htmlspecialchars($transaction['currency1']); ?>">
				<?php echo currency_format($transaction['currency1'], $transaction['value1'], 8); ?>
			</span>
		</td>
		<td>
			<form action="<?php echo htmlspecialchars(url_for('transaction_delete')); ?>" method="post">
				<input type="hidden" name="id" value="<?php echo htmlspecialchars($transaction['id']); ?>">
				<input type="submit" name="delete" value="Delete" class="delete" title="Delete this transaction" onclick="return confirm('Are you sure you want to delete this transaction?');">
			</form>
		</td>
	</tr>
<?php $last_date = $transaction_date;
} ?>
<?php if (!$transactions) { ?>
	<tr><td colspan="7"><i>No transactions found.</td></tr>
<?php } ?>
</tbody>
<tfoot>
	<tr>
		<td class="buttons" colspan="7">
			<form action="<?php echo htmlspecialchars(url_for('your_transactions')); ?>" method="get">
				<?php
				$button_args = array('skip' => max(0, $page_args['skip'] - $page_size)) + $page_args;
				foreach ($button_args as $key => $value) {
					echo "<input type=\"hidden\" name=\"" . htmlspecialchars($key) . "\" value=\"" . htmlspecialchars($value) . "\">\n";
				} ?>
				<input type="submit" class="button-previous" value="&lt; Previous"<?php echo $page_args['skip'] > 0 ? "" : " disabled"; ?>>
			</form>

			<form action="<?php echo htmlspecialchars(url_for('your_transactions')); ?>" method="get">
				<?php
				$button_args = array('skip' => max(0, $page_args['skip'] + $page_size)) + $page_args;
				foreach ($button_args as $key => $value) {
					echo "<input type=\"hidden\" name=\"" . htmlspecialchars($key) . "\" value=\"" . htmlspecialchars($value) . "\">\n";
				} ?>
				<input type="submit" class="button-next" value="Next &gt;"<?php echo count($transactions) == $page_size ? "" : " disabled"; ?>>
			</form>
		</td>
	</tr>
</tfoot>
</table>
</div>

<div class="finance-form">
<h2>Add Manual Transaction</h2>

<?php

$transaction = array(
	'date' => require_get('date', date('Y-m-d')),
	'account' => require_get('account', ""),
	'category' => require_get('category', ""),
	'description' => require_get('description', ""),
	'reference' => require_get('reference', ""),
	'value1' => require_get('value1', ""),
	'currency1' => require_get('currency1', ""),
);

$summaries = get_all_user_currencies();

?>

<form action="<?php echo htmlspecialchars(url_for('transaction_add')); ?>" method="post">
<a name="add_transaction"></a>
<table>
<tr>
	<th>Date:</th>
	<td><input type="text" name="date" size="16" value="<?php echo htmlspecialchars($transaction['date']); ?>"></td>
</tr>
<tr>
	<th>Account:</th>
	<td>
		<select name="account">
			<?php
			$q = db()->prepare("SELECT * FROM finance_accounts WHERE user_id=? ORDER BY title ASC");
			$q->execute(array(user_id()));
			$accounts = $q->fetchAll();
			$accounts[] = array('id' => 0, 'title' => '(none)');
			foreach ($accounts as $account) {
				echo "<option value=\"" . $account['id'] . "\"" . ($transaction['account'] == $account['id'] ? " selected" : "") . ">" . htmlspecialchars($account['title']) . "</option>\n";
			}
			?>
		</select>

		<a href="<?php echo htmlspecialchars(url_for('finance_accounts')); ?>">Add new account</a>
	</td>
</tr>
<tr>
	<th>Category:</th>
	<td>
		<select name="category">
			<?php
			$q = db()->prepare("SELECT * FROM finance_categories WHERE user_id=? ORDER BY title ASC");
			$q->execute(array(user_id()));
			$categories = $q->fetchAll();
			$categories[] = array('id' => 0, 'title' => '(none)');
			foreach ($categories as $category) {
				echo "<option value=\"" . $category['id'] . "\"" . ($transaction['category'] == $category['id'] ? " selected" : "") . ">" . htmlspecialchars($category['title']) . "</option>\n";
			}
			?>
		</select>

		<a href="<?php echo htmlspecialchars(url_for('finance_categories')); ?>">Add new category</a>
	</td>
</tr>
<tr>
	<th>Description:</th>
	<td><input type="text" name="description" size="64" value="<?php echo htmlspecialchars($transaction['description']); ?>"></td>
</tr>
<tr>
	<th>Reference:</th>
	<td><input type="text" name="reference" size="16" value="<?php echo htmlspecialchars($transaction['reference']); ?>"></td>
</tr>
<tr>
	<th>Amount:</th>
	<td>
		<input type="text" name="value1" size="16" value="<?php echo htmlspecialchars($transaction['value1']); ?>">

		<select name="currency1">
			<?php foreach (get_all_currencies() as $cur) {
				if (in_array($cur, $summaries)) {
					echo "<option value=\"" . $cur . "\"" . ($transaction['currency1'] == $cur ? " selected" : "") . ">" . get_currency_abbr($cur) . "</option>\n";
				}
			} ?>
		</select>
	</td>
</tr>
<tr>
	<td colspan="2" class="buttons">
		<input type="submit" value="Add transaction">
	</td>
</tr>
</table>
</form>
</div>

</div>

<?php

page_footer();
