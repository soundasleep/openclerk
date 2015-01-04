<h2>Overview</h2>

<p>
  <a href="http://en.wikipedia.org/wiki/Cryptocurrency" target="_blank">Cryptocurrencies</a> are
  electronic currencies that are supported by a peer-to-peer network and have no central issuing authority.
  They provide cheap and quick transactions and have properties of both a currency and an investment.
</p>

<p>
  These currencies can either be purchased through exchanges such as <a href="https://coinbase.com">Coinbase</a> and
  <a href="http://btc-e.com">BTC-e</a>, or can be generated independently using computing resources such as
  your CPU, your video card, or specialised ASIC hardware (called <i>mining</i>).
</p>

<p>
  <?php echo htmlspecialchars(get_site_config('site_name')); ?> currently supports the following cryptocurrencies:
</p>

<p>
  <ul class="currency_list">
    <?php
    // get_all_cryptocurrencies() so that it's sorted
    foreach (get_all_cryptocurrencies() as $code) {
      if (!\DiscoveredComponents\Currencies::hasKey($code)) {
        continue;
      }

      $currency = \DiscoveredComponents\Currencies::getInstance($code);

      echo "<li>";
      echo "<span class=\"currency_name_" . $currency->getCode() . "\">" . link_to($currency->getURL(), $currency->getName(), array("target" => "_blank")) . "</span>";
      foreach ($currency->getCommunityLinks() as $url => $title) {
        echo " - " . link_to($url, $title, array("target" => "_blank"));
      }
      echo "</li>";
    }
    ?>
    <li><span class="currency_name_xrp"><a href="https://ripple.com/" target="_blank">Ripple</a> - <a href="https://ripple.com/about-ripple/" target="_blank">What is Ripple?</a></span></li>
  </ul>
</p>

<p>
  Support for additional cryptocurrencies will be <a href="<?php echo htmlspecialchars(url_for('help/add_currency')); ?>">added in the future</a>,
  and you can <a href="<?php echo htmlspecialchars(url_for('vote_coins')); ?>">vote on currencies to be added</a> with your <?php echo get_site_config('site_name'); ?> account <span class="new">new</span>.
</p>
