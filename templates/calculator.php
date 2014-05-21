<h1><?php echo ht("Cryptocurrency Calculator"); ?></h1>

<p>
	<?php echo t("
	This is a simple calculator that you can use to convert one currency into another currency,
	using the :recent_rates as tracked by :site_name.
	", array(
		":recent_rates" => link_to(url_for('historical'), t("most recent exchange rates")),
	)); ?>
</p>
