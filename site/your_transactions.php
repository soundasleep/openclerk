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

$q = db()->prepare("SELECT * FROM finance_categories WHERE user_id=? ORDER BY title ASC");
$q->execute(array(user_id()));
$finance_categories = array();
while ($fc = $q->fetch()) {
	$finance_categories[$fc['id']] = $fc;
}

// get all possible exchanges and currencies
$q = db()->prepare("SELECT currency1 AS currency FROM transactions WHERE user_id=? GROUP BY currency1 ORDER BY currency ASC");
$q->execute(array(user_id()));
$currencies = array();
while ($cur = $q->fetch()) {
	$currencies[$cur['currency']] = $cur;
}
$q = db()->prepare("SELECT currency2 AS currency FROM transactions WHERE user_id=? AND NOT(ISNULL(currency2)) GROUP BY currency2 ORDER BY currency ASC");
$q->execute(array(user_id()));
while ($cur = $q->fetch()) {
	$currencies[$cur['currency']] = $cur;
}

// get all possible transactions
$q = db()->prepare("SELECT exchange, account_id FROM transactions WHERE user_id=? GROUP BY exchange,account_id ORDER BY exchange ASC ,account_id ASC");
$q->execute(array(user_id()));
$accounts = $q->fetchAll();
// much too complicated to sort here

$page_args = array(
	'skip' => max(0, (int) require_get("skip", 0)),
	'exchange' => require_get('exchange', false),
	'currency' => require_get('currency', false),
	'account_id' => require_get('account_id', ""),
	'category_id' => require_get('category_id', ""),
	'show_automatic' => require_get('show_automatic', require_get("filter", false) ? false : true),
	'include_rates' => require_get("include_rates", false),
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
	$extra_args[] = (string) $page_args['currency'];
	$extra_args[] = (string) $page_args['currency'];
}

if ($page_args['account_id'] !== "") {
	$extra_query .= " AND account_id=?";
	$extra_args[] = (int) $page_args['account_id'];
}

if ($page_args['category_id'] !== "") {
	$extra_query .= " AND category_id=?";
	$extra_args[] = (int) $page_args['category_id'];
}

if (!$page_args['show_automatic']) {
	$extra_query .= " AND is_automatic=0";
}

if (require_get("csv", false) && $user['is_premium']) {
	// select all transactions if CSV exporting
	$page_args['skip'] = 0;
	$page_size = 1e6;
}

$q = db()->prepare("SELECT * FROM transactions WHERE user_id=? $extra_query ORDER BY transaction_date_day DESC, currency1 ASC, currency2 ASC LIMIT " . $page_args['skip'] . ", $page_size");
$q->execute(array_merge(array(user_id()), $extra_args));
$transactions = $q->fetchAll();

$rates = array();

