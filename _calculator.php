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
	$rates = array();
	foreach (get_all_currencies() as $cur1) {
		foreach (get_all_currencies() as $cur2) {
			if ($cur1 == $cur2) continue;

			$rate = -1;
			$exchange = array();

			if ($cur1 == "btc") {
				if (!in_array($cur2, get_all_fiat_currencies())) {
					// btc/ltc and btc/ghs
					$exchange = get_default_currency_exchange($cur2);
					$ticker = get_latest_ticker($exchange, $cur1, $cur2);
					$rate = 1 / $ticker['last_trade'];
				} else {
					// btc/usd
					$exchange = get_default_currency_exchange($cur2);
					$ticker = get_latest_ticker($exchange, $cur2, $cur1);
					$rate = $ticker['last_trade'];
				}
			} else if ($cur2 == "btc") {
				if (!in_array($cur1, get_all_fiat_currencies())) {
					// btc/ltc and btc/ghs
					$exchange = get_default_currency_exchange($cur1);
					$ticker = get_latest_ticker($exchange, $cur2, $cur1);
					$rate = $ticker['last_trade'];
				} else {
					// btc/usd
					$exchange = get_default_currency_exchange($cur1);
					$ticker = get_latest_ticker($exchange, $cur1, $cur2);
					$rate = 1 / $ticker['last_trade'];
				}
			} else {
				// first have to convert to btc and then to the target currency
				if (!in_array($cur1, get_all_fiat_currencies())) {
					// ltc/? and ghs/?
					$exchange = array(get_default_currency_exchange($cur1));
					$ticker = get_latest_ticker($exchange[0], 'btc', $cur1);
					$rate = $ticker['last_trade'];
				} else {
					// usd/?
					$exchange = array(get_default_currency_exchange($cur1));
					$ticker = get_latest_ticker($exchange[0], $cur1, 'btc');
					$rate = 1 / $ticker['last_trade'];
				}

				// and then to the second currency
				if (!in_array($cur2, get_all_fiat_currencies())) {
					// ?/ltc and ?/ghs
					$exchange[] = get_default_currency_exchange($cur2);
					$ticker = get_latest_ticker($exchange[1], 'btc', $cur2);
					$rate = $rate / $ticker['last_trade'];
				} else {
					// ?/usd
					$exchange[] = get_default_currency_exchange($cur2);
					$ticker = get_latest_ticker($exchange[1], $cur2, 'btc');
					$rate = $rate * $ticker['last_trade'];
				}

			}
			$rates[$cur1 . '_' . $cur2] = array(
				'rate' => $rate,
				'exchanges' => is_array($exchange) ? get_exchange_name($exchange[0]) . " and " . get_exchange_name($exchange[1]) : get_exchange_name($exchange),
			);

		}
	}
	echo json_encode($rates);
	?>;
}
</script>
