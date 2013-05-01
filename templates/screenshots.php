<h1>Screenshots</h1>

<?php
	$screens = array(
		'user_currencies' => array(
			'title' => 'Currencies',
			'url' => 'img/screenshots/user_currencies.png',
			'text' => "A wide range of currencies can be selected; this ensures that you are only informed about currencies you are actually interested in.",
		),
		'accounts' => array(
			'title' => 'Account types',
			'url' => 'img/screenshots/accounts.png',
			'text' => "A wide variety of account types are supported, with more being added regularly. All account types are completely optional.",
		),
		'litecoin' => array(
			'title' => 'Addresses',
			'url' => 'img/screenshots/litecoin.png',
			'text' => "Cryptocurrency addresses are added directly into " . htmlspecialchars(get_site_config('site_name')) . ", and balances downloaded through public explorer APIs.",
		),
		'litecoinglobal' => array(
			'title' => 'Exchanges and pools',
			'url' => 'img/screenshots/litecoinglobal.png',
			'text' => "Exchange, mining pools, securities and funds accounts can only be accessed once you have enabled a read-only key on the site itself, and provided that key to " . htmlspecialchars(get_site_config('site_name')) . ".",
		),
		'profile_summary' => array(
			'title' => 'Graphs',
			'url' => 'img/screenshots/profile_summary.png',
			'text' => "Once you have defined some addresses or accounts, you can construct your own personalised summary pages, displaying any information you deem relevant. Helpful reports include the value of your currencies if immediately converted into another; the distribution of your currency values; and current exchange rates.",
		),
		'profile_historical' => array(
			'title' => 'Historical',
			'url' => 'img/screenshots/profile_historical.png',
			'text' => "Historical data, both for your accounts and popular cryptocurrency exchanges, can also be included on your summary pages.",
		),
		'profile_addgraph' => array(
			'title' => 'Adding graphs',
			'url' => 'img/screenshots/profile_addgraph.png',
			'text' => "A simple interface lets you add new graph types, reconfigure them, and reorder them. Graphs can also be grouped together into <i>pages</i> (premium users only).",
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
	<?php foreach ($screens as $key => $data) { ?>
		<li id="tab_screenshots_<?php echo $key; ?>_tab">
			<img src="<?php echo htmlspecialchars($data['url']); ?>">
			<p><?php echo $data['text']; ?></p>
		</li>
	<?php } ?>
	</ul>
</div>

<script type="text/javascript">
$(document).ready(function() {
	initialise_tabs('#tabs_screenshots');
});
</script>

