<?php

$value1 = require_get("value1", "1");
$currency1 = require_get("currency1", "btc");
$value2 = require_get("value2", "");
$currency2 = require_get("currency2", "usd");

?>

<div class="calculator">
<span class="row">
<input type="text" id="value1" value="<?php echo htmlspecialchars($value1); ?>">
<select id="currency1">
<?php
	foreach (get_all_currencies() as $cur) {
		echo "<option value=\"" . htmlspecialchars($cur) . "\" class=\"currency_name_" . htmlspecialchars($cur) . "\"" . ($cur == $currency1 ? " selected" : "") . ">" . get_currency_abbr($cur) . "</option>\n";
	}
?>
</select>
</span>

<span class="row">
<span class="equals">=</span>
</span>

<span class="row">
<input type="text" id="value2" value="<?php echo htmlspecialchars($value2); ?>">
<select id="currency2">
<?php
	foreach (get_all_currencies() as $cur) {
		echo "<option value=\"" . htmlspecialchars($cur) . "\" class=\"currency_name_" . htmlspecialchars($cur) . "\"" . ($cur == $currency2 ? " selected" : "") . ">" . get_currency_abbr($cur) . "</option>\n";
	}
?>
</select>
</span>

<div class="using">Using <span id="exchange_text">no exchange</span></div>
</div>

<script type="text/javascript">
function get_all_rates() {
	return <?php

	require(__DIR__ . "/inc/api.php");
	$rates = api_get_all_rates(true /* with_extra */);

	echo json_encode($rates);
	?>;
}
</script>
