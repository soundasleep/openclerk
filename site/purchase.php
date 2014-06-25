<?php

/**
 * Purchase premium accounts.
 */

require(__DIR__ . "/../inc/global.php");
require_login();

require(__DIR__ . "/../layout/templates.php");

$messages = array();
$errors = array();

$user = get_user(user_id());
require_user($user);

$currency = require_post("currency", require_get("currency", false));
if (!$currency || !is_valid_currency($currency) || !in_array($currency, get_site_config('premium_currencies'))) {
	$errors[] = t("Unknown currency or no currency specified.");
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
			throw new PurchaseException(t("Invalid period selection."));
		}

		$cost = 0;
		if ($months > 0) {
			$cost += wrap_number(get_premium_price($currency, 'monthly') * $months, 8);
		}
		if ($years > 0) {
			$cost += wrap_number(get_premium_price($currency, 'yearly') * $years, 8);
		}
		if ($cost == 0) {
			throw new PurchaseException(t("Could not calculate any cost"));
		}

		// find an unused $currency address and register it to the system
		$q = db()->prepare("SELECT * FROM premium_addresses WHERE is_used=0 AND currency=?");
		$q->execute(array($currency));
		$address = $q->fetch();
		if (!$address) {
			throw new PurchaseException(t("Could not generate :currency address for purchase; please try again later.", array(':currency' => get_currency_abbr($currency))));
		}

		// register it to the system as a normal blockchain address, but we need to get received rather than balance
		$q = db()->prepare("INSERT INTO addresses SET user_id=:user_id, address=:address, currency=:currency, is_received=1");
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
			send_user_email($user, "purchase", array(
				"name" => ($user['name'] ? $user['name'] : $user['email']),
				"amount" => number_format_autoprecision($cost),
				"currency" => get_currency_abbr($currency),
				"currency_name" => get_currency_name($currency),
				"address" => $address['address'],
				"explorer" => get_site_config($currency . '_address_url'),
				"url" => absolute_url(url_for("user#user_outstanding")),
			));
		}

		// success! inform the user
		redirect(url_for('user#user_outstanding', array('new_purchase' => $purchase_id)));

	} catch (PurchaseException $e) {
		log_uncaught_exception($e);
		$errors[] = $e->getMessage();
	}
}

page_header(t("Purchase Premium"), "page_purchase", array('js' => 'purchase'));

?>

<h1><?php echo ht("Purchase Premium with :currency", array(':currency' => get_currency_name($currency))); ?></h1>

<div class="columns2">
<div class="column">
<form action="<?php echo htmlspecialchars(url_for('purchase')); ?>" method="post">
<table class="form fancy">
<tr>
	<th><?php echo t("Purchase months (:cost/month)", array(':cost' => currency_format($currency, get_premium_price($currency, 'monthly')))); ?></td>
	<td>
		<select name="months" id="monthly">
			<option value="0" selected></option>
			<?php for ($i = 1; $i <= 11; $i++) {
				echo "<option value=\"$i\">" . htmlspecialchars(plural("month", $i)) . " " . currency_format($currency, wrap_number(get_premium_price($currency, 'monthly') * $i, 8), 8) . "</option>\n";
			} ?>
		</select>
	</td>
</tr>
<tr>
	<th colspan="2" class="hr">
		<?php echo ht("OR"); ?>
	</td>
</tr>
<tr>
	<th><?php echo t("Purchase years (:cost/year)", array(':cost' => currency_format($currency, get_premium_price($currency, 'yearly')))); ?></td>
	<td>
		<select name="years" id="yearly">
			<option value="0" selected></option>
			<?php for ($i = 1; $i <= 5; $i++) {
				echo "<option value=\"$i\">" . htmlspecialchars(plural("year", $i)) . " " . currency_format($currency, wrap_number(get_premium_price($currency, 'yearly') * $i, 8), 8) . "</option>\n";
			} ?>
		</select>
	</td>
</tr>
<tr>
	<td colspan="2" class="buttons">
		<input type="hidden" name="currency" value="<?php echo htmlspecialchars($currency); ?>">
		<button type="submit" class="purchase_button"><span class="currency_name_<?php echo htmlspecialchars($currency); ?>">Purchase</span></button>
	</td>
</tr>
</table>
</form>

</div>
<div class="column">

<p>
<?php
echo ht("Once you have submitted your order, a :currency address will be generated for your payment.
		Your premium purchase will complete once the :currency network confirms your transaction.",
	array(
		':currency' => get_currency_name($currency),
	));
?>
</p>

</div>
</div>

<?php
page_footer();
