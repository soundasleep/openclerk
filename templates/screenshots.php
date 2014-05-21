<h1><?php echo t("Screenshots"); ?></h1>

<?php
	$screens = array(
		'user_currencies' => array(
			'title' => t('Currencies'),
			'url' => 'img/screenshots/wizard_currencies.png',
			'text' => t("A wide range of cryptocurrencies and fiat currencies can be selected; this ensures that you are only informed about currencies you are actually interested in."),
		),
		'accounts' => array(
			'title' => t('Account types'),
			'url' => 'img/screenshots/wizard_accounts.png',
			'text' => t("A wide variety of account types are supported, with more being added regularly. All account types are completely optional."),
			'url2' => 'img/screenshots/wizard_accounts_securities.png',
			'text2' => t("Exchange, mining pools, securities and funds accounts can only be accessed once you have enabled a read-only key on the site itself, and provided that key to :site_name. Mining hashrates for most pools can also be tracked and graphed. Helpful wizards guide you through the steps to add new accounts and addresses."),
		),
		'litecoin' => array(
			'title' => t('Addresses'),
			'url' => 'img/screenshots/wizard_addresses.png',
			'text' => t("Cryptocurrency addresses are added directly into :site_name, and balances downloaded through public explorer APIs."),
			'url2' => 'img/screenshots/litecoinqt-export.png',
			'text2' => t('Addresses can be directly exported from your local BTC or LTC wallets into :site_name.'),
		),
		'profile_summary' => array(
			'title' => t('Graphs'),
			'url' => 'img/screenshots/profile_summary.png',
			'text' => t("Once you have defined some addresses or accounts, you can construct your own personalised summary pages, displaying any information you deem relevant. Helpful reports include the value of your currencies if immediately converted into another; the distribution of your currency values; and current exchange rates."),
			'url2' => 'img/screenshots/technicals.png',
			'text2' => t('Premium users can add technical indicators to graphs, such as :example1, :example2 and :example3.', array(':example1' => t("Simple Moving Average") . " (SMA)", ':example2' => t("Bollinger Bands") . " (BOLL)", ':example3' => t("Relative Strength Index") . " (RSI)")),
			'url3' => 'img/screenshots/profile_securities.png',
			'text3' => t('Premium users can also access an automatically-generated page listing all of their securities and their current market values.'),
			'url4' => 'img/screenshots/your_currencies.png',
			'text4' => t('All users can also access a "Your Currencies" report page, displaying your most recent balances for each currency; and a "Your Hashrates" report page, displaying all of your most recent hashrates.'),
		),
		'profile_historical' => array(
			'title' => t('Historical'),
			'url' => 'img/screenshots/profile_historical.png',
			'text' => t("Historical data, both for your accounts and popular cryptocurrency exchanges, can also be included on your summary pages."),
		),
		'profile_addgraph' => array(
			'title' => t('Adding graphs'),
			'url' => 'img/screenshots/wizard_reports.png',
			'text' => t(":site_name automatically manages your reports based on your currency, address and reporting preferences."),
			'url2' => 'img/screenshots/profile_addgraph.png',
			'text2' => t("Alternatively, a simple interface lets you add new graph types, reconfigure them, and reorder them. Graphs can also be grouped together into :pages (premium users only).", array(':pages' => "<i>" . t("pages") . "</i>")),
		),

	);
?>
<div class="tabs" id="tabs_screenshots">
	<ul class="tab_list">
		<?php /* each <li> must not have any whitespace between them otherwise whitespace will appear when rendered */ ?>
		<?php foreach ($screens as $key => $data) {
			echo "<li id=\"tab_screenshots_$key\">" . htmlspecialchars($data['title']) . "</li>";
		} ?>
	</ul>

	<ul class="tab_groups">
	<?php $first_tab = true;
		foreach ($screens as $key => $data) { ?>
		<li id="tab_screenshots_<?php echo $key; ?>_tab"<?php echo $first_tab ? "" : " style=\"display:none;\""; ?>>
			<img src="<?php echo htmlspecialchars(url_for($data['url'])); ?>">
			<p><?php echo $data['text']; ?></p>
			<?php if (isset($data['url2'])) { ?>
				<img src="<?php echo htmlspecialchars(url_for($data['url2'])); ?>" class="image2">
				<p><?php echo $data['text2']; ?></p>
			<?php } ?>
			<?php if (isset($data['url3'])) { ?>
				<img src="<?php echo htmlspecialchars(url_for($data['url3'])); ?>" class="image2">
				<p><?php echo $data['text3']; ?></p>
			<?php } ?>
			<?php if (isset($data['url4'])) { ?>
				<img src="<?php echo htmlspecialchars(url_for($data['url4'])); ?>" class="image2">
				<p><?php echo $data['text4']; ?></p>
			<?php } ?>
		</li>
	<?php 	$first_tab = false;
		} ?>
	</ul>
</div>

<script type="text/javascript">
$(document).ready(function() {
	initialise_tabs('#tabs_screenshots');
});
</script>

	<div class="screenshots_group">
	<ul class="screenshots">
	<?php if (user_logged_in()) { ?>
		<li class="profile"><a href="<?php echo htmlspecialchars(url_for('profile')); ?>"><?php echo t("View your Reports"); ?></a></li>
	<?php } else { ?>
		<li class="signup"><a href="<?php echo htmlspecialchars(url_for('signup')); ?>"><?php echo t("Signup for Free"); ?></a></li>
	<?php } ?>
		<li><a href="<?php echo htmlspecialchars(url_for('help')); ?>"><?php echo t("FAQ"); ?></a></li>
	</ul>
	</div>
