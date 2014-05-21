<?php
global $title;
global $kb_inline;
?>

<h1><?php echo $title; ?></h1>

<p><a href="<?php echo htmlspecialchars(url_for("help")); ?>"><?php echo ht("< Back to Help"); ?></a></p>

<div class="kb_text">
<?php require_template($kb_inline); ?>
</div>
