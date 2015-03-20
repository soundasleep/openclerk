<?php

/**
 * Admin status page: show the explorer and address reference used for each currency (#256)
 */

require_admin();

require(__DIR__ . "/../layout/templates.php");

$messages = array();
$errors = array();

page_header("Admin: Show Explorers", "page_admin_show_explorers");

?>

<h1>Currency Explorers</h1>

<p class="backlink"><a href="<?php echo htmlspecialchars(url_for('admin')); ?>">&lt; Back to Site Status</a></p>

<ul>
<?php
$grouped = account_data_grouped();
$external = get_external_apis();
foreach ($grouped['Addresses'] as $key => $data) {
  echo "<li><span style=\"display: inline-block; min-width: 250px;\">";
  echo get_currency_abbr($data['currency']);
  echo " using " . $external['Address balances'][$key];
  echo ":</span> ";
  echo crypto_address($data['currency'], 'example');
  echo "</li>";
}
?>
</ul>

<?php
page_footer();