foreach ($transactions as $id => $transaction) {
	$account_data = get_account_data($transaction['exchange'], false);
	$account_full = false;
	if ($account_data) {
		$q = db()->prepare("SELECT * FROM " . $account_data['table'] . " WHERE id=? LIMIT 1");
		$q->execute(array($transaction['account_id']));
		$account_full = $q->fetch();
	}

	if ($transaction['exchange'] == 'account') {
		$account = "(finance account)";
		$account_title = isset($finance_accounts[$transaction['account_id']]) ? $finance_accounts[$transaction['account_id']]['title'] : "(none)";
	} else {
		$account = get_exchange_or_currency_name($transaction['exchange']);
		$account_title = false;
		if (!$account_title && $account_full && isset($account_full['title']) && $account_full['title']) {
			$account_title = $account_full['title'];
		}
		if (!$account_title && $account_full && isset($account_full['address']) && $account_full['address']) {
			$account_title = $account_full['address'];
		}
	}

	$transactions[$id]['exchange_name'] = $account;
	$transactions[$id]['account_title'] = $account_title;

	// get rates for that day
	if ($page_args['include_rates']) {
		foreach (array(1, 2) as $index) {
			if ($transaction['currency' . $index] && $transaction['value' . $index]) {
				if ($transaction['currency' . $index] == 'btc') {
					$currency1 = 'usd';
					$currency2 = 'btc';
				} else if (is_fiat_currency($transaction['currency' . $index])) {
					$currency1 = $transaction['currency' . $index];
					$currency2 = 'btc';
				} else {
					$currency1 = 'btc';
					$currency2 = $transaction['currency' . $index];
				}
				$exchange = 'average';

				$key = $exchange . "_" . $currency1 . "_" . $currency2 . "_" . $transaction['transaction_date_day'];
				if (!isset($rates[$key])) {

					$args = array(
						'transaction_date_day' => $transaction['transaction_date_day'],
						'currency1' => $currency1,
						'currency2' => $currency2,
						'exchange' => $exchange,
					);

					// try recent data
					$q = db()->prepare("SELECT * FROM ticker WHERE created_at_day=:transaction_date_day AND currency1=:currency1 AND currency2=:currency2 AND exchange=:exchange AND is_daily_data=1 LIMIT 1");
					$q->execute($args);
					$rate = $q->fetch();

					if (!$rate) {
						// try historical data
						$q = db()->prepare("SELECT * FROM graph_data_ticker WHERE data_date_day=:transaction_date_day AND currency1=:currency1 AND currency2=:currency2 AND exchange=:exchange LIMIT 1");
						$q->execute($args);
						$rate = $q->fetch();
					}

					if ($rate) {
						$rates[$key] = $rate;
					}
				}

				if (isset($rates[$key])) {
					$transactions[$id]['rates' . $index . '_exchange'] = $exchange;
					$transactions[$id]['rates' . $index . '_currency1'] = $currency1;
					$transactions[$id]['rates' . $index . '_currency2'] = $currency2;
					$transactions[$id]['rates' . $index . '_bid'] = $rates[$key]['bid'];
					$transactions[$id]['rates' . $index . '_ask'] = $rates[$key]['ask'];
				}
			}
		}
	}
}

// export to CSV?
if (require_get("csv", false)) {
	if (!$user['is_premium']) {
		$errors[] = t("Only :premium_users can export transactions to CSV.", array(
				':premium_users' => "<a href=\"" . htmlspecialchars(url_for('premium')) . "\">" . t("premium users") . "</a>",
			));
	} else {
		check_heavy_request();

		require(__DIR__ . "/../inc/content_type/csv.php");		// to allow for appropriate headers etc

		header("Content-Disposition: attachment;filename=transactions.csv");

		// output transactions
		$header = array(
			"Transaction ID",
			"Date",
			"Account ID",
			"Account",
			"Account title",
			"Category",
			"Description",
			"Reference",
			"Amount 1",
			"Currency 1",
			"Amount 2",
			"Currency 2",
		);
		if ($page_args['include_rates']) {
			$header[] = "Amount 1 Daily Rate Exchange";
			$header[] = "Amount 1 Daily Rate Currency 1";
			$header[] = "Amount 1 Daily Rate Currency 2";
			$header[] = "Amount 1 Daily Rate Bid";
			$header[] = "Amount 2 Daily Rate Exchange";
			$header[] = "Amount 2 Daily Rate Currency 1";
			$header[] = "Amount 2 Daily Rate Currency 2";
			$header[] = "Amount 2 Daily Rate Bid";
		}
		echo csv_encode($header);

		foreach ($transactions as $transaction) {
			$row = array(
				$transaction['id'],
				date("Y-m-d", strtotime($transaction['transaction_date'])),
				$transaction['exchange'] . "-" . $transaction['account_id'],
				$transaction['exchange_name'],
				$transaction['account_title'],
				isset($finance_categories[$transaction['category_id']]) ? $finance_categories[$transaction['category_id']]['title'] : "",
				$transaction['is_automatic'] ? "(generated automatically)" : $transaction['description'],
				$transaction['is_automatic'] ? $transaction['id'] : $transaction['reference'],
				$transaction['value1'],
				$transaction['currency1'],
				$transaction['value2'],
				$transaction['currency2'],
			);
			if ($page_args['include_rates']) {
				$row[] = isset($transaction['rates1_exchange']) ? $transaction['rates1_exchange'] : "";
				$row[] = isset($transaction['rates1_currency1']) ? $transaction['rates1_currency1'] : "";
				$row[] = isset($transaction['rates1_currency2']) ? $transaction['rates1_currency2'] : "";
				$row[] = isset($transaction['rates1_bid']) ? $transaction['rates1_bid'] : "";
				$row[] = isset($transaction['rates2_exchange']) ? $transaction['rates2_exchange'] : "";
				$row[] = isset($transaction['rates2_currency1']) ? $transaction['rates2_currency1'] : "";
				$row[] = isset($transaction['rates2_currency2']) ? $transaction['rates2_currency2'] : "";
				$row[] = isset($transaction['rates2_bid']) ? $transaction['rates2_bid'] : "";
			}
			echo csv_encode($row);
		}

		performance_metrics_page_end();
		return;
	}
}

