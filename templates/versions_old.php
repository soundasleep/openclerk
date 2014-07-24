
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
