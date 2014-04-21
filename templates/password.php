<h1>Reset Password</h1>

<p>
	If your <?php echo htmlspecialchars(get_site_config('site_name')); ?> account has been
	secured with an e-mail/password login, you may use this form to reset the password
	on your account.

	This form cannot be used to reset or change OpenID identities, which must instead be
	updated through your <a href="<?php echo htmlspecialchars(url_for('user#user_identities')); ?>">user profile</a>.
</p>
