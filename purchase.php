<?php

/**
 * Purchase premium accounts.
 */

require("inc/global.php");
require_login();

require("layout/templates.php");

$messages = array();
$errors = array();

$user = get_user(user_id());
require_user($user);

$currency = require_post("currency", require_get("currency", false));
if (!$currency || !is_valid_currency($currency) || !in_array($currency, get_site_config('premium_currencies'))) {
	$errors[] = "Unknown currency or no currency specified.";
	set_temporary_errors($errors);
	redirect(url_for('premium'));
}

$messages = array();
$errors = array();
class PurchaseException extends Exception { }
if (require_post("months", false) || require_post("years", false)) {
	try {
		$months = require_post("months", false);
		$years = require_post("years", false);
		if (!is_numeric($months) || !is_numeric($years) || !($months > 0 || $years > 0) || $months > 99 || $years > 99) {
			throw new PurchaseException("Invalid period selection.");
		}

		$cost = 0;
		if ($months > 0) {
			$cost += get_site_config('premium_' . $currency . '_monthly') * $months;
		}
		if ($years > 0) {
			$cost += get_site_config('premium_' . $currency . '_yearly') * $years;
		}
		if ($cost == 0) {
			throw new PurchaseException("Could not calculate any cost");
		}

		// find an unused $currency address and register it to the system
		$q = db()->prepare("SELECT * FROM premium_addresses WHERE is_used=0 AND currency=?");
		$q->execute(array($currency));
		$address = $q->fetch();
		if (!$address) {
			throw new Exception("Could not generate " . strtoupper($currency) . " address for purchase; please try again later.");
		}

		// register it to the system as a normal blockchain address
		$q = db()->prepare("INSERT INTO addresses SET user_id=:user_id, address=:address, currency=:currency");
		$q->execute(array(
			"user_id" => get_site_config('system_user_id'),
			"address" => $address['address'],
			"currency" => $currency,
		));
		$new_address_id = db()->lastInsertId();

		// create a new outstanding premium
		$q = db()->prepare("INSERT INTO outstanding_premiums SET user_id=:user_id, premium_address_id=:pid, address_id=:aid, balance=:balance, months=:months, years=:years");
		$q->execute(array(
			"user_id" => user_id(),
			"pid" => $address['id'],
			"aid" => $new_address_id,
			"balance" => $cost,
			"months" => $months,
			"years" => $years,
		));
		$purchase_id = db()->lastInsertId();

		// address is now in use
		$q = db()->prepare("UPDATE premium_addresses SET is_used=1,used_at=NOW() WHERE id=?");
		$q->execute(array($address['id']));

		// try sending email, if an email address has been registered
		if ($user['email']) {
			send_email($user['email'], ($user['name'] ? $user['name'] : $user['email']), "purchase", array(
				"name" => ($user['name'] ? $user['name'] : $user['email']),
				"amount" => $cost,
				"currency" => strtoupper($currency),
				"currency_name" => get_currency_name($currency),
				"address" => $address['address'],
				"explorer" => get_site_config($currency . '_address_url'),
				"url" => absolute_url(url_for("user")),
			));
		}

		// success! inform the user
		redirect(url_for('user', array('new_purchase' => $purchase_id)));

	} catch (PurchaseException $e) {
		$errors[] = $e->getMessage();
	}
}

page_header("Purchase Premium", "page_purchase", array('jquery' => true, 'js' => 'purchase'));

?>

<h1>Purchase Premium with <?php echo get_currency_name($currency); ?></h1>

<form action="<?php echo htmlspecialchars(url_for('purchase')); ?>" method="post">
<table class="form">
<tr>
	<td>Purchase months (<?php echo currency_format($currency, get_site_config('premium_' . $currency . '_monthly')); ?>/month)</td>
	<td>
		<select name="months" id="monthly">
			<option value="0" selected></option>
			<?php for ($i = 1; $i <= 11; $i++) {
				echo "<option value=\"$i\">" . number_format($i) . " months: " . currency_format($currency, get_site_config('premium_' . $currency . '_monthly') * $i, 4) . "</option>\n";
			} ?>
		</select>
	</td>
</tr>
<tr>
	<td colspan="2" class="hr">
		OR
	</td>
</tr>
<tr>
	<td>Purchase years (<?php echo currency_format($currency, get_site_config('premium_' . $currency . '_monthly')); ?>/year)</td>
	<td>
		<select name="years" id="yearly">
			<option value="0" selected></option>
			<?php for ($i = 1; $i <= 5; $i++) {
				echo "<option value=\"$i\">" . number_format($i) . " years: " . currency_format($currency, get_site_config('premium_' . $currency . '_yearly') * $i, 4) . "</option>\n";
			} ?>
		</select>
	</td>
</tr>
<tr>
	<td colspan="2" class="buttons">
		<input type="hidden" name="currency" value="<?php echo htmlspecialchars($currency); ?>">
		<input type="submit" value="Purchase">
	</td>
</tr>
</table>
</form>

<p>
Once you have submitted your order, a <?php echo get_currency_name($currency); ?> address will be generated for your payment.
Your premium purchase will complete once the <?php echo get_currency_name($currency); ?> network confirms your transaction.
</p>

<?php
page_footer();
