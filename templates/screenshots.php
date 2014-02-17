<h1>Screenshots</h1>

<?php
	$screens = array(
		'user_currencies' => array(
			'title' => 'Currencies',
			'url' => 'img/screenshots/wizard_currencies.png',
			'text' => "A wide range of cryptocurrencies and fiat currencies can be selected; this ensures that you are only informed about currencies you are actually interested in.",
		),
		'accounts' => array(
			'title' => 'Account types',
			'url' => 'img/screenshots/wizard_accounts.png',
			'text' => "A wide variety of account types are supported, with more being added regularly. All account types are completely optional.",
			'url2' => 'img/screenshots/wizard_accounts_securities.png',
			'text2' => "Exchange, mining pools, securities and funds accounts can only be accessed once you have enabled a read-only key on the site itself, and provided that key to " . htmlspecialchars(get_site_config('site_name')) . ". Mining hashrates for most pools can also be tracked and graphed. Helpful wizards guide you through the steps to add new accounts and addresses.",
		),
		'litecoin' => array(
			'title' => 'Addresses',
			'url' => 'img/screenshots/wizard_addresses.png',
			'text' => "Cryptocurrency addresses are added directly into " . htmlspecialchars(get_site_config('site_name')) . ", and balances downloaded through public explorer APIs.",
			'url2' => 'img/screenshots/litecoinqt-export.png',
			'text2' => 'Addresses can be directly exported from your local BTC or LTC wallets into '. htmlspecialchars(get_site_config('site_name')) . '.',
		),
		'profile_summary' => array(
			'title' => 'Graphs',
			'url' => 'img/screenshots/profile_summary.png',
			'text' => "Once you have defined some addresses or accounts, you can construct your own personalised summary pages, displaying any information you deem relevant. Helpful reports include the value of your currencies if immediately converted into another; the distribution of your currency values; and current exchange rates.",
			'url2' => 'img/screenshots/technicals.png',
			'text2' => 'Premium users can add technical indicators to graphs, such as Simple Moving Average (SMA), Bollinger Bands (BOLL) and Relative Strength Index (RSI).',
			'url3' => 'img/screenshots/profile_securities.png',
			'text3' => 'Premium users can also access an automatically-generated page listing all of their securities and their current market values.',
			'url4' => 'img/screenshots/your_currencies.png',
			'text4' => 'All users can also access a "Your Currencies" report page, displaying your most recent balances for each currency;<br> and a "Your Hashrates" report page, displaying all of your most recent hashrates.',
		),
		'profile_historical' => array(
			'title' => 'Historical',
			'url' => 'img/screenshots/profile_historical.png',
			'text' => "Historical data, both for your accounts and popular cryptocurrency exchanges, can also be included on your summary pages.",
		),
		'profile_addgraph' => array(
			'title' => 'Adding graphs',
			'url' => 'img/screenshots/wizard_reports.png',
			'text' => htmlspecialchars(get_site_config('site_name')) . " automatically manages your reports based on your currency, address and reporting preferences.",
			'url2' => 'img/screenshots/profile_addgraph.png',
			'text2' => "Alternatively, a simple interface lets you add new graph types, reconfigure them, and reorder them. Graphs can also be grouped together into <i>pages</i> (premium users only).",
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

