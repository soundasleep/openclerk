<?php

/**
 * Admin page for displaying all Currency objects discovered in the system.
 */

require_admin();

require(__DIR__ . "/../layout/templates.php");
require(__DIR__ . "/../layout/graphs.php");

page_header("Admin: Currencies", "page_admin_currencies", array('js' => array('accounts')));

?>

<h1>Currencies</h1>

<p class="backlink"><a href="<?php echo htmlspecialchars(url_for('admin')); ?>">&lt; Back to Site Status</a></p>

<table class="standard standard_account_list">
<thead>
	<tr>
		<th class="default_sort_down">Code</th>
    <th>Name</th>
    <th>Abbr</th>
		<th>Type</th>
    <th>Explorer</th>
	</tr>
</thead>
<tbody>
<?php
  foreach (\DiscoveredComponents\Currencies::getAllInstances() as $code => $currency) {
    echo "<tr>";
    echo "<th>" . htmlspecialchars($code) . "</th>";
    echo "<td><span class=\"currency_name currency_" . $code . "\">" . htmlspecialchars($currency->getName()) . "</span></td>";
    echo "<td>" . htmlspecialchars($currency->getAbbr()) . "</td>";
    echo "<td>";
    if ($currency->isCryptocurrency()) {
      echo "Cryptocurrency";
    }
    if ($currency->isFiat()) {
      echo "Fiat";
    }
    if ($currency->isCommodity()) {
      echo "Commodity";
    }
    echo "</td>";
    echo "<td>";
    if ($currency->hasExplorer()) {
      echo "<span class=\"explorer_name explorer_" . $code . "\">" . link_to($currency->getExplorerURL(), $currency->getExplorerName()) . "<span>";
    } else {
      echo "-";
    }
    echo "</td>";
    echo "</tr>\n";
  }
?>
</tbody>
</table>

<?php
page_footer();
