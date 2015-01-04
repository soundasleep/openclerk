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
    <li><span class="currency_name_ppc"><a href="http://ppcoin.org/" target="_blank">PPCoin</a> - <a href="https://github.com/ppcoin/ppcoin/wiki/FAQ" target="_blank">PPCoin FAQ</a></span></li>
    <li><span class="currency_name_xpm"><a href="http://primecoin.org/" target="_blank">Primecoin</a> - <a href="https://github.com/primecoin/primecoin/wiki" target="_blank">Primecoin Wiki</a></span></li>
    <li><span class="currency_name_trc"><a href="http://terracoin.org/" target="_blank">Terracoin</a> - <a href="http://terracoin.org/about.html" target="_blank">About Terracoin</a></span></li>
    <li><span class="currency_name_xrp"><a href="https://ripple.com/" target="_blank">Ripple</a> - <a href="https://ripple.com/about-ripple/" target="_blank">What is Ripple?</a></span></li>
    <li><span class="currency_name_wdc"><a href="http://www.worldcoinalliance.net/" target="_blank">Worldcoin</a> - <a href="http://www.worldcoinalliance.net/worldcoin-features-specifications-advantages/" target="_blank">Why Worldcoin?</a></span></li>
    <li><span class="currency_name_vtc"><a href="http://www.vertcoin.org/" target="_blank">Vertcoin</a> - <a href="http://vertcoinforum.com/" target="_blank">Vertcoin Forum</a></span></li>
    <li><span class="currency_name_rdd"><a href="https://www.reddcoin.com/" target="_blank">Reddcoin</a> - <a href="http://www.reddit.com/r/reddcoin" target="_blank">/r/reddcoin</a></span></li>
    <li><span class="currency_name_via"><a href="http://viacoin.org/" target="_blank">Viacoin</a> - <a href="http://www.reddit.com/r/viacoin" target="_blank">/r/viacoin</a></span></li>
  </ul>
</p>

<p>
  Support for additional cryptocurrencies will be <a href="<?php echo htmlspecialchars(url_for('help/add_currency')); ?>">added in the future</a>,
  and you can <a href="<?php echo htmlspecialchars(url_for('vote_coins')); ?>">vote on currencies to be added</a> with your <?php echo get_site_config('site_name'); ?> account <span class="new">new</span>.
</p>
