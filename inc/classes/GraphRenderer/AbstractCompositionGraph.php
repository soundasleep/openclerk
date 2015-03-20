<?php

abstract class GraphRenderer_AbstractCompositionGraph extends GraphRenderer {

  var $currency;

  public function __construct($currency) {
    parent::__construct();
    $this->currency = $currency;
  }

  public function requiresUser() {
    return true;
  }

  function usesSummaries() {
    return true;
  }

  /**
   * @return true if data should be limited to days, or false if it can have any resolution.
   *      defaults to true
   */
  public function isDaily() {
    return true;
  }

  public function hasSubheading() {
    // do not try to calculate subheadings!
    return false;
  }

  abstract function getCompositionSources($days, $extra_days);

  abstract function getCompositionArgs();

  /**
   * e.g. {@link #get_exchange_name()}
   */
  abstract function getHeadingTitle($key, $args);

  public function getData($days) {
    $columns = array();

    $key_column = array('type' => 'date', 'title' => ct("Date"));

    // $columns = $this->getTickerColumns();

    // TODO extra_days_necessary
    $extra_days = 10;

    $sources = $this->getCompositionSources($days, $extra_days);
    $args = $this->getCompositionArgs();

    $data = array();
    $last_updated = false;
    $exchanges_found = array();
    $maximum_balances = array();  // only used to check for non-zero accounts

    $data_temp = array();
    $hide_missing_data = !require_get("debug_show_missing_data", false);
    $latest = array();
    foreach ($sources as $source) {
      $q = db()->prepare($source['query']);
      $q->execute($args);
      while ($ticker = $q->fetch()) {
        $key = date('Y-m-d', strtotime($ticker[$source['key']]));
        if (!isset($data_temp[$key])) {
          $data_temp[$key] = array();
        }
        if (!isset($data_temp[$key][$ticker['exchange']])) {
          $data_temp[$key][$ticker['exchange']] = 0;
        }
        $data_temp[$key][$ticker['exchange']] += $ticker[$source['balance_key']];
        $last_updated = max($last_updated, strtotime($ticker['created_at']));
        $exchanges_found[$ticker['exchange']] = $ticker['exchange'];
        if (!isset($maximum_balances[$ticker['exchange']])) {
          $maximum_balances[$ticker['exchange']] = 0;
        }
        $maximum_balances[$ticker['exchange']] = max($ticker[$source['balance_key']], $maximum_balances[$ticker['exchange']]);
        if (!isset($latest[$ticker['exchange']])) {
          $latest[$ticker['exchange']] = 0;
        }
        $latest[$ticker['exchange']] = max($latest[$ticker['exchange']], strtotime($ticker[$source['key']]));
      }
    }

    // get rid of any exchange summaries that had zero data
    foreach ($maximum_balances as $key => $balance) {
      if ($balance == 0) {
        foreach ($data_temp as $dt_key => $values) {
          unset($data_temp[$dt_key][$key]);
        }
        unset($exchanges_found[$key]);
      }
    }

    // sort by date so we can get previous dates if necessary for missing data
    ksort($data_temp);

    $data = array();

    // add headings after we know how many exchanges we've found
    $first_heading = array('title' => t("Date"));
    $headings = array($first_heading);
    $i = 0;
    // sort them so they're always in the same order
    ksort($exchanges_found);
    foreach ($exchanges_found as $key => $ignored) {
      $headings[$key] = array(
        'title' => $this->getHeadingTitle($key, $args),
      );
    }
    // $data[0] = $headings;

    // add '0' for exchanges that we've found at one point, but don't have a data point
    // but reset to '0' for exchanges that are no longer present (i.e. from graph_data_balances archives)
    // this fixes a bug where old securities data is still displayed as present in long historical graphs
    $previous_row = array();
    foreach ($data_temp as $date => $values) {
      $row = array();
      foreach ($exchanges_found as $key => $ignored) {
        if (!$hide_missing_data || strtotime($date) <= $latest[$key]) {
          if (!isset($values[$key])) {
            $row[$key] = graph_number_format(isset($previous_row[$key]) ? $previous_row[$key] : 0);
          } else {
            $row[$key] = graph_number_format(demo_scale($values[$key]));
          }
        } else {
          $row[$key] = graph_number_format(0);
        }
      }
      if (count($row) > 0) {
        // don't add empty rows
        $data[$date] = $row;
        $previous_row = $row;
      }
    }

    // sort each row by the biggest value in the most recent data
    // so e.g. BTC comes first, LTC comes second, regardless of order of summary_instances, balances etc
    $keys = array_keys($data);
    // we can only sort if we actually have data
    if (count($keys) == 0) {
      // bail early
      throw new NoDataGraphException_AddCurrencies();
    }

    $last_row = $data[$keys[count($keys)-1]];
    arsort($last_row);
    $data_temp = array();
    foreach ($data as $row => $columns) {
      $temp = array();
      foreach ($last_row as $key => $ignored) {
        $temp[$key] = $columns[$key];
      }
      $data_temp[$row] = $temp;
    }
    $data = $data_temp;

    // convert columns and data into numeric indices
    $result_columns = array();
    $result_column_map = array();
    foreach ($columns as $key => $column) {
      $result_columns[] = array(
        'type' => 'number',
        'title' => $this->getHeadingTitle($key, $args),
      );
      // if the key is a currency, use the same currency colour across all graphs
      if (in_array(strtolower($key), get_all_currencies())) {
        $result_columns[count($result_columns)-1]['color'] = array_search(strtolower($key), get_all_currencies());
      }
      $result_column_map[$key] = count($result_columns) - 1;
    }
    $result_data = array();
    foreach ($data as $date => $row) {
      $new_row = array();
      foreach ($row as $key => $value) {
        $new_row[$result_column_map[$key]] = $value;
      }
      $result_data[$date] = $new_row;
    }

    // find the last row, and calculate its total for later
    $last_row = array();
    $last_row_total = 0;
    foreach ($result_data as $date => $row) {
      $last_row = $row;
    }
    foreach ($row as $value) {
      $last_row_total += $value;
    }

    // sort the last row, and then use this new order to resort all
    // of the columns and data
    arsort($last_row);
    $sorted_columns = array();
    $sorted_data = array();
    foreach ($last_row as $i => $ignored) {
      $sorted_columns[] = $result_columns[$i];
    }
    foreach ($result_data as $date => $rows) {
      $sorted_row = array();
      foreach ($last_row as $i => $ignored) {
        $sorted_row[] = $rows[$i];
      }
      $sorted_rows[$date] = $sorted_row;
    }

    return array(
      'key' => $key_column,
      'columns' => $sorted_columns,
      'data' => $sorted_rows,
      'last_updated' => $last_updated,

      'last_row_total' => $last_row_total,
    );

  }

}
