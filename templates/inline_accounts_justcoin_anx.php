<?php
$account_data = array('exchange_name' => get_exchange_name('justcoin_anx'));
?>

<div class="instructions_add">
<h2>Adding a <?php echo $account_data['exchange_name']; ?> account</h2>

<ol class="steps">
  <li>Log into your <a href="https://justcoin.com/#settings/"><?php echo $account_data['exchange_name']; ?> account</a>
    and visit your Settings.<br>
    <img src="<?php echo htmlspecialchars(url_for('img/accounts/justcoin_anx1.png')); ?>"></li>

  <li>Under the "API" tab, click "Create" to create a new API key.<br>
    <img src="<?php echo htmlspecialchars(url_for('img/accounts/justcoin_anx2.png')); ?>"></li>

  <li>Click "Reset Secret" to reset and display the secret for this API key.<br>
    <img src="<?php echo htmlspecialchars(url_for('img/accounts/justcoin_anx3.png')); ?>"></li>

  <li>Copy and paste the <i>API Key</i> and <i>API Secret</i> into the <a class="wizard_link" href="<?php echo htmlspecialchars(url_for('wizard_accounts_exchanges')); ?>">"Add new Exchange" form</a>, and click "Add account".<br>
    <img src="<?php echo htmlspecialchars(url_for('img/accounts/justcoin_anx4.png')); ?>"></li>
</ol>
</div>

<div class="instructions_safe">
<h2>Is it safe to provide <?php echo htmlspecialchars(get_site_config('site_name')); ?> a <?php echo $account_data['exchange_name']; ?> API key?</h2>

<ul>
  <li>You need to make sure that the API key <em>does not</em> have the <i>Move Funds</i> or <i>Place and Manage Orders</i> permissions. This should
    mean that the API key can only be used to retrieve account status, and it should not be possible
    to perform trades or withdraw funds using that key.</li>

  <li>Your <?php echo $account_data['exchange_name']; ?> keys and secrets will <i>never</i> be displayed on the <?php echo htmlspecialchars(get_site_config('site_name')); ?>
    site, even if you have logged in.</li>

  <li>Through the <?php echo $account_data['exchange_name']; ?> interface you can deactivate or delete an API key at any time by clicking <i>Deactivate</i> or <i>Delete</i>.<br>
    <img src="<?php echo htmlspecialchars(url_for('img/accounts/justcoin_anxdelete.png')); ?>"></li>

</ul>
</div>
