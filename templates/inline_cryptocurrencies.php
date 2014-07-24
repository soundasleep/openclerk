<h2>Overview</h2>

<p>
	<a href="http://en.wikipedia.org/wiki/Cryptocurrency" target="_blank">Cryptocurrencies</a> are
	electronic currencies that are supported by a peer-to-peer network and have no central issuing authority.
	They provide cheap and quick transactions and have properties of both a currency and an investment.
</p>

<p>
	These currencies can either be purchased through exchanges such as <a href="http://mtgox.com">Mt.Gox</a> and
	<a href="http://btc-e.com">BTC-e</a>, or can be generated independently using computing resources such as
	your CPU, your video card, or specialised ASIC hardware (called <i>mining</i>).
</p>

<p>
	<?php echo htmlspecialchars(get_site_config('site_name')); ?> currently supports the following cryptocurrencies:
</p>

<p>
	<ul class="currency_list">
		<li><span class="currency_name_btc"><a href="http://bitcoin.org" target="_blank">Bitcoin</a> - <a href="https://www.weusecoins.com/en/" target="_blank">What is Bitcoin?</a></span></li>
		<li><span class="currency_name_ltc"><a href="http://litecoin.org" target="_blank">Litecoin</a> - <a href="https://en.bitcoin.it/wiki/Litecoin" target="_blank">What is Litecoin?</a></span></li>
		<li><span class="currency_name_nmc"><a href="http://dot-bit.org" target="_blank">Namecoin</a> - <a href="http://dot-bit.org/Namecoin" target="_blank">What is Namecoin?</a></span></li>
		<li><span class="currency_name_ftc"><a href="http://feathercoin.com/" target="_blank">Feathercoin</a> - <a href="http://www.feathercoin.com/about/" target="_blank">What is Feathercoin?</a></span></li>
		<li><span class="currency_name_ppc"><a href="http://ppcoin.org/" target="_blank">PPCoin</a> - <a href="https://github.com/ppcoin/ppcoin/wiki/FAQ" target="_blank">PPCoin FAQ</a></span></li>
		<li><span class="currency_name_nvc"><a href="http://novacoin.org/" target="_blank">Novacoin</a> - <a href="http://novacoin.org/wiki/" target="_blank">Novacoin Wiki</a></span></li>
		<li><span class="currency_name_xpm"><a href="http://primecoin.org/" target="_blank">Primecoin</a> - <a href="https://github.com/primecoin/primecoin/wiki" target="_blank">Primecoin Wiki</a></span></li>
		<li><span class="currency_name_trc"><a href="http://terracoin.org/" target="_blank">Terracoin</a> - <a href="http://terracoin.org/about.html" target="_blank">About Terracoin</a></span></li>
		<li><span class="currency_name_dog"><a href="http://dogecoin.com/" target="_blank">Dogecoin</a> - <a href="https://dogecoin.org/" target="_blank">Dogecoin community</a></span></li>
		<li><span class="currency_name_mec"><a href="http://megacoin.co.nz/" target="_blank">Megacoin</a> - <a href="http://megacoin.in/about" target="_blank">About Megacoin</a></span></li>
		<li><span class="currency_name_xrp"><a href="https://ripple.com/" target="_blank">Ripple</a> - <a href="https://ripple.com/about-ripple/" target="_blank">What is Ripple?</a></span></li>
		<li><span class="currency_name_dgc"><a href="http://digitalcoin.co/en/" target="_blank">Digitalcoin</a> - <a href="http://digitalcoin.co/quick-start/" target="_blank">Digitalcoin Quick Start</a></span></li>
		<li><span class="currency_name_wdc"><a href="http://www.worldcoinalliance.net/" target="_blank">Worldcoin</a> - <a href="http://www.worldcoinalliance.net/worldcoin-features-specifications-advantages/" target="_blank">Why Worldcoin?</a></span></li>
		<li><span class="currency_name_ixc"><a href="http://www.ixcoin.co/" target="_blank">Ixcoin</a> - <a href="http://www.ixcoin.co/?page_id=18" target="_blank">Ixcoin Frequently Asked Questions</a></span></li>
		<li><span class="currency_name_vtc"><a href="http://www.vertcoin.org/" target="_blank">Vertcoin</a> - <a href="http://vertcoinforum.com/" target="_blank">Vertcoin Forum</a></span></li>
		<li><span class="currency_name_net"><a href="http://netcoinfoundation.org/" target="_blank">Netcoin</a> - <a href="http://forum.netcoinfoundation.org/" target="_blank">Netcoin Forum</a></span></li>
		<li><span class="currency_name_hbn"><a href="http://hobonickels.info/" target="_blank">Hobonickels</a> - <a href="http://www.reddit.com/r/hobonickel" target="_blank">/r/hobonickel</a></span></li>
		<li><span class="currency_name_bc1"><a href="http://www.blackcoin.co/" target="_blank">Blackcoin</a> - <a href="http://www.blackcoin.co/" target="_blank">What is Blackcoin?</a></span></li>
	</ul>
</p>

<p>
	Support for additional cryptocurrencies will be <a href="<?php echo htmlspecialchars(url_for('kb', array('q' => 'add_currency'))); ?>">added in the future</a>,
	and you can <a href="<?php echo htmlspecialchars(url_for('vote_coins')); ?>">vote on currencies to be added</a> with your <?php echo get_site_config('site_name'); ?> account <span class="new">new</span>.
</p>
