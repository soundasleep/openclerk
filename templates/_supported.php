<div class="tabs" id="tabs_home">
	<ul class="tab_list">
		<?php /* each <li> must not have any whitespace between them otherwise whitespace will appear when rendered */ ?>
		<li id="tab_home_exchanges"><?php echo ht("Exchanges"); ?></li><li id="tab_home_pools"><?php echo ht("Mining Pools"); ?></li><li id="tab_home_wallets"><?php echo ht("Wallets"); ?></li><li id="tab_home_securities"><?php echo ht("Securities"); ?></li><li id="tab_home_addresses"><?php echo ht("Addresses"); ?></li><li id="tab_home_updates"><?php echo ht("Updates"); ?></li>
	</ul>

	<ul class="tab_groups">
		<li id="tab_home_exchanges_tab">

<h2><?php echo ht("Supported exchanges"); ?></h2>

<?php
$exchange_data = get_exchange_pairs();
$all_currencies = get_all_currencies();
?>
<table class="supported_exchanges">
<thead>
	<tr>
		<th>Exchange</th>
		<?php foreach ($all_currencies as $p) { ?>
		<th class="currency">
			<span class="currency_name_<?php echo htmlspecialchars($p); ?>" title="<?php echo htmlspecialchars(get_currency_name($p)); ?>"><?php echo htmlspecialchars(get_currency_abbr($p)); ?></span>
			<?php if (in_array($p, get_new_supported_currencies())) echo " <span class=\"new\">" . ht("new") . "</span>"; ?>
		</th>
		<?php } ?>
	</tr>
</thead>
<tbody>
	<?php foreach ($exchange_data as $exchange => $pairs) { ?>
	<tr>
		<td>
			<?php echo htmlspecialchars(get_exchange_name($exchange)); ?>
			<?php if (in_array($exchange, get_new_exchanges())) echo " <span class=\"new\">" . ht("new") . "</span>"; ?>
		</td>
		<?php foreach ($all_currencies as $currency) {
			$exchange_support = array();
			foreach ($pairs as $p) {
				if ($p[0] == $currency || $p[1] == $currency) {
					$p[0] = get_currency_abbr($p[0]);
					$p[1] = get_currency_abbr($p[1]);
					$exchange_support[] = implode("/", $p);
				}
			}
			echo $exchange_support ? "<td class=\"yes\" title=\"" . htmlspecialchars(implode(", ", $exchange_support)) . "\">Y</td>" : "<td class=\"no\"></td>"; ?>
		<?php } ?>
	</tr>
	<?php } ?>
</tbody>
</table>

<div class="screenshots_group">
<ul class="screenshots">
	<li class="historical"><a href="<?php echo htmlspecialchars(url_for('historical')); ?>"><?php echo ht("Historical Data"); ?></a></li>
</ul>
</div>

		</li>
		<li id="tab_home_pools_tab" style="display:none;">

<h2><?php echo ht("Supported mining pools"); ?></h2>

<?php
$balances_data = get_supported_wallets_safe();

// currencies are always sorted in the same order regardless of get_supported_wallets() order
// assumes there are no supported currencies that don't have a wallet
$all_currencies = get_all_currencies();
$all_cryptocurrencies = get_all_cryptocurrencies();
$account_data_grouped = account_data_grouped();

