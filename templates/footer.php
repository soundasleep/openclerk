<?php
use \Pages\PageRenderer;
?>

  </div>
</div>

<?php

PageRenderer::requireTemplate("templates_footer");

echo "<div id=\"footer_nav\">";
PageRenderer::requireTemplate("footer_navigation");
PageRenderer::requireTemplate("footer_copyright");
echo "</div>";

if (!(has_required_admin() || defined('BATCH_SCRIPT'))){ ?>
<script type="text/javascript">
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

  ga('create', '<?php echo get_site_config('google_analytics_account'); ?>', 'auto');
  ga('send', 'pageview');
</script>
<?php } ?>
</body>
</html>

<?php

if (defined('PAGE_RENDER_START')) {
  $end_time = microtime(true);
  $time_diff = ($end_time - PAGE_RENDER_START) * 1000;
  echo "<!-- rendered in " . number_format($time_diff, 2) . " ms -->";
}
performance_metrics_page_end();

echo "\n<!--\n" . print_r(Openclerk\MetricsHandler::getInstance()->printResults(), true) . "\n-->";
if (is_admin()) {
  echo "\n<!-- " . print_r($_SESSION, true) . "\n-->";
}
