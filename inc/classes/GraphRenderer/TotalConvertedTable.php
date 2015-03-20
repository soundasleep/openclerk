<?php

/**
 *
 */
class GraphRenderer_TotalConvertedTable extends GraphRenderer {

  public function __construct() {
    parent::__construct();
  }

  public function requiresUser() {
    return true;
  }

  public function getTitle() {
    return ct("Converted fiat");
  }

  function usesSummaries() {
    return true;
  }

  public function canHaveTechnicals() {
    // do not try to calculate technicals; this also resorts the data by first key
    return false;
  }

  public function getChartType() {
    return "vertical";
  }

  function usesDays() {
    return false;
  }

  public function hasSubheading() {
    // do not try to calculate subheadings!
    return false;
  }

  public function getData($days) {

    $key_column = array('type' => 'string', 'title' => ct("Currency"));
    $columns = array();

    $columns[] = array('type' => 'string', 'title' => ct("Exchange"), 'heading' => true);
    $columns[] = array('type' => 'string', 'title' => ct("Converted fiat"));

    // a table of each all2fiat value
    // get all balances
    $currencies = get_total_conversion_summary_types($this->getUser());
    $last_updated = false;

    // create data
    $data = array();
    foreach ($currencies as $key => $c) {
      $q = db()->prepare("SELECT * FROM summary_instances WHERE user_id=? AND summary_type=? AND is_recent=1");
      $q->execute(array($this->getUser(), "all2".$key));
      if ($balance = $q->fetch()) {
        $data[] = array(
          $c['short_title'],
          currency_format($c['currency'], demo_scale($balance['balance']), 4)
        );
        $last_updated = max($last_updated, strtotime($balance['created_at']));
      }
    }

    return array(
      'key' => $key_column,
      'columns' => $columns,
      'data' => $data,
      'last_updated' => $last_updated,

      // display 'add more currencies' text
      'add_more_currencies' => true,

      // do not render a header
      'no_header' => true,
    );

  }

}
