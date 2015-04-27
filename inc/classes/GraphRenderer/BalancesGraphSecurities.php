<?php

class GraphRenderer_BalancesGraphSecurities extends GraphRenderer_AbstractTicker {

  var $exchange;
  var $security_id;

  public function __construct($exchange, $security_id) {
    parent::__construct();
    $this->exchange = $exchange;
    $this->security_id = $security_id;
  }

  public function requiresUser() {
    return false;
  }

  function usesSummaries() {
    return false;
  }

  public function getTitle() {
    return ct(":exchange");
  }

  var $_security = null;

  /**
   * Get the `security_exchange_securities` instance for this security.
   * May be cached.
   */
  public function getSecurity() {
    if ($this->_security === null) {
      $q = db()->prepare("SELECT * FROM security_exchange_securities WHERE exchange=? AND id=?");
      $q->execute(array($this->exchange, $this->security_id));
      $security = $q->fetch();
      if (!$security) {
        throw new GraphException("Could not find security '" . $this->security_id . "' for exchange '" . $this->exchange . "'");
      }
      $this->_security = $security;
    }
    return $this->_security;
  }

  public function getTitleArgs() {
    $security = $this->getSecurity();

    return array(
      ":exchange" => $security['security'],
    );
  }

  /**
   * @return an array of columns e.g. (type, title, args)
   */
  function getTickerColumns() {
    $security = $this->getSecurity();

    $columns = array();
    $columns[] = array('type' => 'number', 'title' => get_currency_abbr($security['currency']));
    return $columns;
  }

  /**
   * The sources must return 'created_at' column as well, for last_updated
   * @return an array of queries e.g. (query, key = created_at/data_date)
   */
  function getTickerSources($days, $extra_days) {
    return array(
      // TODO first get summarised data
      // and then get more recent data
      array('query' => "SELECT * FROM security_ticker WHERE is_daily_data=1 AND exchange=:exchange AND security=:security
        ORDER BY created_at_day DESC LIMIT " . ($days + $extra_days), 'key' => 'created_at'),
    );
  }

  /**
   * @return an array of arguments passed to each {@link #getTickerSources()}
   */
  function getTickerArgs() {
    $security = $this->getSecurity();

    return array(
      'exchange' => $this->exchange,
      'security' => $security['security'],
    );
  }

  /**
   * @return an array of 1..2 values of the values for the particular row,
   *      maybe formatted with {@link #graph_number_format()}.
   */
  function getTickerData($row) {
    return array(
      graph_number_format(demo_scale($row['last_trade'])),
    );
  }

}
