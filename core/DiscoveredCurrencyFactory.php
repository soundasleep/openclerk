<?php

namespace Core;

use \DiscoveredComponents\Currencies;
use \Openclerk\Currencies\Currency;
use \Openclerk\Currencies\CurrencyFactory;

class DiscoveredCurrencyFactory implements CurrencyFactory {

  /**
   * @return a {@link Currency} for the given currency code, or {@code null}
   *   if none could be found
   */
  public function loadCurrency($cur) {
    if (Currencies::hasKey($cur)) {
      return Currencies::getInstance($cur);
    }
    return null;
  }

}
