<h1><?php echo ht("Help Centre"); ?></h1>

<?php
$knowledge = get_knowledge_base();

foreach ($knowledge as $label => $a) {
	echo "<h4>" . htmlspecialchars($label) . "</h4>\n\n";
	echo "<ul class=\"help_list\">";
	if ($label == 'Concepts') {
		echo "<li><a href=\"" . htmlspecialchars(url_for('features')) . "\">" . ht(":site_name Features") . "</a></li>\n";
		echo "<li><a href=\"" . htmlspecialchars(url_for('screenshots')) . "\">" . ht(":site_name Screenshots") . "</a></li>\n";
	}
	foreach ($a as $key => $kb) {
		$title = $kb;
		if (is_array($kb)) {
			// inline help
			$title = $kb['title'];
		}
		echo "<li><a href=\"" . htmlspecialchars(url_for('kb', array('q' => $key))) . "\">" . htmlspecialchars($title) . "</a>" . ((is_array($kb) && isset($kb['new']) && $kb['new']) ? " <span class=\"new\">" . ht("new") . "</span>" : "") . "</li>\n";
	}
	echo "</ul>";
}
?>

<h4><?php echo ht("Legal"); ?></h4>

<ul class="help_list">
	<li><a href="<?php echo htmlspecialchars(url_for('terms')); ?>"><?php echo ht("Terms and Conditions of Use"); ?></a></li>
	<li><a href="<?php echo htmlspecialchars(url_for('terms#privacy')); ?>"><?php echo ht("Privacy Policy"); ?></a></li>
</ul>

<hr>

<h2><?php echo ht("Frequently Asked Questions"); ?></h2>

<div class="expand_all">
<label><input type="checkbox" id="expand_all"> <?php echo ht("Expand all answers"); ?></input></label>
</div>

