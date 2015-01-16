

<?php if (did_autologin()) { ?>
<div id="autologin">
  <?php echo t("Automatically logged in. Hi, :user!", array(':user' => "<a href=\"" . url_for('user') . "\" class=\"disabled\">"
    . ($_SESSION["user_name"] ? htmlspecialchars($_SESSION["user_name"]) : "<i>" . t("anonymous") . "</i>") . "</a>")); ?>
  (<a href="<?php echo url_for('login', array('logout' => 1)); ?>"><?php echo ht("This isn't me."); ?></a>)
</div>
<?php } ?>