<?php
global $last_calculated;
?>

<h1><?php echo t("Coin Voting"); ?></h1>

<p>
  <?php echo t("Here you can vote for the next currencies which should be added to :site_name in a future release, if the currency satisfies the :requirements.
    Each vote is counted proportionally to the total number of votes from each user;
    votes from :premium_users are multipled by :number.
    Total currency popularity is recalculated daily (last calculated :calculated).
    :site_name will also notify you when a currency you have voted on has been added.",
  array(
    ':requirements' => link_to(url_for('help/add_currency'), t('explorer and exchange requirements')),
    ':premium_users' => link_to(url_for('premium'), t('premium users')),
    ':number' => number_format(get_site_config('premium_user_votes')),
    ':calculated' => recent_format_html($last_calculated),
  )); ?>
</p>

<?php if (!user_logged_in()) { ?>
<p>
  <?php echo t("You need to be :logged_in in order to vote.",
  array(
    ':logged_in' => link_to(url_for('login'), t('logged in')),
  )); ?>
</p>
<?php } ?>

<p>
  <?php echo t("If you would like to sponsor the implementation of a currency immediately, or to suggest a new currency, please :contact_us.",
  array(':contact_us' => link_to(url_for('contact'), t('contact us')))); ?>
</p>
