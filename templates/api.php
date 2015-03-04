<h1><?php echo ht(":site_name API"); ?></h1>

<p>
  <?php echo t(":site_name provides the following public API methods, for private or research purposes only."); ?>
  <?php echo t("Please do not request an API more than once every :time seconds, or your IP address may be temporarily blocked.", array(":time" => 5)); ?>
  <?php echo t("If you have higher usage or commercial requirements, please :contact.", array(":contact" => link_to("contact", t("contact us")))); ?>
</p>

<ul>
<?php
$apis = new \Core\Apis\ApiList();
foreach ($apis->getJSON(array()) as $api) {
  $api['endpoint_link'] = preg_replace("/\[[^\]]+\]/i", "", $api['endpoint']);

  ?>
  <li>
    <h2><?php echo link_to(absolute_url($api['endpoint_link']), $api['endpoint']); ?></h2>

    <?php echo htmlspecialchars($api['title']); ?><br>
    <?php echo htmlspecialchars($api['description']); ?><br>

    <?php if ($api['params']) { ?>
      <ul>
      <?php foreach ($api['params'] as $key => $value) {
        echo "<li><b>" . htmlspecialchars($key) . "</b> - " . htmlspecialchars($value);
      } ?></ul>
    <?php } ?>
  </li>
  <?php

}
?>
</ul>
