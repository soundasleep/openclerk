<?php

/**
 * Issue #194: This page displays all of your transactions with tabs and the current values of each
 * type of balance.
 */

require(__DIR__ . "/../inc/global.php");
require_login();

require(__DIR__ . "/../layout/templates.php");

$user = get_user(user_id());

$page_size = 50;

page_header("Your Transactions", "page_your_transactions", array('js' => array('accounts', 'transactions'), 'class' => 'report_page'));

// get all possible exchanges and currencies
$q = db()->prepare("SELECT exchange FROM transactions WHERE user_id=? GROUP BY exchange");
$q->execute(array(user_id()));
$exchanges = $q->fetchAll();

// get all possible exchanges and currencies
$q = db()->prepare("SELECT currency1 AS currency FROM transactions WHERE user_id=? GROUP BY currency1");
$q->execute(array(user_id()));
$currencies = $q->fetchAll();

// get all possible transactions
$q = db()->prepare("SELECT exchange, account_id FROM transactions WHERE user_id=? GROUP BY exchange,account_id");
$q->execute(array(user_id()));
$accounts = $q->fetchAll();

$page_args = array(
	'skip' => max(0, (int) require_get("skip", 0)),
	'exchange' => require_get('exchange', false),
	'currency' => require_get('currency', false),
	'account_id' => require_get('account_id', false),
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

$q = db()->prepare("SELECT * FROM transactions WHERE user_id=? $extra_query ORDER BY transaction_date_day DESC LIMIT " . $page_args['skip'] . ", $page_size");
$q->execute(array_merge(array(user_id()), $extra_args));
$transactions = $q->fetchAll();

function get_exchange_or_currency_name($exchange) {
	$account_data_grouped = account_data_grouped();
	if (isset($account_data_grouped['Addresses'][$exchange])) {
		return $account_data_grouped['Addresses'][$exchange]['title'];
	} else {
		return get_exchange_name($exchange);
	}
}

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
						echo "<option value=\"" . htmlspecialchars($exchange['exchange']) . "\"" .
							($page_args['exchange'] == $exchange['exchange'] ? " selected" : "") . ">" .
							htmlspecialchars(get_exchange_or_currency_name($exchange['exchange'])) .
							"</option>\n";
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
				</select>
			</td>
		</tr>
		<tr>
			<th>Transaction currency</th>
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
		<tr class="buttons">
			<td colspan="2">
				<input type="submit" value="Filter">
			</td>
		</tr>
		</table>
		</form>
	</div>

	<h1>Your Transactions</h1>

	<p>
		This is a draft version of a page which will allow you to see the historical changes to your various accounts over time as daily transactions,
		generated automatically by <?php echo htmlspecialchars(get_site_config('site_name')); ?>.
	</p>

	<p>
		<a href="<?php echo htmlspecialchars(url_for('kb', array('q' => 'finance'))); ?>">What is <?php echo get_site_config('site_name'); ?> Finance?</a> -
		<a href="<?php echo htmlspecialchars(url_for('kb', array('q' => 'transaction_creation'))); ?>">How are transactions automatically created?</a>
	</p>

	<p>
		To prevent individual accounts from generating transactions, visit <a href="<?php echo htmlspecialchars(url_for('wizard_accounts')); ?>">your accounts wizard</a>
		and disable transaction generation.
	</p>

	<p>
		In the future, you will be able to edit or delete these transactions, create your own transactions, or export these transactions to CSV.
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
		<th class="">Account</th>
		<th class="">Description</th>
		<th class="number">Amount</th>
	</tr>
</thead>
<tbody>
<?php
$count = 0;
foreach ($transactions as $transaction) {
	$account_data = get_account_data($transaction['exchange'], false);
	$account_full = false;
	if ($account_data) {
		$q = db()->prepare("SELECT * FROM " . $account_data['table'] . " WHERE id=? LIMIT 1");
		$q->execute(array($transaction['account_id']));
		$account_full = $q->fetch();
	}

	?>
	<tr class="<?php echo $count % 2 == 0 ? "odd" : "even"; ?>">
		<td><?php echo "<span title=\"" . htmlspecialchars(date('Y-m-d H:i:s', strtotime($transaction['transaction_date']))) . "\">" . date("Y-m-d", strtotime($transaction['transaction_date'])) . "</span>"; ?></td>
		<td>
			<?php
			$url = url_for('your_transactions', array('exchange' => $transaction['exchange'], 'account_id' => $transaction['account_id']));
			echo $url ? "<a href=\"" . htmlspecialchars($url) . "\">" : "";
			echo get_exchange_or_currency_name($transaction['exchange']);

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
			<?php if ($transaction['is_automatic']) { ?>
			(generated automatically)
			<?php } ?>
		</td>
		<td class="number<?php echo $transaction['value1'] < 0 ? " negative" : ""; ?>">
			<span class="transaction_<?php echo htmlspecialchars($transaction['currency1']); ?>">
				<?php echo currency_format($transaction['currency1'], $transaction['value1'], 8); ?>
			</span>
		</td>
	</tr>
<?php } ?>
<?php if (!$transactions) { ?>
	<tr><td colspan="4"><i>No transactions found.</td></tr>
<?php } ?>
</tbody>
<tfoot>
	<tr>
		<td class="buttons" colspan="4">
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

<?php

page_footer();