<dl class="help_list">
	<dt>What are the risks of providing data to <?php echo htmlspecialchars(get_site_config('site_name')); ?>?</dt>
	<dd>
		Security is taken very seriously. This site has been designed to be as secure as possible:

		<p>
		<ul>
			<li>No passwords are stored on this site - all authentication is performed via <a href="http://openid.net/get-an-openid/">OpenID</a>,
			meaning you (or your ID provider) is responsible for authentication security.</li>

			<li>No funds are stored on this site either - all premium account payments are paid to wallets hosted in a different datacentre.</li>

			<li>The underlying code base is also <a href="http://openclerk.org" target="_blank">open sourced</a> to help <a href="http://en.wikipedia.org/wiki/Open-source_software_security">reduce the likelihood</a> of security vunerabilities.</li>

			<li>The only real risk of using <?php echo htmlspecialchars(get_site_config('site_name')); ?> is in the case of a major security breach,
			where a third party is able to reduce your anonymity by seeing what accounts and addresses you hold.
			Not supplying your name or e-mail address - which are both optional at signup - can reduce the impact of this scenario.</li>

			<li>You can further reduce the impact of such a breach by inserting in false addresses, or only providing cold wallet addresses (for example) -
			<?php echo htmlspecialchars(get_site_config('site_name')); ?> does not care if you actually control the addresses you provide.</li>
		</ul>
		</p>

		That said, perfect security is impossible. If you feel uncomfortable with providing a third party site a list
		of your addresses, you might not wish to use <?php echo htmlspecialchars(get_site_config('site_name')); ?>.
		(You could also deploy your <a href="http://openclerk.org" target="_blank">own local copy</a>.)
	</dd>

	<dt>Can anyone ever access my funds?</dt>
	<dd>
		Because the only data that you provide to <?php echo htmlspecialchars(get_site_config('site_name')); ?> are public
		addresses, and read-only APIs, it should not be possible for anyone to perform any trade or transaction with
		any of your currencies - as long as your account providers maintain their security as well.
	</dd>

	<dt>One of my accounts is not being processed.</dt>
	<dd>
		An <a href="<?php echo htmlspecialchars(url_for('wizard_accounts')); ?>">account or address</a> is displayed in red if <?php echo htmlspecialchars(get_site_config('site_name')); ?>
		cannot request a particular API or address, if the API key does not have the necessary permissions, or if the remote service is <a href="<?php echo htmlspecialchars(url_for('external')); ?>">currently down</a>. Make sure that you
		have entered in your API details correctly.
		<br>
		<br>
		If you are sure you have entered in the correct details, <a href="mailto:<?php echo htmlspecialchars(get_site_config('site_email')); ?>">send us an e-mail</a>
		and we will try to help you find the problem.
	</dd>

	<?php /* issue #22: so that I remember the difference between bid/ask and sell/buy */ ?>
	<dt>What does bid and ask mean? <span class="new"><?php echo ht("new"); ?></span></dt>
	<dd>
		The <em>bid price</em> is the highest price that a buyer is willing to pay;
		the <em>ask price</em> is the lowest price that a seller is willing to accept.
		The bid price is never higher than the ask price.
		<br>
		<br>
		The bid price is also known as the <em>sell price</em>; and the ask price known as the <em>buy price</em>.
	</dd>

	<dt>I found a bug!</dt>
	<dd>
		This site is still in beta, and we would love to hear <a href="mailto:<?php echo htmlspecialchars(get_site_config('site_email')); ?>">your feedback</a>. Contact details are provided at the bottom of every page.
	</dd>

	<dt>Why does my Bitcoin/Litecoin address balance seem to be a few blocks behind?</dt>
	<dd>
		Currently all cryptocurrency addresses are only evaluated against transactions that have six or more confirmations.
		This will be added as a user option soon.
	</dd>

	<dt>Can I access my report data with APIs?</dt>
	<dd>Of course; APIs are one of the first features that will be implemented after the public beta release.</dd>

	<dt>Where does <?php echo htmlspecialchars(get_site_config('site_name')); ?> obtain fiat currency exchange rates from?</dt>
	<dd>
		<a href="http://themoneyconverter.com" target="_blank"><img src="<?php echo htmlspecialchars(url_for('img/themoneyconverter.png')); ?>" class="float_right"></a>
		<?php echo htmlspecialchars(get_site_config('site_name')); ?> currently obtains fiat currency exchange rates (for example, USD/EUR) from the
		fantastic service provided by <a href="http://themoneyconverter.com/RSSFeeds.aspx">TheMoneyConverter</a>.
		<br>
		<br>
		Note that these exchange rates are not guaranteed for accuracy or reliability. If you would like to add accurate exchange data from
		a different service such as <a href="http://www.xe.com/" target="_blank">XE.com</a> or <a href="http://josscrowcroft.github.io/open-exchange-rates/" target="_blank">Open Exchange Rates</a>, please <a href="<?php echo htmlspecialchars(url_add('mailto:' . get_site_config('site_email'), array('subject' => 'Custom Exchange Rates'))); ?>">contact us</a> for pricing.
	</dd>

	<dt>Can I sponsor/add a bounty on a bug/issue/feature?</dt>
	<dd>
		Absolutely. You can sponsor/bounty a bug, issue or feature request by purchasing a premium account, and then e-mailing us
		with the details of the task (or tasks) that you would like to sponsor. Sponsored tasks are our number one priorities.
		<br>
		<br>
		In the future there will be a way to sponsor tasks automatically, and sponsor activity will be made public (unless otherwise requested).
	</dd>

	<dt>Why do free accounts have limits?</dt>
	<dd>It takes significant computing power and network capacity to keep track of all of the currencies, exchanges, accounts and services for many users;
		regularly compile them into reports; and display these reports as requested in a timely manner. However, the limits of both free and
		premium accounts will be increased over time as resources permit. (Statistics will be
		provided soon once the beta is up and running.)
		<br>
		<br>If you would like
		access to a wider range of currencies, addresses and accounts - and you would like to have your reports updated
		more frequently - please consider upgrading to a <a href="<?php echo htmlspecialchars(url_for('premium')); ?>">premium account</a>.
	</dd>

	<dt>What does "Address has too many transactions" mean?</dt>
	<dd>
		Address balances retrieved through <span class="ltc_address"><a href="http://explorer.litecoin.net" class="inspectx" target="_blank">Litecoin Explorer</a></span>
		and <span class="ftc_address"><a href="http://cryptocoinexplorer.com:5750" class="inspectx" target="_blank">Feathercoin Search</a></span>, both based off Abe,
		cannot display address balances if the address has too many transactions. To solve this problem we need to run our own custom blockchain APIs on a separate server -
		please consider donating or purchasing a premium account.
	</dd>

	<dt>Why must free accounts log in every X days?</dt>
	<dd>
		Free users must log into <?php echo htmlspecialchars(get_site_config('site_name')); ?> every
		<?php echo plural("day", get_site_config('user_expiry_days')); ?> in order to keep their account active. This means that system
		resources dedicated to free accounts can be optimised for only those users that are active.<br>
		<br>
		When a free account is disabled, historical account data and graphs will not be removed, but existing accounts and addresses will
		no longer be updated automatically, and summaries will no longer be calculated.
		As soon as you have logged back in, your accounts and addresses will once again be updated
		as normal.<br>
		<br>
		<a href="<?php echo htmlspecialchars(url_for('premium')); ?>">Premium users</a> do not have any activity requirements.
	</dd>

	<dt>Is this site accessible through .bit?</dt>
	<dd>
		Yes - you can access this site using <a href="http://dot-bit.org/How_To_Browse_Bit_Domains" target="_blank" class="currency_name_nmc">Namecoin</a>
		by visiting <a href="http://cryptfolio.bit">http://cryptfolio.bit</a> (or <a href="http://openclerk.bit">http://openclerk.bit</a> for the open source project).
	</dd>

	<dt>More help...</dt>
	<dd>
		For additional support, contact us via:

		<?php require_template('inline_contact'); ?>
	</dd>

</dl>
