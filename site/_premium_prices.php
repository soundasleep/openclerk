<div class="premium_prices">
<h3><?php echo t("Current premium prices"); ?></h3>

<table class="fancy">
<?php foreach (get_site_config('premium_currencies') as $currency) { ?>
<tr>
	<th class="currency"><span class="currency_name_<?php echo $currency; ?>"><?php echo htmlspecialchars(get_currency_name($currency)); ?></span></th>
	<td class="prices">
		<?php if (get_site_config('premium_' . $currency . '_discount')) { ?>
		<div class="discounted"><?php
		echo ht(":cost1 per month, or :cost2 per year", array(
			'cost1' => currency_format($currency, get_site_config('premium_' . $currency . '_monthly')),
			'cost2' => currency_format($currency, get_site_config('premium_' . $currency . '_yearly')),
		)); ?></div>
		<?php } ?>
		<?php
		echo ht(":cost1 per month, or :cost2 per year", array(
			'cost1' => currency_format($currency, get_premium_price($currency, 'monthly')),
			'cost2' => currency_format($currency, get_premium_price($currency, 'yearly')),
		)); ?>
	</td>
	<td class="buttons">
		<form action="<?php echo htmlspecialchars(url_for('purchase')); ?>" method="post">
			<input type="hidden" name="currency" value="<?php echo htmlspecialchars($currency); ?>">
			<input type="submit" value="<?php echo ht("Purchase"); ?>">
		</form>
	</td>
</tr>
<?php } ?>
</table>
</div>
