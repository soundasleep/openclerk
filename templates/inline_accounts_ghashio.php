<?php
$account_data = array('exchange_name' => get_exchange_name('ghashio'));
?>

<div class="instructions_add">
<h2>Adding a <?php echo $account_data['exchange_name']; ?> account</h2>

<ol class="steps">
  <li>Log into your <?php echo get_exchange_name('cexio'); ?> account
    and visit your <a href="https://cex.io/trade/profile">Profile page</a>.<br>
    <img src="<?php echo htmlspecialchars(url_for('img/accounts/cexio1.png')); ?>"></li>

  <li>In the <i>API Access</i> section, generate a new key with only the <i>GHash.IO Hash Rate</i> and <i>GHash.IO Workers</i> permissions.<br>
    <img src="<?php echo htmlspecialchars(url_for('img/accounts/ghashio2.png')); ?>"></li>

  <li>Copy and paste your <i>Key</i> and <i>Secret</i>, along with your <?php echo get_exchange_name('cexio'); ?> username, into the <a class="wizard_link" href="<?php echo htmlspecialchars(url_for('wizard_accounts_pools')); ?>">"Add new Exchange" form</a>, and click "Add account".<br>
    <img src="<?php echo htmlspecialchars(url_for('img/accounts/cexio3.png')); ?>"></li>

  <li>You must first activate the API key; once copying your <i>Key</i> and <i>Secret</i>, click on "Activate" to activate the key.<br>
    <img src="<?php echo htmlspecialchars(url_for('img/accounts/cexio4.png')); ?>"></li>

  <li>You will receive a confirmation e-mail from <?php echo get_exchange_name('cexio'); ?>; click on this link to complete the key activation.<br>
    <img src="<?php echo htmlspecialchars(url_for('img/accounts/cexio5.png')); ?>"></li>

  <li>The <i>Secret</i> for your key will now be hidden. Finally, click "Add Account" on the completed "Add new Exchange" form.<br>
    <img src="<?php echo htmlspecialchars(url_for('img/accounts/cexio6.png')); ?>"></li>

</ol>
</div>

<div class="instructions_safe">
<h2>Is it safe to provide <?php echo htmlspecialchars(get_site_config('site_name')); ?> a <?php echo $account_data['exchange_name']; ?> API key?</h2>

<ul>
  <li>You need to make sure that the API key <em>only</em> has the <i>GHash.IO Hash Rate</i> permission. This should
    mean that the API key can only be used to retrieve account status, and it should not be possible
    to perform trades or withdraw funds using that key.</li>

  <li>Your <?php echo $account_data['exchange_name']; ?> keys and secrets will <i>never</i> be displayed on the <?php echo htmlspecialchars(get_site_config('site_name')); ?>
    site, even if you have logged in.</li>

  <li>Through the <?php echo get_exchange_name('cexio'); ?> interface you can revoke an API key&apos;s access at any time by clicking <i>Delete</i>.<br>
    <img src="<?php echo htmlspecialchars(url_for('img/accounts/cexio_delete.png')); ?>"></li>

</ul>
</div>
