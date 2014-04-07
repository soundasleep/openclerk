<h2>Overview</h2>

<p>
	We want to add as many exchanges, pools and services as we can to
	<a href="http://openclerk.org" target="_blank">Openclerk</a> (the underlying open source project). The requirements
	for supporting a new service are:
</p>

<p>
	<ul>
		<li>The service needs to be stable, and accept free public registrations.</li>
		<li>The service needs to have a public API to obtain balances per currency.</li>
		<li>This public API <i>must</i> be read-only;
			it must <i>not</i> be possible for someone with a read-only API key to perform an exchange or trade.</li>
	</ul>
</p>

<div class="tip" style="margin-bottom: 15px;">
Exchanges/pools/services that do <em>not</em> have a suitable read-only API yet at the time of writing:

<div class="columns2">
<div class="column">
<ul>
	<li>BIPS (wallet API was removed)</li>
	<li>Bitcoin.de</li>
	<li>Bitfinex</li>
	<li>Bitcoin China</li>
	<li>BTER</li>
	<li>CampBX (read-only API <a href="https://bitcointalk.org/index.php?topic=192423.msg2868088#msg2868088">"coming in September"</a>)</li>
	<li>CoinJar (<a href="https://support.coinjar.com/discussions/suggestions/271-read-only-api-access">requested</a>)</li>
	<li>Coinotron (on development list)</li>
	<li>CoinEX</li>
	<li>Coin.Mx</li>
</ul>
</div>
<div class="column">
<ul>
	<li>Coins-E</li>
	<li>fast-pool.com</li>
	<li>Flexcoin (<a href="https://bitcointalk.org/index.php?topic=57732.msg2022077#msg2022077">no API yet</a>)</li>
	<li>Intersango (registration is closed)</li>
	<li>NetcodePool (all currencies)</li>
	<li>Safello</li>
	<li>VirtEx (<a href="https://www.cavirtex.com/faq#tradingapi">wallet balances API coming</a>)</li>
	<li>Bit2c (only available through an <a href="http://code.google.com/p/openclerk/wiki/Unsafe">unsafe instance</a>)</li>
</ul>
</div>
</div>

<div style="clear:both;">If you would like one of these services to add a safe read-only API, let them know!</div>
</div>

<h2>Example: Existing mining pool software</h2>

<p>
	<img src="<?php echo htmlspecialchars(url_for('img/accounts/poolx.png')); ?>" class="help_inline help_inline_double">
	<img src="<?php echo htmlspecialchars(url_for('img/accounts/hashfaster_doge2.png')); ?>" class="help_inline help_inline_double">

	Mining pools that are run on existing mining pool software, such as
	<a href="https://github.com/TheSerapher/php-mpos">MPOS</a> and <a href="https://github.com/Greedi/mmcFE">mmcFE</a>,
	often automatically provide users with read-only API keys for wallets and balances.
	These pools can easily be added to Openclerk.
</p>

<p>
	There are many supported pools that follow this approach. For example, <a href="http://pool-x.eu">Pool-x.eu</a>
	and <a href="http://hashfaster.com">HashFaster</a>
	both provide a read-only API key for each account (illustrated).
</p>

<h2>Example: A read-only balance API key</h2>

<p>
	<img src="<?php echo htmlspecialchars(url_for('img/accounts/litecoinglobal2.png')); ?>" class="help_inline">

	This approach is an easy way to add read-only API keys to a service that either has no API, or
	already supports a full trade API. In this approach, a <em>different</em> API key is generated for
	each account, and this API key is explicitly read-only.
</p>

<p>
	This means the service does not need to support revoking keys, managing multiple keys, managing key permissions etc.
	However, this does mean that once an API key is shared, it cannot be revoked.
</p>

<p>
	For example, <a href="http://litecoinglobal.com">Litecoin Global</a> provided a read-only API key for each account (illustrated).
</p>

<h2>Example: Permissions-based API keys</h2>

<p>
	<img src="<?php echo htmlspecialchars(url_for('img/accounts/havelock3.png')); ?>" class="help_inline">

	This approach involves much more work but is the most powerful approach. In this approach, a service
	supports multiple API keys, with each API key given different permissions - including a read-only
	"Get Balance" permission. API keys can also be revoked at any time, making this the most secure approach as well.
</p>

<p>
	For example, <a href="https://www.havelockinvestments.com">Havelock Investments</a>
	allows multiple API keys to be created, each with different permissions. (illustrated).
</p>

<h2>Requesting a new service</h2>

<p>
	If you would like Openclerk to support a new mining pool, exchange or service, please let us know through one of the following methods:
</p>

<?php require_template('inline_contact'); ?>

<p>
	If you would like to increase the priority of adding your preferred exchange, pool or service to
	Openclerk, you might want to consider <a href="<?php echo htmlspecialchars(url_for('help')); ?>">sponsoring the task</a>
	or supporting <?php echo htmlspecialchars(get_site_config('site_name')); ?> by
	<a href="<?php echo htmlspecialchars(url_for('premium')); ?>">purchasing a premium account</a>.
</p>