// issue #52: calculate 'mining pools' and 'other wallets'
$all_mining_pools = array();
$all_wallets = array();
foreach ($account_data_grouped['Mining pools'] as $exchange => $data) {
	if ($data['disabled']) {
		continue;
	}
	if (isset($balances_data[$exchange])) {
		$all_mining_pools[$exchange] = $balances_data[$exchange];
	} else if (isset($data['title_key']) && $data['title_key'] && isset($balances_data[$data['title_key']])) {
		$all_mining_pools[$data['title_key']] = $balances_data[$data['title_key']];
	} else {
		$all_wallets[$exchange] = $balances_data[$exchange];
	}
}
foreach (array_merge($account_data_grouped['Exchanges'], $account_data_grouped['Securities'], $account_data_grouped['Other']) as $exchange => $data) {
	if (!isset($balances_data[$exchange])) {
		continue;
	}
	if ($data['disabled']) {
		continue;
	}
	$all_wallets[$exchange] = $balances_data[$exchange];
}
// sort alphabetically
uksort($all_wallets, 'sort_all_wallets');
function sort_all_wallets($a, $b) {
	if ($a == "generic") return 1;
	if ($b == "generic") return -1;
	return strcmp(get_exchange_name($a), get_exchange_name($b));
}
?>
<table class="supported_wallets">
<thead>
	<tr>
		<th><?php echo ht("Source"); ?></th>
		<?php foreach ($all_cryptocurrencies as $p) { ?>
		<th class="currency">
			<span class="currency_name_<?php echo htmlspecialchars($p); ?>" title="<?php echo htmlspecialchars(get_currency_name($p)); ?>"><?php echo htmlspecialchars(get_currency_abbr($p)); ?></span>
			<?php if (in_array($p, get_new_supported_currencies())) echo " <span class=\"new\">" . ht("new") . "</span>"; ?>
		</th>
		<?php } ?>
		<th><?php echo ht("Hashrate"); ?></th>
	</tr>
</thead>
<tbody>
	<?php foreach ($all_mining_pools as $exchange => $currencies) { ?>
	<tr>
		<td><?php echo htmlspecialchars(get_exchange_name($exchange)); if (in_array($exchange, get_new_supported_wallets())) echo " <span class=\"new\">" . ht("new") . "</span>"; ?></td>
		<?php foreach ($all_cryptocurrencies as $p) { ?>
		<?php echo in_array($p, $currencies) ? "<td class=\"yes\" title=\"" . htmlspecialchars(get_currency_abbr($p)) . "\">Y</td>" : "<td class=\"no\"></td>"; ?>
		<?php } ?>
		<?php echo in_array('hash', $currencies) ? "<td class=\"yes\" title=\"Hashrate\">Y</td>" : "<td class=\"no\"></td>"; ?>
	</tr>
	<?php } ?>
</tbody>
<tfoot>
	<tr>
		<th><?php echo ht("Source"); ?></th>
		<?php foreach ($all_cryptocurrencies as $p) { ?>
		<th class="currency">
			<span class="currency_name_<?php echo htmlspecialchars($p); ?>" title="<?php echo htmlspecialchars(get_currency_name($p)); ?>"><?php echo htmlspecialchars(get_currency_abbr($p)); ?></span>
			<?php if (in_array($p, get_new_supported_currencies())) echo " <span class=\"new\">" . ht("new") . "</span>"; ?>
		</th>
		<?php } ?>
		<th>Hashrate</th>
	</tr>
</tfoot>
</table>

<div class="screenshots_group">
<ul class="screenshots">
	<li class="add_service"><a href="<?php echo htmlspecialchars(url_for('kb', array('q' => 'add_service'))); ?>"><?php echo ht("Add another mining pool..."); ?></a></li>
</ul>
</div>

		</li>
		<li id="tab_home_wallets_tab" style="display:none;">

<h2><?php echo ht("Supported wallets"); ?></h2>

<table class="supported_wallets">
<thead>
	<tr>
		<th><?php echo ht("Source"); ?></th>
		<?php foreach ($all_currencies as $p) { ?>
		<th class="currency">
			<span class="currency_name_<?php echo htmlspecialchars($p); ?>" title="<?php echo htmlspecialchars(get_currency_name($p)); ?>"><?php echo htmlspecialchars(get_currency_abbr($p)); ?></span>
			<?php if (in_array($p, get_new_supported_currencies())) echo " <span class=\"new\">" . ht("new") . "</span>"; ?>
		</th>
		<?php } ?>
	</tr>
</thead>
<tbody>
	<?php foreach ($all_wallets as $exchange => $currencies) { ?>
	<tr>
		<td><?php echo htmlspecialchars(get_exchange_name($exchange)); if (in_array($exchange, get_new_supported_wallets())) echo " <span class=\"new\">" . ht("new") . "</span>"; ?></td>
		<?php foreach ($all_currencies as $p) { ?>
		<?php echo in_array($p, $currencies) ? "<td class=\"yes\" title=\"" . htmlspecialchars(get_currency_abbr($p)) . "\">Y</td>" : "<td class=\"no\"></td>"; ?>
		<?php } ?>
	</tr>
	<?php } ?>
