
<div id="navigation">
<ul>
  <li class="home"><a href="<?php echo url_for('index'); ?>" title="<?php echo htmlspecialchars(get_site_config('site_name')); ?>"><span class="text"><?php echo htmlspecialchars(get_site_config('site_name')); ?></span></a></li>
  <?php if (user_logged_in()) { ?>
    <li class="profile"><a href="<?php echo url_for('profile'); ?>" title="<?php echo ht("Your Reports"); ?>"><span class="text"><?php echo ht("Your Reports"); ?></span><span class="responsive-text"><?php echo ht("Reports"); ?></span></a></li>
    <li class="finance"><a href="<?php echo url_for('your_transactions'); ?>" title="<?php echo ht("Finance"); ?>"><span class="text"><?php echo ht("Finance"); ?></span></a></li>
    <li class="accounts"><a href="<?php echo url_for('wizard_currencies'); ?>" title="<?php echo ht("Configure Accounts"); ?>"><span class="text"><?php echo ht("Configure Accounts"); ?></span><span class="responsive-text"><?php echo ht("Configure"); ?></span></a></li>
    <li class="user"><a href="<?php echo url_for('user'); ?>" title="<?php echo ht("User Profile"); ?>"><span class="text"><?php echo ht("User Profile"); ?></span></a></li>
    <li class="logout"><a href="<?php echo url_for('login', array('logout' => 1)); ?>" title="<?php echo ht("Logout"); ?>"><span class="text"><?php echo ht("Logout"); ?></span></a></li>
    <?php if (is_admin()) { ?>
      <li class="admin"><a href="<?php echo url_for('admin'); ?>" title="<?php echo ht("Admin"); ?>"><span class="text"><?php echo ht("Admin"); ?></span></a></li>
    <?php } ?>
  <?php } else { ?>
    <li class="signup"><a href="<?php echo url_for('signup'); ?>" title="<?php echo ht("Signup"); ?>"><span class="text"><?php echo ht("Signup"); ?></span></a></li>
    <li class="login"><a href="<?php echo url_for('login'); ?>" title="<?php echo ht("Login"); ?>"><span class="text"><?php echo ht("Login"); ?></span></a></li>
  <?php } ?>
  <li class="premium"><a href="<?php echo url_for('premium'); ?>" title="<?php echo ht("Premium"); ?>"><span class="text"><?php echo ht("Premium"); ?></span></a></li>
  <li class="help"><a href="<?php echo url_for('help'); ?>" title="<?php echo ht("Help"); ?>"><span class="text"><?php echo ht("Help"); ?></span></a></li>
</ul>
</div>