<?php
global $user;
?>

<?php if (strtotime($user['created_at']) >= strtotime("-1 hour") || require_get("welcome", false)) { ?>
<div class="success">
<ul>
	<li><?php echo t("Welcome to :site_name!"); ?></li>
	<li><?php echo t("To get started, first select the currencies that you are interested in below, and then follow the wizards to configure your addresses, accounts and reports."); ?></li>
	<li><?php echo t("If you have any problems in getting set up, please send us :email or tweet :twitter.",
			array(
				':email' => link_to(url_add('mailto:' . get_site_config('site_email'), array('subject' => 'Problems Signing Up')), t("an e-mail")),
				':twitter' => '<a class="twitter" href="https://twitter.com/cryptfolio">@cryptfolio</a>',
			)); ?></li>
</ul>
</div>

<div class="message">
 <ul>
  <li><b>Want to try CryptFolio Premium for free?</b></li>
  <li>As a new user of CryptFolio, we would love to hear your thoughts
  and feedback by filling out <a href="https://goo.gl/forms/gjLv2XpsM5BaLchz2" target="_blank">a short survey</a>.</li>
  <li>To say thank you for your time, you'll automatically receive one month of <a href="https://cryptfolio.com/premium">CryptFolio Premium</a>*.</li>
 </ul>
</div>
<?php } ?>

<div class="wizard-steps">
	<h2><?php echo t("Preferences Wizard"); ?></h2>
	<ul>
		<li class="current"><a href="<?php echo htmlspecialchars(url_for('wizard_currencies')); ?>"><?php echo t("Currencies"); ?></a></li>
		<li class=""><a href="<?php echo htmlspecialchars(url_for('wizard_accounts')); ?>"><?php echo t("Accounts"); ?></a></li>
		<li class=""><a href="<?php echo htmlspecialchars(url_for('wizard_reports')); ?>"><?php echo t("Reports"); ?></a></li>
		<li class=""><a href="<?php echo htmlspecialchars(url_for('profile')); ?>"><?php echo t("Your Reports"); ?></a></li>
	</ul>
</div>

<div class="wizard-content">
<h1><?php echo t("Currency Preferences"); ?></h1>

<p>
	<?php echo t('Welcome to :site_name!
	To begin tracking your investments and addresses, please first select the
	currencies that you are interested in. (You can always change these options
	later, by selecting the "Configure Accounts" link above.)'); ?>
</p>

<!--<p class="tip tip_float your_account_limits">-->
<p>
<?php
echo ht("As a :user, you may have up to :accounts defined.",
	array(
		':user' => $user['is_premium'] ? ht("premium user") : ht("free user"),
		':accounts' => plural("currency and exchange selection", get_premium_value($user, 'summaries')),
	));
echo "\n";
if (!$user['is_premium']) {
	echo t("To increase this limit, please purchase a :premium_account.", array(':premium_account' => link_to(url_for('premium'), ht("premium account"))));
}
?>
</p>