</tbody>
<tfoot>
	<tr>
		<th><?php echo ht("Source"); ?></th>
		<?php foreach ($all_currencies as $p) { ?>
		<th class="currency">
			<span class="currency_name_<?php echo htmlspecialchars($p); ?>" title="<?php echo htmlspecialchars(get_currency_name($p)); ?>"><?php echo htmlspecialchars(get_currency_abbr($p)); ?></span>
			<?php if (in_array($p, get_new_supported_currencies())) echo " <span class=\"new\">" . ht("new"). "</span>"; ?>
		</th>
		<?php } ?>
	</tr>
</tfoot>
</table>

<div class="screenshots_group">
<ul class="screenshots">
	<li class="add_service"><a href="<?php echo htmlspecialchars(url_for('kb', array('q' => 'add_service'))); ?>"><?php echo ht("Add another wallet..."); ?></a></li>
</ul>
</div>

		</li>
		<li id="tab_home_securities_tab" style="display:none;">

<?php
$security_exchange_data_temp = get_security_exchange_pairs();

// remove any exchanges that are disabled
$security_exchange_data = array();
foreach ($security_exchange_data_temp as $exchange => $pairs) {
	foreach (account_data_grouped() as $label => $data) {
		foreach ($data as $key => $value) {
			if ($key == $exchange && !$value['disabled']) {
				$security_exchange_data[$exchange] = $pairs;
			}
		}
	}
}

// summarise
$all_security_currencies = array();
foreach ($security_exchange_data as $exchange => $pairs) {
	foreach ($pairs as $p) {
		$all_security_currencies[$p] = $p;
	}
}
?>
<table class="supported_securities">
<thead>
	<tr>
		<th><?php echo ht("Securities"); ?></th>
		<?php foreach ($all_security_currencies as $p => $pairs) { ?>
		<th><span class="currency_name_<?php echo htmlspecialchars($p); ?>"><?php echo htmlspecialchars(get_currency_abbr($p)); ?></span></th>
		<?php } ?>
	</tr>
</thead>
<tbody>
	<?php foreach ($security_exchange_data as $exchange => $pairs) { ?>
	<tr>
		<td>
			<?php echo htmlspecialchars(get_exchange_name($exchange)); ?>
			<?php if (in_array($exchange, get_new_security_exchanges())) echo " <span class=\"new\">" . ht("new") . "</span>"; ?>
		</td>
		<?php foreach ($all_security_currencies as $p) { ?>
		<?php echo in_array($p, $pairs) ? "<td class=\"yes\">Y</td>" : "<td class=\"no\"></td>"; ?>
		<?php } ?>
	</tr>
	<?php } ?>
</tbody>
</table>

<div class="screenshots_group">
<ul class="screenshots">
	<li class="historical"><a href="<?php echo htmlspecialchars(url_for('historical')); ?>"><?php echo ht("Historical Data"); ?></a></li>
	<li class="add_service"><a href="<?php echo htmlspecialchars(url_for('kb', array('q' => 'add_service'))); ?>"><?php echo ht("Add another exchange..."); ?></a></li>
</ul>
</div>

		</li>
		<li id="tab_home_addresses_tab" style="display:none;">

<h2><?php echo ht("Supported addresses"); ?></h2>

<?php
$addresses_data = get_blockchain_currencies();

// summarise
$all_currencies = array();
foreach ($addresses_data as $exchange => $currencies) {
	$all_currencies[$exchange] = array();
	foreach ($currencies as $p) {
		$all_currencies[$exchange][] = "<span class=\"currency_name_" . htmlspecialchars($p) . "\" title=\"" . htmlspecialchars(get_currency_name($p)) . "\">" . htmlspecialchars(get_currency_abbr($p)) . "</span>";
	}
}
?>
<table class="supported_addresses">
<thead>
	<tr>
		<th><?php echo ht("Explorer"); ?></th>
		<th><?php echo ht("Currencies"); ?></th>
	</tr>
