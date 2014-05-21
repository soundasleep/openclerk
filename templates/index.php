<div class="home-block" id="home_block_top">
<a href="<?php echo htmlspecialchars(url_for('screenshots#screenshots_profile_summary')); ?>" title="Screenshots"><div class="splash"></div></a>

<h1><?php echo ht("Welcome to :name", array(":name" => get_site_config('site_name'))); ?> <small class="beta"><?php echo ht("Beta"); ?></small></h1>

<p>
	<?php echo htmlspecialchars(get_site_config('site_name')); ?> lets you keep track of your cryptocurrencies, miners, investments and equities, and generates regular reports of your portfolio.
</p>

<div class="screenshots_group">
<ul class="screenshots">
<?php if (user_logged_in()) { ?>
	<li class="profile"><a href="<?php echo htmlspecialchars(url_for('profile')); ?>">View your Reports</a></li>
<?php } else { ?>
	<li class="signup"><a href="<?php echo htmlspecialchars(url_for('signup')); ?>">Signup for Free</a></li>
<?php } ?>
	<li><a href="<?php echo htmlspecialchars(url_for('features')); ?>">Features</a></li>
</ul>
</div>
</div>

<hr>

<div class="home-block" id="home_block_features">
<h2>Features</h2>

<div class="feature feature-left" id="home_feature_addresses">
	<a href="<?php echo htmlspecialchars(url_for('screenshots#screenshots_litecoin')); ?>" title="Screenshots on adding cryptocurrency addresses"><div class="splash"></div></a>
	<h3>Track addresses</h3>

	<p>
		Cryptocurrency addresses can be supplied manually or by exporting CSV files from your wallet software, and balances downloaded through public explorer APIs.
	</p>

	<p>
		<a href="<?php echo htmlspecialchars(url_for('kb', array('q' => 'cryptocurrencies'))); ?>">What are cryptocurrencies?</a>
	</p>
</div>

<div class="feature feature-right" id="home_feature_accounts">
	<a href="<?php echo htmlspecialchars(url_for('screenshots#screenshots_accounts')); ?>" title="Screenshots on adding accounts"><div class="splash"></div></a>
	<h3>Track exchanges and miners</h3>

	<p>
		Exchanges, mining pools and security exchanges can be tracked through read-only APIs. The performance of mining hardware through mining pools is also tracked.
	</p>

	<p>
		<a href="<?php echo htmlspecialchars(url_for('kb', array('q' => 'notifications'))); ?>">Configure e-mail notifications</a> <span class="new">new</span>
	</p>
</div>

<div class="feature feature-left" id="home_feature_reports">
	<a href="<?php echo htmlspecialchars(url_for('screenshots#screenshots_profile_addgraph')); ?>" title="View screenshots on personalising your reports"><div class="splash"></div></a>
	<h3>View Reports</h3>

	<p>
		<?php echo htmlspecialchars(get_site_config('site_name')); ?> can automatically personalise reports for you, or you can take full control and design your own report pages with custom graphs, layouts and technical indicators.
	</p>
</div>

<div class="feature feature-right" id="home_feature_secure">
	<a href="http://openclerk.org" target="_blank"><div class="splash"></div></a>
	<h3>Safe and secure</h3>

	<p>
		All data and addresses are read-only, the <a href="http://openclerk.org" target="_blank">underlying software</a> is open source, and no passwords are stored. This means your accounts and funds will always be safe.
	</p>
</div>

<div class="screenshots_group">
<ul class="screenshots">
<?php if (user_logged_in()) { ?>
	<li class="profile"><a href="<?php echo htmlspecialchars(url_for('profile')); ?>">View your Reports</a></li>
<?php } else { ?>
	<li class="signup"><a href="<?php echo htmlspecialchars(url_for('signup')); ?>">Signup for Free</a></li>
<?php } ?>
	<li><a href="<?php echo htmlspecialchars(url_for('screenshots')); ?>">Screenshots</a></li>
	<li><a href="<?php echo htmlspecialchars(url_for('features')); ?>">All Features...</a></li>
</ul>
</div>
</div>

<hr>

<div class="home-block" id="home_block_support">
<h2>Supported Addresses and Accounts</h2>

<?php require(__DIR__ . "/_supported.php"); ?>
</div>

<div class="banner">
	<a href="<?php echo htmlspecialchars(url_for('screenshots')); ?>" title="Screenshots"><img src="<?php echo htmlspecialchars(url_for('img/screenshots/banner2_small.png')); ?>" alt="Example reports and graphs"></a>
</div>
