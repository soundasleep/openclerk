<?php

class GraphRenderer_StatisticsQueue extends GraphRenderer_AbstractTicker {

  public function __construct() {
    parent::__construct();
  }

  public function getTitle() {
    return ct("Job queue delay (hours)");
  }

  /**
   * @return an array of columns e.g. (type, title, args)
   */
  function getTickerColumns() {
    $columns = array();
    $columns[] = array('type' => 'number', 'title' => ct("Free delay"));
    $columns[] = array('type' => 'number', 'title' => ct("Premium delay"));
    return $columns;
  }

  /**
   * The sources must return 'created_at' column as well, for last_updated
   * @return an array of queries e.g. (query, key = created_at/data_date)
   */
  function getTickerSources($days, $extra_days) {
    return array(
      array('query' => "SELECT * FROM site_statistics
        ORDER BY created_at DESC LIMIT " . ($days + $extra_days), 'key' => 'created_at'),
    );
  }

  /**
   * @return an array of arguments passed to each {@link #getTickerSources()}
   */
  function getTickerArgs() {
    return array();
  }

  /**
   * @return an array of 1..2 values of the values for the particular row,
   *      maybe formatted with {@link #graph_number_format()}.
   */
  function getTickerData($row) {
    return array(
      graph_number_format($row['free_delay_minutes'] / 60),
      graph_number_format($row['premium_delay_minutes'] / 60),
    );
  }

}

