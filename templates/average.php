<?php
global $currency1, $currency2;
?>

<p>
	<?php echo ht("
	This page lists the most recent and historical average price index, as weighted by trade volume,
	from all of the :currency1/:currency2
	exchanges supported by :site_name.
	", array(
		":currency1" => get_currency_name($currency1),
		":currency2" => get_currency_name($currency2),
	)); ?>
</p>
