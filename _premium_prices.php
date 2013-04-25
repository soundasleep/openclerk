<div class="premium_prices">
<h3>Current premium prices</h3>

<table>
<?php foreach (get_site_config('premium_currencies') as $currency) { ?>
<tr>
	<th><?php echo htmlspecialchars(get_currency_name($currency)); ?></th>
	<td><?php echo currency_format($currency, get_site_config('premium_' . $currency . '_monthly')); ?> per month, or
		<?php echo currency_format($currency, get_site_config('premium_' . $currency . '_yearly')); ?> per year</td>
	<td>
		<form action="<?php echo htmlspecialchars(url_for('purchase')); ?>" method="post">
			<input type="hidden" name="currency" value="<?php echo htmlspecialchars($currency); ?>">
			<input type="submit" value="Purchase">
		</form>
	</td>
</tr>
<?php } ?>
</table>
</div>