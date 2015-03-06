<?php

class GraphRenderer_SummaryGraph extends GraphRenderer_AbstractTicker {

  var $summary_type;
  var $currency;

  public function __construct($summary_type, $currency) {
    parent::__construct();
    $this->summary_type = $summary_type;
    $this->currency = $currency;
  }

  public function requiresUser() {
    return true;
  }

  function usesSummaries() {
    return true;
  }

  public function getTitle() {
    return ct("Total :currency");
  }

  public function getTitleArgs() {
    return array(
      ':currency' => get_currency_abbr($this->currency),
    );
  }

  public function getData($days) {
    $result = parent::getData($days);
    if (!$result['data']) {
      throw new NoDataGraphException_AddCurrencies();
    }
    return $result;
  }

  /**
   * @return an array of columns e.g. (type, title, args)
   */
  function getTickerColumns() {
    $columns = array();
    $columns[] = array('type' => 'number', 'title' => get_currency_abbr($this->currency));
    return $columns;
  }

  /**
   * The sources must return 'created_at' column as well, for last_updated
   * @return an array of queries e.g. (query, key = created_at/data_date)
   */
  function getTickerSources($days, $extra_days) {
    return array(
      // first get summarised data
      array('query' => "SELECT * FROM graph_data_summary WHERE user_id=:user_id AND summary_type=:summary_type AND
        data_date >= DATE_SUB(NOW(), INTERVAL " . ($days + $extra_days) . " DAY) ORDER BY data_date DESC", 'key' => 'data_date'),
      // and then get more recent data
      array('query' => "SELECT * FROM summary_instances WHERE is_daily_data=1 AND summary_type=:summary_type AND
        user_id=:user_id ORDER BY created_at DESC LIMIT " . ($days + $extra_days), 'key' => 'created_at'),
    );
  }

  /**
   * @return an array of arguments passed to each {@link #getTickerSources()}
   */
  function getTickerArgs() {
    return array(
      'user_id' => $this->getUser(),
      'summary_type' => $this->summary_type,
    );
  }

  /**
   * @return an array of 1..2 values of the values for the particular row,
   *      maybe formatted with {@link #graph_number_format()}.
   */
  function getTickerData($row) {
    if (isset($row['balance_closing'])) {
      return array(
        graph_number_format(demo_scale($row['balance_closing'])),
      );
    } else {
      return array(
        graph_number_format(demo_scale($row['balance'])),
      );
    }
  }

}
