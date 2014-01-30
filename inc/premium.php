<?php

/**
 * Various functionality related to premium accounts and limits.
 */

$global_user_limits_summary = null;
function user_limits_summary($user_id) {
	global $global_user_limits_summary;
	if ($global_user_limits_summary === null) {
		$accounts = array();

		foreach (account_data_grouped() as $group) {
			foreach ($group as $key => $data) {
				if (!isset($data['group']))
					continue;

				$q = db()->prepare("SELECT COUNT(*) AS c FROM " .  $data['table'] . " WHERE user_id=?" . (isset($data['query']) ? $data['query'] : ""));
				$q->execute(array($user_id));
				$accounts[$key] = $q->fetch();
				$accounts[$key] = $accounts[$key]['c'];

				if (!isset($accounts['total_' . $data['group']])) {
					$accounts['total_' . $data['group']] = 0;
				}
				$accounts['total_' . $data['group']] += $accounts[$key];

				if (!isset($data['wizard']))
					continue;

				if (!isset($accounts['wizard_' . $data['wizard']])) {
					$accounts['wizard_' . $data['wizard']] = 0;
				}
				$accounts['wizard_' . $data['wizard']] += $accounts[$key];
			}
		}

		$global_user_limits_summary = $accounts;
	}

	return $global_user_limits_summary;
}

/**
 * @param $keytype e.g. 'blockchain', 'mtgox', 'notification', ...
 */
function can_user_add($user, $keytype, $amount = 1) {
	$summary = user_limits_summary($user['id']);

	$data = get_account_data($keytype);
	$current_total = $summary['total_' . $data['group']];
	$limit = get_premium_value($user, $data['group']);
	return ($current_total + $amount) <= $limit;

	throw new Exception("Could not find user limit type '$keytype'");

}

/**
 * Get the current premium or free value for a particular group.
 */
function get_premium_value($user, $group) {
	return get_premium_config($group . "_" . ($user['is_premium'] ? 'premium' : 'free'));
}

/**
 * @param $period monthly or yearly
 */
function get_premium_price($currency, $period) {
	// because of floating point inaccuracy we need to round it to 8 decimal places, particularly before displaying it
	return wrap_number(get_site_config('premium_' . $currency . '_' . $period) * (1-get_site_config('premium_' . $currency . '_discount')), 8);
}

/**
 * @return a string that can be used in an e-mail, listing all prices
 */
function get_text_premium_prices() {
	$prices = array();
	foreach (get_site_config('premium_currencies') as $currency) {
		$prices[] = "  " . get_currency_abbr($currency) . ": " .
				number_format_autoprecision(get_premium_price($currency, 'monthly')) . " " . get_currency_abbr($currency) . "/month, or " .
				number_format_autoprecision(get_premium_price($currency, 'yearly')) . " " . get_currency_abbr($currency) . "/year" .
				(get_site_config('premium_' . $currency . '_discount') ? " (" . (int) (get_site_config('premium_' . $currency . '_discount') * 100) . "% off)" : "");
	}
	return implode("\n", $prices);
}
