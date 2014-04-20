<?php

/**
 * This page is the first page in a series of wizards to configure a user account.
 * A user may revisit this page at any time to reconfigure their account.
 * This page allows the user to select their currencies and default exchanges.
 */

require(__DIR__ . "/../inc/global.php");
require_login();

require(__DIR__ . "/../layout/templates.php");
page_header("Currency Preferences", "page_wizard_currencies", array('js' => array('wizard'), 'class' => 'page_accounts'));

$user = get_user(user_id());
require_user($user);

$messages = array();

// get all of our accounts
$accounts = user_limits_summary(user_id());

$cryptos = get_all_cryptocurrencies();
$fiats = get_all_fiat_currencies();
$commodities = get_all_commodity_currencies();

// get all of our summaries
$summaries = array();
$q = db()->prepare("SELECT * FROM summaries WHERE user_id=?");
$q->execute(array(user_id()));
while ($s = $q->fetch()) {
	$summaries[$s['summary_type']] = $s;
}

?>

<?php require_template("wizard_currencies"); ?>

<form action="<?php echo htmlspecialchars(url_for('wizard_currencies_post')); ?>" method="post" class="wizard">

<div class="cryptocurrencies">
<h2>Cryptocurrencies</h2>

<ul>
<?php foreach ($cryptos as $c) { ?>
	<li>
		<input type="checkbox" name="currencies[]" value="<?php echo htmlspecialchars($c); ?>" id="currencies_<?php echo htmlspecialchars($c); ?>"<?php echo isset($summaries["summary_" . $c]) ? " checked" : ""; ?>>
		<label for="currencies_<?php echo htmlspecialchars($c); ?>" class="currency_name_<?php echo htmlspecialchars($c); ?>"><?php echo htmlspecialchars(get_currency_name($c)); ?></label>
	</li>
<?php } ?>
</ul>
</div>

<hr>

<div class="fiatcurrencies">
<h2>Fiat currencies</h2>

<ul>
<?php foreach ($fiats as $c) {
	$exchanges = array();
	$selected = false;
	foreach (get_summary_types() as $key => $summary) {
		$prefix = "summary_" . $c;
		if (substr($key, 0, strlen($prefix)) == $prefix) {
			$exchanges[$summary['exchange']] = $key;
			$selected = $selected || isset($summaries[$key]);
		}
	}
?>
	<li>
		<input type="checkbox" name="currencies[]" value="<?php echo htmlspecialchars($c); ?>" id="currencies_<?php echo htmlspecialchars($c); ?>"<?php echo $selected ? " checked" : ""; ?> class="parent-currency">
		<label for="currencies_<?php echo htmlspecialchars($c); ?>" class="currency_name_<?php echo htmlspecialchars($c); ?>"><?php echo htmlspecialchars(get_currency_name($c)); ?></label>

		<div class="exchange"><span class="exchange-text">Exchange: <?php echo htmlspecialchars(get_exchange_name(get_default_currency_exchange($c))); ?></span>
			<a class="collapse-link collapsed">+</a>

			<div class="collapse-target">
			Instead of the default exchange, use the following exchanges:
			<ul>
			<?php foreach ($exchanges as $exchange => $key) {
				?>
				<li>
					<input type="checkbox" name="exchanges[]" value="<?php echo htmlspecialchars($key); ?>" id="exchanges_<?php echo htmlspecialchars($key); ?>"<?php echo isset($summaries[$key]) ? " checked" : ""; ?>>
					<label for="exchanges_<?php echo htmlspecialchars($key); ?>" class="<?php echo (get_default_currency_exchange($c) == $exchange) ? "default-exchange" : ""; ?>"><?php echo htmlspecialchars(get_exchange_name($exchange)); ?></label>
				</li>
				<?php
			}
			?>
			</ul>
			</div>
		</div>
	</li>
<?php } ?>
</ul>
</div>

<hr>

<div class="commoditycurrencies">
<h2>Commodity currencies</h2>

<ul>
<?php foreach ($commodities as $c) { ?>
	<li>
		<input type="checkbox" name="currencies[]" value="<?php echo htmlspecialchars($c); ?>" id="currencies_<?php echo htmlspecialchars($c); ?>"<?php echo isset($summaries["summary_" . $c]) ? " checked" : ""; ?>>
		<label for="currencies_<?php echo htmlspecialchars($c); ?>" class="currency_name_<?php echo htmlspecialchars($c); ?>"><?php echo htmlspecialchars(get_currency_name($c)); ?></label>
	</li>
<?php } ?>
</ul>
</div>

<p class="warning-inline">
<b>NOTE:</b> Removing a currency will also permanently remove any historical summary data for that currency.
</p>

<div class="wizard-buttons">
<input type="submit" name="submit" value="Next &gt;">
</div>
</form>

<?php

require_template("wizard_currencies_footer");

page_footer();
