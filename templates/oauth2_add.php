<h1><?php echo t("Add OAuth2 Identity"); ?></h1>

<!-- login instructions go here -->

<p><?php echo t("You can also see a list of :identities.", array(':identities' => link_to(url_for('user#user_openid'), ht("your current OAuth2 identities")))); ?></p>