page_header("Your Transactions", "page_your_transactions", array('js' => array('accounts', 'transactions'), 'class' => 'report_page page_finance'));

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
			<th>Categories</th>
			<td>
				<select name="category_id" id="category_id_list">
					<option value="" class="all">(all)</option>
					<?php
					foreach ($finance_categories as $category) {
						$title = $category['title'];

						echo "<option value=\"" . htmlspecialchars($category['id']) . "\"" .
							(($page_args['category_id'] == $category['id']) ? " selected" : "") . ">" .
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
		<tr>
			<th></th>
			<td>
				<label>
				<input type="checkbox" name="include_rates" value="1"<?php echo $page_args['include_rates'] ? " checked" : ""; ?>>
				Include daily exchange rates
				</label>
			</td>
		</tr>
		<tr class="buttons">
			<td colspan="2">
				<input type="submit" value="Filter">
				<input type="submit" name="csv" value="Export to CSV" class="premium">
				<input type="hidden" name="filter" value="1">
				<a style="float:right;" href="<?php echo htmlspecialchars(url_for('your_transactions')); ?>">Clear Filters</a>
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
		In the future, you will be able to export these transactions to CSV.
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
		<th class="">Category</th>
		<th class="">Description</th>
		<th class="">Reference</th>
		<th class="number">Amount</th>
		<?php if ($page_args['include_rates']) { ?>
			<th class="rates">Daily Rates</th>
		<?php } ?>
		<th class="buttons"></th>
	</tr>
</thead>
<tbody>
<?php
$totals = array();
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
	<tr class="<?php echo $count % 2 == 0 ? "odd" : "even"; echo $transaction['id'] == require_get('highlight', 0) ? " selected" : ""; ?>">
		<td class="<?php echo $last_date == $transaction_date ? "repeated_date" : ""; ?>">
			<?php
			echo "<span title=\"" . htmlspecialchars($transaction_date) . "\">" . ($transaction_date) . "</span>";
			echo "<a name=\"transaction_" . htmlspecialchars($transaction['id']) . "\"></a>";
			?>
		</td>
		<td>
			<?php echo htmlspecialchars($transaction['exchange'] . "-" . $transaction['account_id']); ?>
		</td>
		<td>
			<?php
			$url = url_for('your_transactions', array('exchange' => $transaction['exchange'], 'account_id' => $transaction['account_id']));
			echo "<a href=\"" . htmlspecialchars($url) . "\">";

			if ($transaction['exchange'] == 'account') {
				echo $transaction['account_title'];
			} else {
				echo $transaction['exchange_name'];
			}

			if ($account && $transaction['exchange'] != 'account' && $transaction['account_title']) {
				echo ": " . htmlspecialchars($transaction['account_title']);
			}
			echo "</a>";
		 	?>
		</td>
		<td>
			<?php
			$url = url_for('your_transactions', array('exchange' => $transaction['exchange'], 'category_id' => $transaction['category_id']));
			if (isset($finance_categories[$transaction['category_id']])) {
				echo "<a href=\"" . htmlspecialchars($url) . "\">" . htmlspecialchars($finance_categories[$transaction['category_id']]['title']) . "</a>";
			}
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
		<td class="number">
			<span class="transaction_<?php echo htmlspecialchars($transaction['currency1']) . ($transaction['value1'] < 0 ? " negative" : ""); ?>">
				<?php echo currency_format($transaction['currency1'], $transaction['value1'], 8); ?>
			</span>
			<?php if ($transaction['value2']) { ?>
				<br><span class="transaction_<?php echo htmlspecialchars($transaction['currency2']) . ($transaction['value2'] < 0 ? " negative" : ""); ?>">
					<?php echo currency_format($transaction['currency2'], $transaction['value2'], 8); ?>
				</span>
			<?php } ?>
			<?php
			if ($transaction['currency1']) {
				if (!isset($totals[$transaction['currency1']])) {
					$totals[$transaction['currency1']] = 0;
				}
				$totals[$transaction['currency1']] += $transaction['value1'];
			}
			if ($transaction['currency2']) {
				if (!isset($totals[$transaction['currency2']])) {
					$totals[$transaction['currency2']] = 0;
				}
				$totals[$transaction['currency2']] += $transaction['value2'];
			}
			?>
		</td>
		<?php if ($page_args['include_rates']) { ?>
		<td class="number rate">
			<?php if (isset($transaction['rates1_bid'])) { ?>
				<a href="<?php echo htmlspecialchars(url_for('average', array('currency1' => $transaction['rates1_currency1'], 'currency2' => $transaction['rates1_currency2']))); ?>">
				<span class="transaction_rate">
					<?php echo rate_format($transaction['rates1_currency1'], $transaction['rates1_currency2'], $transaction['rates1_bid'], 8); ?>
				</span>
				</a>
			<?php } ?>
			<?php if (isset($transaction['rates2_bid'])) { ?>
				<br>
				<a href="<?php echo htmlspecialchars(url_for('average', array('currency1' => $transaction['rates2_currency1'], 'currency2' => $transaction['rates2_currency2']))); ?>">
				<span class="transaction_rate">
					<?php echo rate_format($transaction['rates2_currency1'], $transaction['rates2_currency2'], $transaction['rates2_bid'], 8); ?>
				</span>
			</a>
			<?php } ?>
		</td>
		<?php } ?>
		<td class="buttons">
			<form action="<?php echo htmlspecialchars(url_for('your_transactions#add_transaction')); ?>" method="get">
				<input type="hidden" name="description" value="<?php echo htmlspecialchars($transaction['description']); ?>">
				<input type="hidden" name="reference" value="<?php echo htmlspecialchars($transaction['reference']); ?>">
				<input type="hidden" name="account" value="<?php echo htmlspecialchars($transaction['account_id']); ?>">
				<input type="hidden" name="category" value="<?php echo htmlspecialchars($transaction['category_id']); ?>">
				<input type="hidden" name="value1" value="<?php echo htmlspecialchars($transaction['value1']); ?>">
				<input type="hidden" name="currency1" value="<?php echo htmlspecialchars($transaction['currency1']); ?>">
				<input type="hidden" name="value2" value="<?php echo htmlspecialchars($transaction['value2']); ?>">
				<input type="hidden" name="currency2" value="<?php echo htmlspecialchars($transaction['currency2']); ?>">
				<input type="submit" name="copy" value="Copy" class="copy" title="Copy this transaction">
				<?php foreach ($page_args as $key => $value) { ?>
					<input type="hidden" name="<?php echo htmlspecialchars($key); ?>" value="<?php echo htmlspecialchars($value); ?>">
				<?php } ?>
			</form>

			<form action="<?php echo htmlspecialchars(url_for('transaction_delete')); ?>" method="post">
				<input type="hidden" name="id" value="<?php echo htmlspecialchars($transaction['id']); ?>">
				<?php foreach ($page_args as $key => $value) { ?>
					<input type="hidden" name="page_args[<?php echo htmlspecialchars($key); ?>]" value="<?php echo htmlspecialchars($value); ?>">
				<?php } ?>
				<input type="submit" name="delete" value="Delete" class="delete" title="Delete this transaction" onclick="return confirm('Are you sure you want to delete this transaction?');">
			</form>
		</td>
	</tr>
<?php $last_date = $transaction_date;
} ?>
<?php if (!$transactions) { ?>
	<tr><td colspan="<?php echo $page_args['include_rates'] ? 9 : 8; ?>"><i>No transactions found.</td></tr>
<?php } ?>
</tbody>
<tfoot>
	<tr>
		<td class="buttons">
			<form action="<?php echo htmlspecialchars(url_for('your_transactions')); ?>" method="get">
				<?php
				$button_args = array('skip' => max(0, $page_args['skip'] - $page_size)) + $page_args;
				foreach ($button_args as $key => $value) {
					echo "<input type=\"hidden\" name=\"" . htmlspecialchars($key) . "\" value=\"" . htmlspecialchars($value) . "\">\n";
				} ?>
				<input type="submit" class="button-previous" value="&lt; Previous"<?php echo $page_args['skip'] > 0 ? "" : " disabled"; ?>>
			</form>
		</td>
		<td colspan="5">
			<b>Subtotal</b>
		</td>
		<td class="number">
			<?php
			ksort($totals);
			$totals_count = 0;
			foreach ($totals as $currency => $value) {
				$totals_count++;
				if ($totals_count == 3) {
					echo "<a class=\"collapse-link collapsed\">+</a><span class=\"collapse-target\" style=\"display:none;\">";
				}
				if ($totals_count > 1) {
					echo "<br>";
				}
				?>
				<span class="transaction_<?php echo htmlspecialchars($currency) . ($value < 0 ? " negative" : ""); ?>">
					<?php echo currency_format($currency, $value, 8); ?>
				</span>
			<?php }
			if ($totals_count > 3) echo "</span>"; ?>
		</td>
		<?php if ($page_args['include_rates']) { ?>
		<td></td>
		<?php } ?>
		<td class="buttons">
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
<h2>Add Transaction</h2>

