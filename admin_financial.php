<?php

/**
 * Admin status page: financial report
 */

require("inc/global.php");
require_admin();

require("layout/templates.php");

$messages = array();
$errors = array();

page_header("Admin: Financial Report", "page_admin_jobs");

$total_months = 6;
$months = array();
for ($i = $total_months - 1; $i >= 0; $i--) {
	$y = date('Y', strtotime('-' . $i . ' months'));
	$m = date('m', strtotime('-' . $i . ' months'));
	$months[] = array(
		'start' => sprintf("%04d-%02d-01", $y, $m) . ' 00:00:00',
		'end' => date('Y-m-d', strtotime(sprintf('%04d-%02d-01', $m == 12 ? $y + 1 : $y, $m == 12 ? 1 : $m + 1) . " -1 day")) . ' 23:59:59',
	);
}
?>

<h1>Financial Report</h1>

<p class="backlink"><a href="<?php echo htmlspecialchars(url_for('admin')); ?>">&lt; Back to Site Status</a></p>

<table class="standard">
<thead>
	<tr>
		<th>Measurement</th>
		<?php foreach ($months as $m) {
			echo "<th>" . htmlspecialchars(date('M y', strtotime($m['start']))) . "</th>";
		} ?>
		<th>Total</th>
	</tr>
</thead>
<tbody>
<?php
$queries = array(
	"New users" => array(
		'query' => "SELECT COUNT(*) AS c FROM users WHERE created_at >= :start AND created_at <= :end",
		'callback' => 'number_format',
	),
	"Total users" => array(
		'query' => "SELECT COUNT(*) AS c FROM users WHERE created_at <= :end AND :start = :start",
		'callback' => 'number_format',
	),
	"Current Premium users" => array(
		'query' => "SELECT COUNT(*) AS c FROM users WHERE created_at <= :end AND :start = :start AND is_premium=1",
		'callback' => 'number_format',
	),
	"Completed premiums" => array(
		'query' => "SELECT COUNT(*) AS c FROM outstanding_premiums WHERE is_paid=1 AND (created_at >= :start AND created_at <= :end)",
		'callback' => 'number_format',
	),
	"Income (BTC)" => array(
		'query' => "SELECT IFNULL(SUM(balance), 0) AS c FROM outstanding_premiums
			JOIN premium_addresses ON outstanding_premiums.premium_address_id=premium_addresses.id
			WHERE is_paid=1 AND (outstanding_premiums.created_at >= :start AND outstanding_premiums.created_at <= :end) AND currency='btc'",
		'callback' => 'number_format_autoprecision',
	),
	"Income (LTC)" => array(
		'query' => "SELECT IFNULL(SUM(balance), 0) AS c FROM outstanding_premiums
			JOIN premium_addresses ON outstanding_premiums.premium_address_id=premium_addresses.id
			WHERE is_paid=1 AND (outstanding_premiums.created_at >= :start AND outstanding_premiums.created_at <= :end) AND currency='ltc'",
		'callback' => 'number_format_autoprecision',
	),
);
foreach ($queries as $query_title => $query) {
	echo "<tr>\n";
	echo "<th>" . htmlspecialchars($query_title) . "</th>\n";
	foreach ($months as $m) {
		$q = db()->prepare($query['query']);
		$q->execute(array('start' => $m['start'], 'end' => $m['end']));
		$result = $q->fetch();
		echo "<td class=\"number\">" . $query['callback']($result['c']) . "</td>";
	}
	{
		// total
		$q = db()->prepare($query['query']);
		$q->execute(array('start' => '2001-01-01', 'end' => '2049-01-01'));
		$result = $q->fetch();
		echo "<td class=\"number\">" . $query['callback']($result['c']) . "</td>";
	}
	echo "</tr>\n";
}
$account_data_grouped = account_data_grouped();
$account_data_grouped['Instances'] = array(
	'ticker' => array('title' => 'Ticker instances', 'table' => 'ticker'),
	'balance' => array('title' => 'Balance instances', 'table' => 'balances'),
	'summary' => array('title' => 'Summary instances', 'table' => 'summary_instances'),
	'hashrates' => array('title' => 'Hashrate instances', 'table' => 'hashrates'),
	'securities_btct' => array('title' => 'BTCT securities', 'table' => 'securities_btct'),
	'securities_cryptostocks' => array('title' => 'Cryptostocks securities', 'table' => 'securities_cryptostocks'),
	'securities_litecoinglobal' => array('title' => 'Litecoinglobal securities', 'table' => 'securities_litecoinglobal'),
	'jobs' => array('title' => 'Jobs', 'table' => 'jobs'),
	'exceptions' => array('title' => 'Uncaught exceptions', 'table' => 'uncaught_exceptions'),
	'archived_ticker' => array('title' => 'Archived ticker instances', 'table' => 'graph_data_ticker'),
	'archived_balance' => array('title' => 'Archived balance instances', 'table' => 'graph_data_balances'),
	'archived_summary' => array('title' => 'Archived summary instances', 'table' => 'graph_data_summary'),
);
foreach ($account_data_grouped as $label => $group) {
	echo "<tr><td class=\"hr\" colspan=\"" . ($total_months+1) . "\">" . htmlspecialchars($label) . "</td></tr>\n";
	foreach ($group as $key => $data) {
		echo "<tr><th>" . htmlspecialchars($data['title']) . "</th>";
		foreach ($months as $m) {
			$q = db()->prepare("SELECT COUNT(*) AS c FROM " . $data['table'] . " WHERE (created_at >= :start AND created_at <= :end)" . (isset($data['query']) ? $data['query'] : ""));
			$q->execute(array('start' => $m['start'], 'end' => $m['end']));
			$result = $q->fetch();
			echo "<td class=\"number\">" . $query['callback']($result['c']) . "</td>";
		}
		{
			// total
			$q = db()->prepare("SELECT COUNT(*) AS c FROM " . $data['table'] . " WHERE 1" . (isset($data['query']) ? $data['query'] : ""));
			$q->execute(array('start' => '2001-01-01', 'end' => '2049-01-01'));
			$result = $q->fetch();
			echo "<td class=\"number\">" . $query['callback']($result['c']) . "</td>";
		}
		echo "</tr>";
	}
}
?>
</tbody>
</table>

<?php

page_footer();