</thead>
<tbody>
	<?php foreach ($addresses_data as $exchange => $currencies) { ?>
	<tr>
		<td><?php echo htmlspecialchars($exchange); ?></td>
		<td class="currency_list"><?php echo implode(", ", $all_currencies[$exchange]); ?></td>
	</tr>
	<?php } ?>
</tbody>
</table>

<div class="screenshots_group">
<ul class="screenshots">
	<li class="add_service"><a href="<?php echo htmlspecialchars(url_for('kb', array('q' => 'add_currency'))); ?>"><?php echo ht("Add another cryptocurrency..."); ?></a></li>
</ul>
</div>

		</li>
		<li id="tab_home_updates_tab" style="display:none;">

			<dl>
				<dt>12 May</dt>
				<dd>
				Added <a href="<?php echo htmlspecialchars(url_for('finance_accounts')); ?>">finance accounts</a> and <a href="<?php echo htmlspecialchars(url_for('finance_categories')); ?>">finance categories</a>;
				added support for creating manual transactions;
				added <a href="<?php echo htmlspecialchars(url_for('your_transactions')); ?>">transactions</a> export as CSV;
				added support for listing daily exchange rates with <a href="<?php echo htmlspecialchars(url_for('average')); ?>">your transactions</a>.
				</dd>

				<dt>25 April</dt>
				<dd>Added currency support for <span class="currency_name_krw" title="<?php echo htmlspecialchars(get_currency_name('krw')); ?>">KRW</span>;
				added <a href="https://www.kraken.com/">Kraken</a> exchange;
				added <a href="<?php echo htmlspecialchars(url_for('average')); ?>">market average price indices</a>, and enabled price average summary calculations;
				added initial <a href="<?php echo htmlspecialchars(url_for('your_transactions')); ?>">Your Transactions</a> interface for testing,
				and <a href="<?php echo htmlspecialchars(url_for('kb', array('q' => 'transaction_creation'))); ?>">automatic transaction creation</a>;
				added <a href="https://doge.rapidhash.net">RapidHash DOGE</a>, <a href="https://vtc.rapidhash.net">RapidHash VTC</a>
				and <a href="http://doge.cryptotroll.com/">Cryptotroll DOGE</a> mining pools;
				accounts can now be <a href="<?php echo htmlspecialchars(url_for('wizard_accounts')); ?>">manually disabled</a>;
				added more large graph sizes;
				fixed <span class="currency_name_dgc" title="<?php echo htmlspecialchars(get_currency_name('dgc')); ?>">DGC</span> and <span class="currency_name_wdc" title="<?php echo htmlspecialchars(get_currency_name('wdc')); ?>">WDC</span> addresses not updating.
				</dd>

				<dt>21 April</dt>
				<dd>Added support for <a href="<?php echo htmlspecialchars(url_for('signup', array('use_password' => 1))); ?>">password signup</a> and login;
				rewrote <a href="http://openclerk.org">Openclerk</a> to use new build tools and technologies;
				fixed <span class="currency_name_trc" title="<?php echo htmlspecialchars(get_currency_name('trc')); ?>">TRC</span> and <span class="currency_name_nvc" title="<?php echo htmlspecialchars(get_currency_name('nvc')); ?>">NVC</span> addresses not updating;
				fixed <a href="http://ypool.net/">ypool.net</a> mining pool accounts not updating.</dd>

				<dt>7 April</dt>
				<dd>Added currency support for <a href="http://www.ixcoin.co/" class="currency_name_ixc">Ixcoin</a>, <a href="https://vertcoin.org/" class="currency_name_vtc">Vertcoin</a>,
				<a href="http://netcoinfoundation.org/" class="currency_name_net">Netcoin</a>, <a href="http://hobonickels.info/" class="currency_name_hbn">Hobonickels</a>
				and <span class="currency_name_ils" title="<?php echo htmlspecialchars(get_currency_name('ils')); ?>">ILS</span>;
				added <a href="https://www.bit2c.co.il/">Bit2c</a> exchange;
				added <a href="https://www.scryptguild.com/">ScryptGuild</a> mining pool;
				enabled a number of exchange currency pairs.</dd>

				<dt>21 March</dt>
				<dd>Added currency support for <a href="http://digitalcoin.co/en/" class="currency_name_dgc">Digitalcoin</a> and <a href="http://www.worldcoinalliance.net/" class="currency_name_wdc">Worldcoin</a>;
				added <a href="https://www.cryptsy.com">Cryptsy</a> accounts;
				added <a href="http://shibepool.com/">Shibe Pool</a>, <a href="http://dgc.cryptopools.com/">CryptoPools DGC</a> and <a href="https://wdc.d2.cc/">d2 WDC</a> mining pools;
				disabled <a href="https://www.litecoinglobal.com/">Litecoin Global</a> and <a href="https://www.btct.co/">BTC Trading Co.</a> security exchanges;
				disabled <a href="http://meg.smalltimeminer.com/">Small Time Miner Megacoin</a> mining pool.</dd>

				<dt>27 February</dt>
				<dd>Released the <a href="https://play.google.com/store/apps/details?id=com.cryptfolio.calculator">Crypto Converter</a> Android app,
				based on the <a href="<?php echo htmlspecialchars(url_for('calculator')); ?>"><?php echo htmlspecialchars(get_site_config('site_name')); ?> calculator</a>.</dd>

				<dt>17 February</dt>
				<dd>Added currency support for <a href="http://megacoin.co.nz/" class="currency_name_mec">Megacoin</a>;
				added <a href="https://www.vaultofsatoshi.com">Vault of Satoshi</a> exchange;
				added <a href="https://www.miningpool.co">MiningPool.co</a>, <a href="https://teamdoge.com/">TeamDoge</a>,
				<a href="http://doge.dedicatedpool.com/">dedicatedpool.com DOGE</a>, <a href="http://meg.smalltimeminer.com/">Small Time Miner Megacoin</a>,
				<a href="https://peercoin.ecoining.com/">Ecoining Peercoin</a>, <a href="https://ftc.nut2pools.com/">Nut2Pools FTC</a> and <a href="https://50btc.com">50BTC</a> mining pools;
				added <a href="<?php echo htmlspecialchars(url_for('img/screenshots/addgraph_subcategories.png')); ?>">new subcategories interface</a> for report graphs;
				<a href="<?php echo htmlspecialchars(url_for('premium')); ?>">premium accounts</a> can now be purchased with <span class="currency_name_dog">Dogecoin</span>;
				disabled <a href="http://lite.coin-pool.com/">lite.coin-pool.com</a> mining pool.</dd>

				<dt>1 February</dt>
				<dd>Added <a href="<?php echo htmlspecialchars(url_for('calculator')); ?>">currency converter calculator tool</a> and graph;
				bid/ask is now used instead of buy/sell; Bitstamp is now the default USD/BTC exchange;
				added <a href="https://coinbase.com">Coinbase</a> exchange and accounts;
				added <a href="https://www.litecoininvest.com/">Litecoininvest</a> and <a href="https://www.btcinve.com/">BTCInve</a> securities exchange and individual securities;
				<a href="<?php echo htmlspecialchars(url_for('wizard_accounts_other')); ?>">generic APIs</a> can now have value multipliers;
				<span class="currency_name_xrp">Ripple</span> and <span class="currency_name_nmc">Namecoin</a> addresses can <a href="<?php echo htmlspecialchars(url_for('wizard_accounts_addresses')); ?>">now be tracked</a>;
				disabled <a href="https://bips.me/">BIPS</a> accounts;
				lots of bug fixes.</dd>

				<dt>10 January</dt>
				<dd>Graphs can now show <a href="<?php echo htmlspecialchars(url_for('historical', array('id' => 'bitstamp_usdbtc_daily', 'days' => 180, 'delta' => 'percent'))); ?>">deltas</a> (absolute and percent); updated <a href="<?php echo htmlspecialchars(url_for('historical')); ?>">historical data</a> interface;
				added currency support for <a href="https://ripple.com/" class="currency_name_xrp">Ripple</a> and <span class="currency_name_pln" title="<?php echo htmlspecialchars(get_currency_name('pln')); ?>">PLN</span>;
				added <a href="https://pln.bitcurex.com">Bitcurex PLN</a>, <a href="https://eur.bitcurex.com">Bitcurex EUR</a> and <a href="https://justcoin.com/">Justcoin</a> exchanges;
				added <a href="http://doge.hashfaster.com">HashFaster DOGE</a>, <a href="https://www.multipool.us/">Multipool</a>,
				<a href="http://www.wemineftc.com/">WeMineFTC</a> and <a href="http://ypool.net/">ypool.net</a> mining pools;
				reduced <a href="<?php echo htmlspecialchars(url_for('premium')); ?>">premium</a> prices.</dd>

				<dt>9 January</dt>
				<dd>Added <a href="<?php echo htmlspecialchars(url_for('kb', array('q' => 'notifications'))); ?>">e-mail notifications</a>;
				improved site performance; fixes for managed graphs; disabled <a href="https://50btc.com/">50BTC</a> mining pool.</dd>

				<dt>23 December</dt>
				<dd>Added <a href="<?php echo htmlspecialchars(url_for('kb', array('q' => 'graph_refresh'))); ?>">live graph updates</a>;
				accounts can now have <a href="<?php echo htmlspecialchars(url_for('user#user_openid')); ?>">multiple OpenID identities</a>;
				enabled <a href="<?php echo htmlspecialchars(url_for('kb', array('q' => 'bitcoin_csv'))); ?>">CSV upload</a> and multiple addresses for all cryptocurrencies;
				added currency support for <a href="http://dogecoin.com/" class="currency_name_dog">Dogecoin</a> and <span class="currency_name_gbp" title="<?php echo htmlspecialchars(get_currency_name('gbp')); ?>">GBP</span>;
				added <a href="http://pool.dogechain.info">Dogechain Pool</a>, <a href="http://dogepool.pw">dogepool.pw</a>, <a href="https://www.ejpool.info/">Elitist Jerks</a>,
				<a href="http://hashfaster.com">HashFaster</a> (LTC, FTC), <a href="http://ozco.in">Ozcoin</a> (LTC, BTC),
				<a href="http://doge.scryptpools.com">scryptpools.com</a> and <a href="https://www.triplemining.com">TripleMining</a> mining pools;
				added <a href="https://www.coins-e.com">Coins-E</a> exchange.</dd>

				<dt>4 December</dt>
				<dd>Added currency support for <a href="http://terracoin.org/" class="currency_name_trc">Terracoin</a>;
				added <a href="http://beeeeer.org"><?php echo htmlspecialchars(get_exchange_name('beeeeer')); ?></a> and <a href="https://www.litecoinpool.org">litecoinpool.org</a> mining pools;
				added <a href="<?php echo htmlspecialchars(url_for('your_hashrates')); ?>">Your Hashrates</a> report page;
				added "BTC Equivalent" (graph, stacked, proportional),
				"Currency Composition" (stacked, proportional) graph types;
				disabled <a href="https://www.bitfunder.com">BitFunder</a> securities.</dd>

				<dt>28 November</dt>
				<dd>Added currency support for <a href="http://primecoin.org/" class="currency_name_xpm">Primecoin</a>;
				added <a href="http://eligius.st">Eligius</a>, <a href="http://litepool.eu">Litepool</a>,
				<a href="https://coinhuntr.com">CoinHuntr</a> and <a href="http://lite.coin-pool.com">lite.coin-pool.com</a> mining pools.</dd>

				<dt>27 November</dt>
				<dd>Enabled <span class="currency_name_nmc" title="Namecoin">NMC</span> on <a href="https://cex.io">CEX.io</a> wallet balances;
				added currency support for <span class="currency_name_cny" title="<?php echo htmlspecialchars(get_currency_name('cny')); ?>">CNY</span>;
				added <a href="https://btcchina.com">BTC China</a> and <a href="https://www.cryptsy.com/">Cryptsy</a> exchanges;
				graphs are now loaded asynchronously;
				failing accounts will now be disabled after repeated errors;
				enabled <a href="https://cryptfolio.com">site-wide SSL</a> by default.</dd>

				<dt>20 November</dt>
				<dd>Added <a href="https://www.bitstamp.net">Bitstamp</a> wallet balances;
				added <a href="https://796.com/">796 Xchange</a> securities exchange and individual securities;
				added <a href="http://ltc.kattare.com/">ltc.kattare.com</a> mining pool;
				migrated to two new servers.</dd>

				<dt>25 October</dt>
				<dd>Added <a href="https://cex.io">CEX.io</a> and <a href="https://crypto-trade.com">Crypto-Trade</a> exchanges;
				added <a href="https://cex.io">CEX.io</a> and <a href="https://crypto-trade.com">Crypto-Trade</a> wallet balances;
				added <a href="https://crypto-trade.com">Crypto-Trade</a> securities exchange and individual securities;
				added commodity currency <span class="currency_name_ghs">GHS</span>;
				added <a href="<?php echo htmlspecialchars(url_for('wizard_accounts')); ?>">account testing</a> functionality;
				redesigned home page; added <a href="<?php echo htmlspecialchars(url_for('features')); ?>">features</a> and <a href="<?php echo htmlspecialchars(url_for('contact')); ?>">contact us</a> page.</dd>

				<dt>14 October</dt>
				<dd>Added currency support for <a href="http://www.novacoin.org/" class="currency_name_nvc">Novacoin</a>;
				added <a href="https://nvc.khore.org">nvc.khore.org</a> mining pool;
				added support for <a href="<?php echo htmlspecialchars(url_for('wizard_accounts_individual_securities')); ?>">individual securities</a>;
				added Your Currencies tables as graphs;
				added <a href="<?php echo htmlspecialchars(url_for('screenshots#screenshots_profile_summary')); ?>">Your Currencies</a> report page.</dd>

				<dt>26 September</dt>
				<dd>Added <a href="https://www.bitfunder.com">BitFunder</a> securities;
				added new <a href="<?php echo htmlspecialchars(url_for('wizard_currencies')); ?>">currencies</a>, <a href="<?php echo htmlspecialchars(url_for('wizard_accounts')); ?>">accounts</a>
				and <a href="<?php echo htmlspecialchars(url_for('wizard_reports')); ?>">report preferences</a> wizards; redesigned signup and login forms;
				added lots of <a href="<?php echo htmlspecialchars(url_for('help')); ?>">Help Centre</a> content;
				updated <a href="<?php echo htmlspecialchars(url_for('terms')); ?>">Terms and Conditions of Use</a> and <a href="<?php echo htmlspecialchars(url_for('terms#privacy')); ?>">Privacy Policy</a>;
				updated <a href="<?php echo htmlspecialchars(url_for('screenshots')); ?>">screenshots</a>.</dd>

				<dt>25 September</dt>
				<dd>Like us on <a href="https://facebook.com/cryptfolio" class="facebook" target="_blank">Facebook</a>!</dd>

				<dt>19 September</dt>
				<dd>Added <a href="https://www.give-me-coins.com">Give Me Coins</a> mining pool;
				removed <a href="https://www.give-me-ltc.com">Give Me LTC</a> and <a href="https://www.mine-litecoin.com/">Mine-Litecoin</a> mining pools.</dd>

				<dt>8 August</dt>
				<dd>Added currency support for <span class="currency_name_cad" title="<?php echo htmlspecialchars(get_currency_name('cad')); ?>">CAD</span>;
				added <a href="https://www.cavirtex.com/">VirtEx</a> and <a href="https://www.bitstamp.net">Bitstamp</a> exchanges;
				added <a href="https://www.bitminter.com">BitMinter</a>, <a href="https://www.liteguardian.com/">LiteGuardian</a>
				and <a href="https://www.mine-litecoin.com/">Mine-Litecoin</a> mining pools;
				added fiat currency exchange rates from <a href="http://themoneyconverter.com">TheMoneyConverter</a>;
				currency composition graphs will now include blockchain and offset values;
				securities will now display wallet and securities values separately.</dd>

				<dt>25 July</dt>
				<dd>Added currency support for <a href="http://www.ppcoin.org/" class="currency_name_ppc">PPCoin</a>;
				added <a href="http://ftc.mining-foreman.org">Mining Foreman</a> (FTC) mining pool;
				added automatically generated <a href="<?php echo htmlspecialchars(url_for('profile', array('securities' => 1))); ?>">your securities</a> reports;
				report graphs now link to historical data where possible;
				added "Heading" graph type.</dd>

				<dt>11 July</dt>
				<dd>Added technical indicator <abbr title="Exponential moving average">EMA</abbr>;
				added <a href="https://www.havelockinvestments.com">Havelock Investments</a> securities;
				added <a href="http://www.mining-foreman.org">Mining Foreman</a> (LTC) mining pool;
				added <a href="<?php echo htmlspecialchars(url_for('external')); ?>">external API graphs</a> and historical balances graphs;
				inactive free accounts are now <a href="<?php echo htmlspecialchars(url_for('premium')); ?>">disabled</a> after <?php echo plural("day", get_site_config('user_expiry_days')); ?>.</dd>

				<dt>6 June</dt>
				<dd>Added currency support for <span class="currency_name_aud" title="<?php echo htmlspecialchars(get_currency_name('aud')); ?>">AUD</span>;
				<a href="<?php echo htmlspecialchars(url_for('wizard_accounts_addresses')); ?>">BTC and LTC addresses</a>
				can now be imported from clients;
				mining hashrates can now be tracked and graphed;
				added security value graphs for all security exchanges;
				added <a href="<?php echo htmlspecialchars(url_for('historical', array('id' => 'securities_litecoinglobal_ltc', 'days' => '180', 'name' => 'LTC-GLOBAL'))); ?>">historical security value graphs</a>;
				added <a href="https://hypernova.pw/">Hypernova</a> and <a href="http://ltcmine.ru/">LTCMine.ru</a> mining pools;
				updated screenshots.</dd>

				<dt>23 May</dt>
				<dd>Added currency support for <span class="currency_name_eur" title="<?php echo htmlspecialchars(get_currency_name('eur')); ?>">EUR</span>;
				addresses can now have titles; titles can be edited inline; address lists can be sorted.</dd>

				<dt>22 May</dt>
				<dd>Graphs can now be edited inline; added <a href="<?php echo htmlspecialchars(url_for('premium')); ?>">technical indicators</a>
				<abbr title="Simple moving average">SMA</abbr>, <abbr title="Relative strength index">RSI</abbr> and <abbr title="Bollinger bands">BOLL</abbr>.</dd>

				<dt>11 May</dt>
				<dd>Added public <a href="<?php echo htmlspecialchars(url_for('historical')); ?>">historical data</a>;
				graphs now have configurable date ranges;
				added <a href="https://bips.me">BIPS</a> wallet balances;
				added <a href="https://www.btcguild.com">BTC Guild</a> and <a href="https://www.50btc.com">50BTC</a> mining pools.</dd>

				<dt>4 May</dt>
				<dd>Added currency support for <a href="http://www.feathercoin.com/" class="currency_name_ftc">Feathercoin</a>;
				added <a href="http://www.cryptostocks.com">Cryptostocks</a> securities;
				added <a href="https://mining.bitcoin.cz/">Slush&apos;s pool</a>,
				<a href="http://www.give-me-ltc.com">Give Me LTC</a> and <a href="http://www.wemineltc.com">WeMineLTC</a> mining pools.</dd>

				<dt>1 May</dt>
				<dd>We&apos;re now live!</dd>

				<dt>28 April</dt>
				<dd>First private release deployed.</dd>

				<dt>24 April</dt>
				<dd>Project started.</dd>

				<dt>&nbsp;</dt>
				<dd>Visit the <a href="<?php echo htmlspecialchars(url_for(get_site_config('forum_link'))); ?>" target="_blank">discussion forum</a>,
				or subscribe to <a href="https://twitter.com/cryptfolio" target="_blank" class="twitter">@cryptfolio</a>
				or <span class="help_name_groups"><a href="http://groups.google.com/group/<?php echo htmlspecialchars(get_site_config('google_groups_announce')); ?>" target="_blank">cryptfolio-announce</a></span>, for updates.
				</dd>

			</dl>

		</li>
	</ul>
</div>
<script type="text/javascript">
$(document).ready(function() {
	initialise_tabs('#tabs_home');
});
</script>
