<?php

/**
 * Admin reported currencies page (#121).
 */

require(__DIR__ . "/inc/global.php");
require_admin();

require(__DIR__ . "/layout/templates.php");
require(__DIR__ . "/layout/graphs.php");

$messages = array();
$errors = array();

page_header("Reported Currencies", "page_reported_currencies");

?>

<h1>Reported Currencies</h1>

<p class="backlink"><a href="<?php echo htmlspecialchars(url_for('admin')); ?>">&lt; Back to Site Status</a>
| <a href="<?php echo htmlspecialchars(url_for('admin_reported_currencies', array('only_supported' => 0))); ?>">All currencies</a>
| <a href="<?php echo htmlspecialchars(url_for('admin_reported_currencies', array('only_supported' => 1))); ?>">Only supported currencies</a>
</p>

<?php
$matrix = array();

// create a matrix of exchanges to currencies
$q = db()->prepare("SELECT * FROM exchanges ORDER BY name ASC");
$q->execute();
$exchanges = $q->fetchAll();
$all_currencies = array();
foreach ($exchanges as $i => $exchange) {

	$matrix[$exchange['name']] = array();

	$q = db()->prepare("SELECT * FROM reported_currencies WHERE exchange=?");
	$q->execute(array($exchange['name']));
	while ($cur = $q->fetch()) {
		$c = get_currency_key($cur['currency']);
		$matrix[$exchange['name']][$c] = 1;
		$all_currencies[$c] = 1;
		$exchanges[$i]['reported_currencies_created_at'] = $cur['created_at'];
	}

}
// add all currencies we natively support
foreach (get_all_currencies() as $cur) {
	$all_currencies[$cur] = $cur;
}
ksort($all_currencies);

// now render it
echo "<table class=\"fancy reported-currencies\">";
echo "<tr>";
echo "<th>Exchange</th>";
echo "<th>Reported</th>";
foreach ($all_currencies as $cur => $ignored) {
	$class = in_array($cur, get_all_currencies()) ? "supported" : "";

	if (require_get("only_supported", false) && !in_array($cur, get_all_currencies()))
		continue;

	echo "<th class=\"$class\">" . htmlspecialchars($cur) . "</th>";
}
echo "</tr>\n";

$exchange_pairs = get_exchange_pairs();
$get_supported_wallets = get_supported_wallets();

foreach ($exchanges as $exchange) {
	echo "<tr>";
	echo "<th>" . get_exchange_name($exchange['name']) . "</th>";
	echo $exchange['track_reported_currencies'] ? "<td>" . recent_format_html($exchange['reported_currencies_created_at']) . "</td>" : "<td>-</td>";
	foreach ($all_currencies as $cur => $ignored) {
		if (require_get("only_supported", false) && !in_array($cur, get_all_currencies()))
			continue;

		$class = isset($matrix[$exchange['name']][$cur]) ? "reported" : "";

		// do we have at least one exchange pair for this defined?
		$pair_supported = false;
		foreach ($exchange_pairs[$exchange['name']] as $pair) {
			if ($pair[0] == $cur || $pair[1] == $cur) {
				$pair_supported = true;
			}
		}

		if (isset($get_supported_wallets[$exchange['name']]) && in_array($cur, $get_supported_wallets[$exchange['name']])) {
			$class .= " wallet";
		}

		$class .= $pair_supported ? " supported" : "";
		echo "<td class=\"$class\">$class</td>";
	}
	echo "</tr>";
}

echo "</table>";

?>

<?php
page_footer();
