<?php
$exchange = get_exchange_name("bitfunder");
$example = "ASICMINER";
?>

<div class="instructions_add">
<h2>Adding individual <?php echo htmlspecialchars($exchange); ?> securities</h2>

<ol class="steps">
	<li>As of version 0.10, you can manually add quantities of privately-owned securities
		to your portfolio, and estimate their value against those traded on <?php echo htmlspecialchars($exchange); ?>,
		by visiting your <a class="wizard_link" href="<?php echo htmlspecialchars(url_for('wizard_accounts_individual_securities')); ?>">"Individual Securities" wizard page</a>
		through your <a href="<?php echo htmlspecialchars(url_for('wizard_accounts_securities')); ?>">"Securities" wizard page</a>.<br>
		<img src="img/accounts/individual_securities.png"></li>
	</li>

	<li>For example, if you own 10 shares of <?php echo htmlspecialchars($example); ?> privately, then you can track these shares
		using one of the <?php echo htmlspecialchars($example); ?> passthrough values on the public <?php echo htmlspecialchars($exchange); ?> securities exchange.<br>
		<img src="img/accounts/individual_securities2.png"></li>
</ol>
</div>

<div class="instructions_safe">
<h2>Is it safe to provide <?php echo htmlspecialchars(get_site_config('site_name')); ?> details on individual securities?</h2>

<ul>
	<li>Because you are only specifying a security and a quantity, there is no way to actually verify that you own these securities,
		and there is no way to perform trades with these securities. This is merely a service to help owners of private
		securities understand what they may be worth publicly.</li>

	<li>You can modify the quantity of securities, or remove the details of your securities, at any time.</li>
</ul>
</div>