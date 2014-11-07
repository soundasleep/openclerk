
<dt>7 November</dt>
<dd>
	Added currency support for <a href="https://www.reddcoin.com/" class="currency_name_rdd">Reddcoin</a>
	and <a href="http://viacoin.org/" class="currency_name_via">Viacoin</a>;

	added <a href="https://www.nicehash.com/">NiceHash</a>,
	<a href="https://westhash.com/">WestHash</a>,
	<a href="https://hash-to-coins.com/">Hash-to-coins</a> and
	<a href="https://www.eobot.com/">Eobot</a> mining pools;

	added <a href="https://btclevels.com/">BTClevels</a> accounts;

	disabled <a href="https://www.scryptguild.com/">ScryptGuild</a>,
	<a href="http://ltcmine.ru/">LTCMine.ru</a>,
	<a href="http://beeeeer.org/"><?php echo htmlspecialchars(get_exchange_name('beeeeer')); ?></a> and
	<a href="https://doge.rapidhash.net/">RapidHash</a> mining pools;

	fixed <span class="currency_name_xpm">XPM</span> addresses not updating;

	updated example graph images.
</dd>

<dt>10 September</dt>
<dd>
	Added currency support for <a href="https://www.darkcoin.io/" class="currency_name_drk">Darkcoin</a>,
	<a href="http://www.vericoin.info/" class="currency_name_vrc">Vericoin</a>,
	<a href="http://nxt.org/" class="currency_name_nxt">Nxt</a>,
	<span class="currency_name_dkk" title="<?php echo htmlspecialchars(get_currency_name('dkk')); ?>">DKK</span>
	and <span class="currency_name_inr" title="<?php echo htmlspecialchars(get_currency_name('inr')); ?>">INR</span>;

	rewrote graphing framework;

	added simple currency pair graphs for all exchange pairs;

	graphs can now be <a href="<?php echo htmlspecialchars(url_for('kb', array('q' => 'graph_refresh'))); ?>">refreshed manually</a>;

	disabled <a href="http://pool.dogechain.info">Dogechain Pool</a>,
	<a href="https://hypernova.pw/">hypernova.pw</a>,
	<a href="http://dogepool.pw">dogepool.pw</a> and
	<a href="http://shibepool.com/">Shibe Pool</a> mining pools;

	notifications can now be <a href="<?php echo htmlspecialchars(url_for('wizard_notifications')); ?>">individually disabled</a>;

	automatic transactions have been disabled, but can be re-enabled through <a href="<?php echo htmlspecialchars(url_for('wizard_accounts')); ?>">your wizards</a>;

	you can now add <a href="<?php echo htmlspecialchars(url_for('wizard_accounts_offsets')); ?>">multiple named offsets</a> for each currency;

	fixed <span class="currency_name_dog">DOGE</span> addresses not updating;

	updated supported currencies from <a href="https://www.crypto-trade.com">Crypto-Trade</a>, <a href="https://cex.io">CEX.io</a> and other exchanges.
</dd>

<dt>24 July</dt>
<dd>
	Added currency support for <a href="http://www.blackcoin.co/" class="currency_name_bc1">Blackcoin</a>;
	added <a href="https://bittrex.com/">Bittrex</a> exchange;
	you can now <a href="<?php echo htmlspecialchars(url_for('vote_coins')); ?>">vote for new currencies</a> to be added;
	changed <span class="currency_name_nmc">NMC</span> addresses to use <a href="http://namecha.in/">Namecha.in</a>;
	changed <span class="currency_name_nvc">NVC</span> addresses to use <a href="https://explorer.novaco.in/">Novacoin explorer</a>;
	<span class="currency_name_xrp">Ripple</span> addresses will now also fetch non-XRP balances;
	improved notifications for <span class="currency_name_doge">DOGE</span> balances;
	disabled <a href="http://mining-foreman.org">Mining Foreman</a> mining pool;
	fixed <span class="currency_name_ixc">IXC</span> addresses not updating;
	updated icons in <a href="https://play.google.com/store/apps/details?id=com.cryptfolio.calculator">Crypto Converter</a> Android app.
</dd>

<dt>25 June</dt>
<dd>
Added currency support for <span class="currency_name_sgd" title="<?php echo htmlspecialchars(get_currency_name('sgd')); ?>">SGD</span>;
added <a href="https://www.itbit.com/">itBit</a> exchange ticker;
changed <span class="currency_name_ppc">PPC</span> and <span class="currency_name_dgc">DGC</span> addresses to use <a href="http://blockr.io/">Blockr.io</a>;
changed <span class="currency_name_wdc">WDC</span> addresses to use <a href="http://www.worldcoinexplorer.com/">Worldcoin Explorer</a>;
site design is now slightly more responsive;
user accounts can now be <a href="<?php echo htmlspecialchars(url_for('user#user_delete')); ?>">deleted</a>;
created the <a href="http://blog.cryptfolio.com">CryptFolio blog</a>.
</dd>

<dt>28 May</dt>
<dd>
Added <a href="https://anxpro.com/">ANXPRO</a>, <a href="https://www.bitmarket.pl/">BitMarket.pl</a>, <a href="https://www.poloniex.com/">Poloniex</a> exchanges;
updated <a href="https://cex.io/">CEX.io</a> supported currencies;
added <a href="https://mupool.com/">MuPool</a> mining pool.
</dd>

<dt>21 May</dt>
<dd>
Added initial site translations into German, French, Japanese, Russian and Chinese (Simplified) languages;
enabled <a href="https://code.google.com/p/openclerk/wiki/HelpTranslate">contributions for improving these translations</a>.
</dd>

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

<!-- more are in versions_old -->
