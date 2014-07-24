
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

<!-- more are in versions_old -->
