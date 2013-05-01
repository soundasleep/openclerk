<div class="premium_prices">
<h3>Current premium prices</h3>

<table class="fancy">
<?php foreach (get_site_config('premium_currencies') as $currency) { ?>
<tr>
	<th><span class="currency_name_<?php echo $currency; ?>"><?php echo htmlspecialchars(get_currency_name($currency)); ?></span></th>
	<td class="prices"><?php echo currency_format($currency, get_site_config('premium_' . $currency . '_monthly')); ?> per month, or
		<?php echo currency_format($currency, get_site_config('premium_' . $currency . '_yearly')); ?> per year</td>
	<td class="buttons">
		<form action="<?php echo htmlspecialchars(url_for('purchase')); ?>" method="post">
			<input type="hidden" name="currency" value="<?php echo htmlspecialchars($currency); ?>">
			<input type="submit" value="Purchase">
		</form>
	</td>
</tr>
<?php } ?>
</table>
</div>