  <ul class="footer_nav_list">
    <li><span class="title"><?php echo htmlspecialchars(get_site_config('site_name')); ?></span>
      <ul>
        <li><a href="<?php echo htmlspecialchars(url_for('index')); ?>"><?php echo ht("About"); ?></a></li>
        <li><a href="<?php echo htmlspecialchars(url_for('premium')); ?>"><?php echo ht("Get Premium"); ?></a></li>
        <li><a href="<?php echo htmlspecialchars(get_site_config('version_history_link')); ?>"><?php echo ht("Release History"); ?></a></li>
        <li><a href="http://openclerk.org" target="_blank">Openclerk.org</a></li>
      </ul>
    </li>
    <li><span class="title"><?php echo ht("Your Account"); ?></span>
      <ul>
        <?php if (user_logged_in()) { ?>
        <li><a href="<?php echo htmlspecialchars(url_for('user')); ?>"><?php echo ht("User Profile"); ?></a></li>
        <li><a href="<?php echo htmlspecialchars(url_for('wizard_currencies')); ?>"><?php echo ht("Currency Preferences"); ?></a></li>
        <li><a href="<?php echo htmlspecialchars(url_for('wizard_accounts')); ?>"><?php echo ht("Configure Accounts"); ?></a></li>
        <li><a href="<?php echo htmlspecialchars(url_for('profile')); ?>"><?php echo ht("Your Reports"); ?></a></li>
        <?php } else { ?>
        <li><a href="<?php echo htmlspecialchars(url_for('signup')); ?>"><?php echo ht("Signup"); ?></a></li>
        <li><a href="<?php echo htmlspecialchars(url_for('login')); ?>"><?php echo ht("Login"); ?></a></li>
        <?php } ?>
      </ul>
    </li>
    <li><span class="title"><?php echo ht("Tools"); ?></span>
      <ul>
        <li><a href="<?php echo htmlspecialchars(url_for('historical')); ?>"><?php echo ht("Historical Data"); ?></a></li>
        <li><a href="<?php echo htmlspecialchars(url_for('average')); ?>"><?php echo ht("Market Averages"); ?></a></li>
        <li><a href="<?php echo htmlspecialchars(url_for('your_transactions')); ?>"><?php echo ht(":site_name Finance"); ?></a></li>
        <li><a href="<?php echo htmlspecialchars(url_for('calculator')); ?>"><?php echo ht("Calculator"); ?></a></li>
      </ul>
    </li>
    <li><span class="title"><?php echo ht("Support"); ?></span>
      <ul>
        <li><a href="<?php echo htmlspecialchars(url_for('help')); ?>"><?php echo ht("Help Centre"); ?></a></li>
        <li><a href="<?php echo htmlspecialchars(get_site_config('blog_link')); ?>" target="_blank"><?php echo ht("Blog"); ?></a> <span class="new"><?php echo ht("new"); ?></span></li>
        <li><a href="<?php echo htmlspecialchars(url_for('contact')); ?>"><?php echo ht("Contact Us"); ?></a></li>
        <li><a href="<?php echo htmlspecialchars(url_for('external')); ?>"><?php echo ht("External API Status"); ?></a></li>
      </ul>
    </li>
  </ul>

