<h1><?php echo t("Reset Password"); ?></h1>

<p>
	<?php echo t("If your :site_name account has been
	secured with an e-mail/password login, you may use this form to reset the password
	on your account."); ?>

	<?php echo t("This form cannot be used to reset or change OpenID identities, which must instead be
	updated through your :user_profile.", array(':user_profile' => link_to(url_for('user#user_identities'), ht("user profile")))); ?>
</p>
