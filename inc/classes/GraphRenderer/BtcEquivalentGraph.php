<?php

class GraphRenderer_BtcEquivalentGraph extends GraphRenderer_AbstractCompositionGraph {

  public function __construct() {
    parent::__construct('btc');
  }

  public function getTitle() {
    return ct("Equivalent :currency");
  }

  public function getTitleArgs() {
    return array(
      ':currency' => get_currency_abbr($this->currency),
    );
  }

  function usesSummaries() {
    return true;
  }

  public function hasSubheading() {
    return true;
  }

  /**
   * We want to have a custom subheading of the total equivalent BTC instead.
   */
  public function getCustomSubheading() {
    return $this->total;
  }

  var $total;

  /**
   * When we generate data, also keep track of the total of the last row
   */
  public function getData($days) {
    $result = parent::getData($days);
    $this->total = $result['last_row_total'];   // saved by AbstractCompositionGraph from the last row
    return $result;
  }

  function getCompositionSources($days, $extra_days) {
    $sources = array();
    $summary_currencies = get_all_summary_currencies($this->getUser());
    foreach (get_all_currencies() as $cur) {
      if (isset($summary_currencies[$cur])) {
        if ($cur == 'btc') {
          // we can't LIMIT by days here, because we may have many accounts for one exchange
          // first get summarised data
          $sources[] = array('query' => "SELECT *, '$cur' AS exchange FROM graph_data_summary WHERE user_id=:user_id AND summary_type='totalbtc'
            AND data_date > DATE_SUB(NOW(), INTERVAL " . ($days + $extra_days) . " DAY) ORDER BY data_date DESC", 'key' => 'data_date', 'balance_key' => 'balance_closing');
          // and then get more recent data
          $sources[] = array('query' => "SELECT *, '$cur' AS exchange FROM summary_instances WHERE is_daily_data=1 AND summary_type='totalbtc'
            AND user_id=:user_id AND created_at >= DATE_SUB(NOW(), INTERVAL " . ($days + $extra_days) . " DAY) ORDER BY created_at DESC", 'key' => 'created_at', 'balance_key' => 'balance');
        } else {
          // we can't LIMIT by days here, because we may have many accounts for one exchange
          // first get summarised data
          $sources[] = array('query' => "SELECT *, '$cur' AS exchange FROM graph_data_summary WHERE user_id=:user_id AND summary_type='equivalent_btc_$cur'
            AND data_date > DATE_SUB(NOW(), INTERVAL " . ($days + $extra_days) . " DAY) ORDER BY data_date DESC", 'key' => 'data_date', 'balance_key' => 'balance_closing');
          // and then get more recent data
          $sources[] = array('query' => "SELECT *, '$cur' AS exchange FROM summary_instances WHERE is_daily_data=1 AND summary_type='equivalent_btc_$cur'
            AND user_id=:user_id AND created_at >= DATE_SUB(NOW(), INTERVAL " . ($days + $extra_days) . " DAY) ORDER BY created_at DESC", 'key' => 'created_at', 'balance_key' => 'balance');
        }
      }
    }
    return $sources;
  }

  function getCompositionArgs() {
    return array(
      'user_id' => $this->getUser(),
    );
  }

  /**
   * e.g. {@link #get_exchange_name()}
   */
  function getHeadingTitle($key, $args) {
    return get_currency_abbr($key);
  }

}
