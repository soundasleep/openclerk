<div class="home-block" id="home_block_top">
<a href="<?php echo htmlspecialchars(url_for('screenshots#screenshots_profile_summary')); ?>" title="<?php echo ht("Screenshots"); ?>"><div class="splash"></div></a>

<h1><?php echo ht("Welcome to :site_name"); ?> <small class="beta"><?php echo ht("Beta"); ?></small></h1>

<p>
	<?php echo ht(":site_name lets you keep track of your cryptocurrencies, miners, investments and equities, and generates regular reports of your portfolio."); ?>
</p>

<div class="screenshots_group">
<ul class="screenshots">
<?php if (user_logged_in()) { ?>
	<li class="profile"><a href="<?php echo htmlspecialchars(url_for('profile')); ?>"><?php echo ht("View your Reports"); ?></a></li>
<?php } else { ?>
	<li class="signup"><a href="<?php echo htmlspecialchars(url_for('signup')); ?>"><?php echo ht("Signup for Free"); ?></a></li>
<?php } ?>
	<li><a href="<?php echo htmlspecialchars(url_for('features')); ?>"><?php echo ht("Features"); ?></a></li>
</ul>
</div>
</div>

<?php
if (in_premium_promotion_leadup_period()) {
	require_template("premium_promotion");
}
?>

<hr>

<div class="home-block" id="home_block_features">
<h2><?php echo ht("Features"); ?></h2>

<div class="feature feature-left" id="home_feature_addresses">
	<a href="<?php echo htmlspecialchars(url_for('screenshots#screenshots_litecoin')); ?>" title="<?php echo ht("Screenshots on adding cryptocurrency addresses"); ?>"><div class="splash"></div></a>
	<h3><?php echo ht("Track addresses"); ?></h3>

	<p>
		<?php echo ht("Cryptocurrency addresses can be supplied manually or by exporting CSV files from your wallet software, and balances downloaded through public explorer APIs."); ?>
	</p>

	<p>
		<a href="<?php echo htmlspecialchars(url_for('kb', array('q' => 'cryptocurrencies'))); ?>"><?php echo ht("What are cryptocurrencies?"); ?></a>
	</p>
</div>

<div class="feature feature-right" id="home_feature_accounts">
	<a href="<?php echo htmlspecialchars(url_for('screenshots#screenshots_accounts')); ?>" title="<?php echo ht("Screenshots on adding accounts"); ?>"><div class="splash"></div></a>
	<h3><?php echo ht("Track exchanges and miners"); ?></h3>

	<p>
		<?php echo ht("Exchanges, mining pools and security exchanges can be tracked through read-only APIs. The performance of mining hardware through mining pools is also tracked."); ?>
	</p>

	<p>
		<a href="<?php echo htmlspecialchars(url_for('kb', array('q' => 'notifications'))); ?>"><?php echo ht("Configure e-mail notifications"); ?></a> <span class="new"><?php echo ht("new"); ?></span>
	</p>
</div>

<div class="feature feature-left" id="home_feature_reports">
	<a href="<?php echo htmlspecialchars(url_for('screenshots#screenshots_profile_addgraph')); ?>" title="<?php echo ht("View screenshots on personalising your reports"); ?>"><div class="splash"></div></a>
	<h3><?php echo ht("View Reports"); ?></h3>

	<p>
		<?php echo ht(":site_name can automatically personalise reports for you, or you can take full control and design your own report pages with custom graphs, layouts and technical indicators."); ?>
	</p>
</div>

<div class="feature feature-right" id="home_feature_secure">
	<a href="http://openclerk.org" target="_blank"><div class="splash"></div></a>
	<h3><?php echo ht("Safe and secure"); ?></h3>

	<p>
		<?php echo t("All data and addresses are read-only, the :software is open source, and no passwords are stored. This means your accounts and funds will always be safe.", array(':software' => '<a href="http://openclerk.org" target="_blank">' . ht("underlying software") . '</a>')); ?>
	</p>
</div>

<div class="screenshots_group">
<ul class="screenshots">
<?php if (user_logged_in()) { ?>
	<li class="profile"><a href="<?php echo htmlspecialchars(url_for('profile')); ?>"><?php echo ht("View your Reports"); ?></a></li>
<?php } else { ?>
	<li class="signup"><a href="<?php echo htmlspecialchars(url_for('signup')); ?>"><?php echo ht("Signup for Free"); ?></a></li>
<?php } ?>
	<li><a href="<?php echo htmlspecialchars(url_for('screenshots')); ?>"><?php echo ht("Screenshots"); ?></a></li>
	<li><a href="<?php echo htmlspecialchars(url_for('features')); ?>"><?php echo ht("All Features..."); ?></a></li>
</ul>
</div>
</div>

<hr>

<div class="home-block" id="home_block_support">
<h2><?php echo ht("Supported Addresses and Accounts"); ?></h2>

<?php require(__DIR__ . "/_supported.php"); ?>
</div>

<div class="banner">
	<a href="<?php echo htmlspecialchars(url_for('screenshots')); ?>" title="<?php echo ht("Screenshots"); ?>"><img src="<?php echo htmlspecialchars(url_for('img/screenshots/banner2_small.png')); ?>" alt="<?php echo ht("Example reports and graphs"); ?>"></a>
</div>
