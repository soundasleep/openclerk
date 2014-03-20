<?php

/**
 * /api/v1/rates.json
 */
function api_get_all_rates($with_extra = true) {
	$rates = array();
	foreach (get_all_currencies() as $cur1) {
		foreach (get_all_currencies() as $cur2) {
			if ($cur1 == $cur2) continue;

			$rate = -1;
			$exchange = array();
			$pair = array();

			if ($cur1 == "btc") {
				if (!is_fiat_currency($cur2)) {
					// btc/ltc and btc/ghs
					$exchange = get_default_currency_exchange($cur2);
					$ticker = get_latest_ticker($exchange, $cur1, $cur2);
					$rate = 1 / $ticker['last_trade'];
					$pair = $cur1 . $cur2;
				} else {
					// btc/usd
					$exchange = get_default_currency_exchange($cur2);
					$ticker = get_latest_ticker($exchange, $cur2, $cur1);
					$rate = $ticker['last_trade'];
					$pair = $cur2 . $cur1;
				}
			} else if ($cur2 == "btc") {
				if (!is_fiat_currency($cur1)) {
					// btc/ltc and btc/ghs
					$exchange = get_default_currency_exchange($cur1);
					$ticker = get_latest_ticker($exchange, $cur2, $cur1);
					$rate = $ticker['last_trade'];
					$pair = $cur2 . $cur1;
				} else {
					// btc/usd
					$exchange = get_default_currency_exchange($cur1);
					$ticker = get_latest_ticker($exchange, $cur1, $cur2);
					$rate = 1 / $ticker['last_trade'];
					$pair = $cur1 . $cur2;
				}
			} else {
				// first have to convert to btc and then to the target currency
				if (!is_fiat_currency($cur1)) {
					// ltc/? and ghs/?
					$exchange = array(get_default_currency_exchange($cur1));
					$ticker = get_latest_ticker($exchange[0], 'btc', $cur1);
					$rate = $ticker['last_trade'];
					$pair = array('btc' . $cur1);
				} else {
					// usd/?
					$exchange = array(get_default_currency_exchange($cur1));
					$ticker = get_latest_ticker($exchange[0], $cur1, 'btc');
					$rate = 1 / $ticker['last_trade'];
					$pair = array($cur1 . 'btc');
				}

				// and then to the second currency
				if (!is_fiat_currency($cur2)) {
					// ?/ltc and ?/ghs
					$exchange[] = get_default_currency_exchange($cur2);
					$ticker = get_latest_ticker($exchange[1], 'btc', $cur2);
					$rate = $rate / $ticker['last_trade'];
					$pair[] = 'btc' . $cur2;
				} else {
					// ?/usd
					$exchange[] = get_default_currency_exchange($cur2);
					$ticker = get_latest_ticker($exchange[1], $cur2, 'btc');
					$rate = $rate * $ticker['last_trade'];
					$pair[] = $cur2 . 'btc';
				}

			}
			$rates[$cur1 . '_' . $cur2] = array(
				'rate' => $rate,
				'exchanges' => is_array($exchange) ? get_exchange_name($exchange[0]) . " and " . get_exchange_name($exchange[1]) : get_exchange_name($exchange),
			);
			if (is_array($exchange)) {
				$rates[$cur1 . '_' . $cur2]['exchange1'] = get_exchange_name($exchange[0]);
				$rates[$cur1 . '_' . $cur2]['exchange2'] = get_exchange_name($exchange[1]);
				if ($with_extra) {
					$rates[$cur1 . '_' . $cur2]['pair1'] = $pair[0];
					$rates[$cur1 . '_' . $cur2]['pair2'] = $pair[1];
					$rates[$cur1 . '_' . $cur2]['url1'] = absolute_url(url_for('historical', array('id' => $exchange[0] . '_' . $pair[0] . '_daily')));		// TODO add analytics
					$rates[$cur1 . '_' . $cur2]['url2'] = absolute_url(url_for('historical', array('id' => $exchange[0] . '_' . $pair[1] . '_daily')));
				}
			} else {
				$rates[$cur1 . '_' . $cur2]['exchange1'] = get_exchange_name($exchange);
				if ($with_extra) {
					$rates[$cur1 . '_' . $cur2]['pair1'] = $pair;
					$rates[$cur1 . '_' . $cur2]['url1'] = absolute_url(url_for('historical', array('id' => $exchange . '_' . $pair . '_daily')));		// TODO add analytics
				}
			}

		}
	}

	return $rates;

}
