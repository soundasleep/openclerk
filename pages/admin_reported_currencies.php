<?php

/**
 * Admin reported currencies page (#121).
 */

require_admin();

require(__DIR__ . "/../layout/templates.php");
require(__DIR__ . "/../layout/graphs.php");

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

$exchanges = \DiscoveredComponents\Exchanges::getAllInstances();
foreach ($exchanges as $exchange) {
  $persistent = new \Core\PersistentExchange($exchange, db());
  $markets = $persistent->getMarkets();

  $matrix[$exchange->getCode()] = array();

  foreach ($markets as $pair) {
    $c = get_currency_key($pair[0]);
    $matrix[$exchange->getCode()][$c] = 1;
    $all_currencies[$c] = 1;
  }

  foreach ($markets as $pair) {
    $c = get_currency_key($pair[1]);
    $matrix[$exchange->getCode()][$c] = 1;
    $all_currencies[$c] = 1;
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
  echo "<th>" . htmlspecialchars($exchange->getName()) . "</th>";
  foreach ($all_currencies as $cur => $ignored) {
    if (require_get("only_supported", false) && !in_array($cur, get_all_currencies()))
      continue;

    $class = isset($matrix[$exchange->getCode()][$cur]) ? "reported" : "";

    // do we have at least one exchange pair for this defined?
    $pair_supported = false;
    foreach ($exchange_pairs[$exchange->getCode()] as $pair) {
      if ($pair[0] == $cur || $pair[1] == $cur) {
        $pair_supported = true;
      }
    }

    if (isset($get_supported_wallets[$exchange->getCode()]) && in_array($cur, $get_supported_wallets[$exchange->getCode()])) {
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