<p>
	Here you may add in a transaction for a different <a href="<?php echo htmlspecialchars(url_for('finance_accounts')); ?>">account</a>
	or <a href="<?php echo htmlspecialchars(url_for('finance_category')); ?>">category</a> that cannot be
	<a href="<?php echo htmlspecialchars(url_for('kb', array('q' => 'transaction_creation'))); ?>">generated automatically</a>.
</p>

<?php

$transaction = array(
	'date' => require_get('date', date('Y-m-d')),
	'account' => require_get('account', ""),
	'category' => require_get('category', ""),
	'description' => require_get('description', ""),
	'reference' => require_get('reference', ""),
	'value1' => require_get('value1', ""),
	'currency1' => require_get('currency1', ""),
	'value2' => require_get('value2', ""),
	'currency2' => require_get('currency2', ""),
);

$summaries = get_all_user_currencies();

?>

<form action="<?php echo htmlspecialchars(url_for('transaction_add')); ?>" method="post">
<a name="add_transaction"></a>
<table class="add-transaction">
<tr>
	<th>Date:</th>
	<td><input type="text" name="date" size="16" value="<?php echo htmlspecialchars($transaction['date']); ?>"> <span class="required">*</span></td>
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

		<a class="add-new" href="<?php echo htmlspecialchars(url_for('finance_accounts')); ?>">Add new account</a>
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

		<a class="add-new" href="<?php echo htmlspecialchars(url_for('finance_categories')); ?>">Add new category</a>
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
	<th>Amount 1:</th>
	<td>
		<input type="text" name="value1" size="16" value="<?php echo htmlspecialchars($transaction['value1']); ?>">

		<select name="currency1">
			<?php foreach (get_all_currencies() as $cur) {
				if (in_array($cur, $summaries)) {
					echo "<option value=\"" . $cur . "\"" . ($transaction['currency1'] == $cur ? " selected" : "") . ">" . get_currency_abbr($cur) . "</option>\n";
				}
			} ?>
		</select>

		<span class="required">*</span>
	</td>
</tr>
<tr>
	<th>Amount 2:</th>
	<td>
		<input type="text" name="value2" size="16" value="<?php echo htmlspecialchars($transaction['value2']); ?>">

		<select name="currency2">
			<?php foreach (get_all_currencies() as $cur) {
				if (in_array($cur, $summaries)) {
					echo "<option value=\"" . $cur . "\"" . ($transaction['currency2'] == $cur ? " selected" : "") . ">" . get_currency_abbr($cur) . "</option>\n";
				}
			} ?>
		</select>
	</td>
</tr>
<tr>
	<td colspan="2" class="buttons">
		<?php foreach ($page_args as $key => $value) { ?>
			<input type="hidden" name="page_args[<?php echo htmlspecialchars($key); ?>]" value="<?php echo htmlspecialchars($value); ?>">
		<?php } ?>
		<input type="submit" value="Add transaction">
	</td>
</tr>
</table>
</form>
</div>

</div>

<?php

page_footer();
