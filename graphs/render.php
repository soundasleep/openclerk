<?php


function get_graph_days($graph) {
  return ((isset($graph['days']) && $graph['days'] > 0) ? ((int) $graph['days']) : 45);
}

// e.g. a SMA with a period of 10 requires the 10 days of data before as well
function extra_days_necessary($graph) {
  $count = 0;
  if (isset($graph['technicals'])) {
    foreach ($graph['technicals'] as $t) {
      $count = min(get_site_config('technical_period_max'), $t['technical_period']);
    }
  }
  return $count;
}

/**
 * Apply graph technicals based on $graph['technicals'] - which is an array
 * of (technical_type, technical_period).
 *
 * @param $use_headings actual headings to use, or false (default) to load it from the $data; returns array(data, headings) instead of just data
 * @param $ignore_first_row if true (default), ignore the first row of rendered data
 */
function calculate_technicals($graph, $data, $use_headings = false, $ignore_first_row = true) {
  $days = get_graph_days($graph);
  if (!$use_headings) {
    $headings = $data[0];
    if (count($data) <= 1) {
      throw new GraphException("Cannot calculate technicals for graph '" . htmlspecialchars($graph['graph_type']) . "' with no data");
    }
  } else {
    $headings = $use_headings;
    if (count($data) <= 0) {
      throw new GraphException("Cannot calculate technicals for graph '" . htmlspecialchars($graph['graph_type']) . "' with no data");
    }
  }
  $original_rows = count($headings);

  // need to sort data by date
  ksort($data);

  $new_headings = array();
  if (isset($graph['technicals'])) {
    // if we have headings, then data that was in [1] and [2] are actually in [0] and [1];
    // we fix the data here so we can continue to use old technical code silently
    // TODO this is a temporary fix until all code is using use_headings, and we can update all
    // other technicals code correctly
    if (!$ignore_first_row) {
      foreach ($data as $key => $row) {
        array_unshift($data[$key], "inserted temporary value");
      }
    }

    $graph_technical_types = graph_technical_types();
    foreach ($graph['technicals'] as $t) {
      $i = -1;
      foreach ($data as $label => $row) {
        $i++;
        if ($i == 0 && $ignore_first_row) continue; // skip heading row
        if ($i < count($data) - $days - 1) continue;  // skip period data that isn't displayed

        // we now actually calculate data
        switch ($t['technical_type']) {
          case "sma":
            // simple moving average
            $new_headings = array(array(
              'title' => "SMA (" . number_format($t['technical_period']) . ")",
              'line_width' => 1,
              'color' => default_technical_colour_index(),
              'technical' => true,
            ));

            $last = 0;
            $sum = 0;
            for ($j = 0; $j < $t['technical_period']; $j++) {
              $key = date('Y-m-d', strtotime($label . " -$j days"));
              $last = isset($data[$key]) ?
                (isset($data[$key][2]) ? ($data[$key][1] + $data[$key][2]) / 2 : $data[$key][1]) : $last; // take average if both bid and ask are defined
              $sum += $last;
            }

            $data[$label][] = graph_number_format($sum / $t['technical_period']);
            break;

          default:
            if (isset($graph_technical_types[$t['technical_type']]['callback'])) {
              // a premium graph technical type, defined elsewhere
              // should return array('headings' => array, 'data' => array) for each row
              $result = $graph_technical_types[$t['technical_type']]['callback']($graph, $t, $label, $data);
              $new_headings = $result['headings'];
              foreach ($result['data'] as $value) {
                $data[$label][] = $value;
              }
              break;

            } else {
              throw new GraphException("Unknown technical type '" . $t['technical_type'] . "'");
            }
        }
      }
    }

    // TODO ... and then we get rid of the unnecessary labels
    if (!$ignore_first_row) {
      foreach ($data as $key => $row) {
        array_shift($data[$key]);
      }
    }

    // add headings
    foreach ($new_headings as $h) {
      if ($use_headings) {
        $headings[] = $h;
      } else {
        $data[0][] = $h;
      }
    }
  }

  if ($use_headings) {
    // (new behaviour, not implemented yet)
    return array(
      'headings' => $headings,
      'data' => $data,
    );
  } else {
    // move the first $original_rows to the end, so they are displayed on top
    // (original behaviour)
    if (count($headings) != $original_rows) {
      $data_new = array();
      foreach ($data as $label => $row) {
        $r = array();
        $row_values = array_values($row);   // get rid of any associative indexes
        $r[] = $row_values[0];  // keep date row
        for ($j = $original_rows; $j < count($row_values); $j++) {
          $r[] = $row_values[$j];
        }
        for ($j = 1; $j < $original_rows; $j++) {
          $r[] = $row_values[$j];
        }
        $data_new[$label] = $r;
      }
      $data = $data_new;
    }

    return $data;
  }
}

function render_ticker_graph($graph, $exchange, $cur1, $cur2) {

  $data = array();
  $data[0] = array(t("Date"),
    array(
      'title' => t(":pair Bid", array(':pair' => get_currency_abbr($cur1) . "/" . get_currency_abbr($cur2))),
      'line_width' => 2,
      'color' => default_chart_color(0),
    ),
    // put Ask second so that it is drawn over Bid (but using colour 0)
    array(
      'title' => t(":pair Ask", array(':pair' => get_currency_abbr($cur1) . "/" . get_currency_abbr($cur2))),
      'line_width' => 2,
      'color' => default_chart_color(1),
    ),
  );
  if ($exchange == 'themoneyconverter' || $exchange == "coinbase") {
    // hack fix because TheMoneyConverter and Coinbase only have last_trade
    unset($data[0][2]);
    $data[0][1]['title'] = get_currency_abbr($cur1) . "/" . get_currency_abbr($cur2);
    $data[0][1]['color'] = default_chart_color(1);
  }
  $last_updated = false;
  $days = get_graph_days($graph);
  $extra_days = extra_days_necessary($graph);

  $sources = array(
    // cannot use 'LIMIT :limit'; PDO escapes :limit into string, MySQL cannot handle or cast string LIMITs
    // first get summarised data
    array('query' => "SELECT * FROM graph_data_ticker WHERE exchange=:exchange AND
      currency1=:currency1 AND currency2=:currency2 AND data_date > DATE_SUB(NOW(), INTERVAL " . ($days + $extra_days) . " DAY) ORDER BY data_date DESC", 'key' => 'data_date'),
    // and then get more recent data
    array('query' => "SELECT * FROM ticker WHERE is_daily_data=1 AND exchange=:exchange AND
      currency1=:currency1 AND currency2=:currency2 ORDER BY created_at DESC LIMIT " . ($days + $extra_days), 'key' => 'created_at'),
  );

  $day_count = 0;
  foreach ($sources as $source) {
    $q = db()->prepare($source['query']); // TODO add days_to_display as parameter
    $q->execute(array(
      'exchange' => $exchange,
      'currency1' => $cur1,
      'currency2' => $cur2,
    ));
    while ($ticker = $q->fetch()) {
      $data_key = date('Y-m-d', strtotime($ticker[$source['key']]));
      if ($exchange == 'themoneyconverter' || $exchange == "coinbase") {
        // hack fix because TheMoneyConverter and Coinbase only has last_trade
        $data[$data_key] = array(
          'new Date(' . date('Y, n-1, j', strtotime($ticker[$source['key']])) . ')',
          graph_number_format(/* last_trade is in ticker; last_trade_closing is in graph_data_ticker */ isset($ticker['last_trade']) ? $ticker['last_trade'] : $ticker['last_trade_closing']),
        );
      } else {
        $data[$data_key] = array(
          'new Date(' . date('Y, n-1, j', strtotime($ticker[$source['key']])) . ')',
          graph_number_format($ticker['bid']),
          graph_number_format($ticker['ask']),
        );
      }
      $last_updated = max($last_updated, strtotime($ticker['created_at']));
    }
  }


  if (count($data) > 1) {
    // calculate deltas if necessary
    $data = calculate_graph_deltas($graph, $data);

    // calculate technicals
    // (only if there is at least one point of data, otherwise calculate_technicals() will throw an error)
    $data = calculate_technicals($graph, $data);


    // discard early data
    $data = discard_early_data($data, $days);

    // sort by key, but we only want values
    uksort($data, 'cmp_time');
    $graph['subheading'] = format_subheading_values($graph, $data);
    $graph['last_updated'] = $last_updated;
    render_linegraph_date($graph, array_values($data));

  } else {
    render_text($graph, "There is no data available for this exchange pair yet.");
  }

}

/**
 * Calculate deltas on this graph if the graph has defined a delta property.
 * Returns the updated data array.
 * $graph['delta'] = ('', 'percent', 'absolute')
 * @param $ignore_first_row if true (default), do not apply delta to first row of data
 */
function calculate_graph_deltas($graph, $data, $ignore_first_row = true) {
  // calculate deltas
  if ($graph['delta']) {
    // keep in mind that this data is in arbitrary order, i.e. (2013, 2012, 2011) not (2011, 2012, 2013)
    // so we first need to sort it into ascending order
    uksort($data, 'cmp_time_reverse');

    $result = array();
    $previous = array();
    foreach ($data as $key => $row) {
      if ($key === 0) {
        // go through and modify the headings
        foreach ($row as $k => $v) {
          if ($k !== 0) {
            $row[$k]['title'] .= ($graph['delta'] == 'percent') ? ' +%' : " +";
          }
        }

        $result[$key] = $row;
      } else {
        $this_row = array();
        foreach ($row as $k => $v) {
          if ($k === 0 && $ignore_first_row) {
            // keep the date label
            $this_row[$k] = $v;
          } else if (isset($previous[$k])) {
            // absolute or percent?
            if ($graph['delta'] == 'percent') {
              // percent
              if ($previous[$k] != 0) { // prevent div/0
                $this_row[$k] = graph_number_format((($v - $previous[$k]) / $previous[$k]) * 100);
              }
            } else {
              // absolute
              $this_row[$k] = graph_number_format($v - $previous[$k]);
            }
          }
          $previous[$k] = $v;
        }
        if (count($this_row) > 1 /* i.e. more than just the date label */) {
          $result[$key] = $this_row;
        }
      }
    }
    $data = $result;
  }

  return $data;
}

/**
 * Get the most recent data values, strip out any dates and technical indicator
 * values, and return a HTML string that can be used to show the most recent
 * data for this graph.
 */
function format_subheading_values($graph, $input, $suffix = false) {
  $array = array_slice($input, 1 /* skip heading row */, 1, true);
  $array = array_pop($array); // array_slice returns an array(array(...))
  // array[0] is always the date; the remaining values are the formatted data
  // remove any data that is a Date heading or a technical value
  foreach ($input[0] as $key => $heading) {
    if ($key === 0 || (is_array($heading) && isset($heading['technical']) && $heading['technical'])) {
      unset($array[$key]);
    }
  }
  if (!$array) {
    return "";
  }
  if ($graph['delta'] == 'percent') {
    $suffix .= '%';
  }
  foreach ($array as $key => $value) {
    $array[$key] = number_format_html($value, 4, $suffix);
  }
  return implode(" / ", $array);
}

/**
 * Get the most recent data values, strip out any dates and technical indicator
 * values, and return a HTML string that can be used to show the most recent
 * data for this graph.
 * Uses $data and $headings objects, rather than an array of (headings, data)
 */
function format_subheading_values_objects($graph, $data, $headings, $suffix = false) {
  $array = $data;
  $array = array_pop($array); // array_slice returns an array(array(...))
  // array[0] is always the date; the remaining values are the formatted data
  // remove any data that is a Date heading or a technical value
  foreach ($headings as $key => $heading) {
    if (isset($heading['technical']) && $heading['technical']) {
      unset($array[$key]);
    }
  }
  if (!$array) {
    return "";
  }
  if ($graph['delta'] == 'percent') {
    $suffix .= '%';
  }
  if (!is_array($array)) {
    throw new GraphException("'$array' is not an array");
  }
  foreach ($array as $key => $value) {
    if (!is_numeric($value)) {
      throw new GraphException("Cannot format subheading value '$value': is not numeric");
    }
    $this_suffix = $suffix;
    if ($headings[$key]['type'] == "percent") {
      $this_suffix .= "%";
    }

    $array[$key] = number_format_html($value, 4, $this_suffix);
  }
  if (count($array) > 3) {
    // only return the first three
    $array = array_slice($array, 0, 3);
  }
  return implode(" / ", $array);
}


/**
 * Same as format_subheading_values(), but sum all values together.
 */
function format_subheading_values_subtotal($graph, $input, $suffix = false) {
  $array = array_slice($input, 1 /* skip heading row */, 1, true);
  $array = array_pop($array); // array_slice returns an array(array(...))
  // array[0] is always the date; the remaining values are the formatted data
  // remove any data that is a Date heading or a technical value
  foreach ($input[0] as $key => $heading) {
    if ($key === 0 || (is_array($heading) && isset($heading['technical']) && $heading['technical'])) {
      unset($array[$key]);
    }
  }
  if (!$array) {
    return "";
  }
  $total = 0;
  foreach ($array as $key => $value) {
    $total += $value;
  }
  if ($graph['delta'] == 'percent') {
    $suffix .= '%';
  }
  return number_format_html($total, 4, $suffix);
}

function discard_early_data($data, $days) {
  $data_new = array();
  foreach ($data as $label => $row) {
    if ($label == 0 || strtotime($label) >= strtotime("-" . $days . " days -1 day")) {
      $data_new[$label] = $row;
    }
  }
  return $data_new;
}

function cmp_time($a, $b) {
  if ($a === 0) return -1;
  if ($b === 0) return 1;
  return strtotime($a) < strtotime($b);
}

function cmp_time_reverse($a, $b) {
  if ($a === 0) return 1;
  if ($b === 0) return -1;
  return strtotime($a) > strtotime($b);
}

function render_summary_graph($graph, $summary_type, $currency, $user_id, $row_title = false) {

  $data = array();
  $data[0] = array(t("Date"),
    array(
      'title' => $row_title ? $row_title : get_currency_abbr($currency),
      'line_width' => 2,
      'color' => default_chart_color(0),
    ),
  );
  $last_updated = false;
  $days = get_graph_days($graph);
  $extra_days = extra_days_necessary($graph);

  $sources = array(
    // first get summarised data
    array('query' => "SELECT * FROM graph_data_summary WHERE user_id=:user_id AND summary_type=:summary_type AND
      data_date >= DATE_SUB(NOW(), INTERVAL " . ($days + $extra_days) . " DAY) ORDER BY data_date DESC", 'key' => 'data_date', 'balance_key' => 'balance_closing'),
    // and then get more recent data
    array('query' => "SELECT * FROM summary_instances WHERE is_daily_data=1 AND summary_type=:summary_type AND
      user_id=:user_id ORDER BY created_at DESC LIMIT " . ($days + $extra_days), 'key' => 'created_at', 'balance_key' => 'balance'),
  );

  foreach ($sources as $source) {
    $q = db()->prepare($source['query']);
    $q->execute(array(
      'summary_type' => $summary_type,
      'user_id' => $user_id,
    ));
    while ($ticker = $q->fetch()) {
      $data[date('Y-m-d', strtotime($ticker[$source['key']]))] = array(
        'new Date(' . date('Y, n-1, j', strtotime($ticker[$source['key']])) . ')',
        graph_number_format(demo_scale($ticker[$source['balance_key']])),
      );
      $last_updated = max($last_updated, strtotime($ticker['created_at']));
    }
  }

  if (count($data) > 1) {
    // calculate deltas if necessary
    $data = calculate_graph_deltas($graph, $data);

    // calculate technicals
    // (only if there is at least one point of data, otherwise calculate_technicals() will throw an error)
    $data = calculate_technicals($graph, $data);

    // discard early data
    $data = discard_early_data($data, $days);

    // sort by key, but we only want values
    uksort($data, 'cmp_time');
    $graph['subheading'] = format_subheading_values($graph, $data);
    $graph['last_updated'] = $last_updated;

    render_linegraph_date($graph, array_values($data));
  } else {
    render_text($graph, t("Either you have not enabled this currency, or your summaries for this currency have not yet been updated by :site_name.") .
        "<br><a class=\"add_accounts\" href=\"" . htmlspecialchars(url_for('wizard_currencies')) . "\">" . ht("Configure currencies") . "</a>");
  }

}

function render_balances_graph($graph, $exchange, $currency, $user_id, $account_id) {

  $days = get_graph_days($graph);
  $extra_days = extra_days_necessary($graph);

  $sources = array(
    // first get summarised data
    array('query' => "SELECT * FROM graph_data_balances WHERE user_id=:user_id AND exchange=:exchange AND account_id=:account_id AND currency=:currency AND
      data_date >= DATE_SUB(NOW(), INTERVAL " . ($days + $extra_days) . " DAY) ORDER BY data_date DESC", 'key' => 'data_date', 'balance_key' => 'balance_closing'),
    // and then get more recent data
    array('query' => "SELECT * FROM balances WHERE is_daily_data=1 AND exchange=:exchange AND account_id=:account_id AND currency=:currency AND
      user_id=:user_id ORDER BY created_at DESC LIMIT " . ($days + $extra_days), 'key' => 'created_at', 'balance_key' => 'balance'),
  );

  render_sources_graph($graph, $sources, array(
      'exchange' => $exchange,
      'account_id' => $account_id,
      'currency' => $currency,
    ), $user_id, 'render_graph_return_currency');

}

function render_graph_return_currency($exchange, $args) { return get_currency_abbr($args['currency']); }

function render_balances_composition_graph($graph, $currency, $user_id, $stacked = false, $proportional = false) {

  $days = get_graph_days($graph);
  $extra_days = extra_days_necessary($graph);

  $sources = array(
    // we can't LIMIT by days here, because we may have many accounts for one exchange
    // first get summarised data
    array('query' => "SELECT * FROM graph_data_balances WHERE user_id=:user_id AND currency=:currency
      AND data_date > DATE_SUB(NOW(), INTERVAL " . ($days + $extra_days) . " DAY) ORDER BY data_date DESC", 'key' => 'data_date', 'balance_key' => 'balance_closing'),
    // and then get more recent data
    array('query' => "SELECT * FROM balances WHERE is_daily_data=1 AND currency=:currency
      AND user_id=:user_id AND created_at >= DATE_SUB(NOW(), INTERVAL " . ($days + $extra_days) . " DAY) ORDER BY created_at DESC", 'key' => 'created_at', 'balance_key' => 'balance'),
    // also include blockchain balances
    // first get summarised data
    array('query' => "SELECT *, 'blockchain' AS exchange FROM graph_data_summary WHERE user_id=:user_id AND summary_type=CONCAT('blockchain', :currency) AND
      data_date >= DATE_SUB(NOW(), INTERVAL " . ($days + $extra_days) . " DAY) ORDER BY data_date DESC", 'key' => 'data_date', 'balance_key' => 'balance_closing'),
    // and then get more recent data
    array('query' => "SELECT *, 'blockchain' AS exchange FROM summary_instances WHERE is_daily_data=1 AND summary_type=CONCAT('blockchain', :currency) AND
      user_id=:user_id ORDER BY created_at DESC LIMIT " . ($days + $extra_days), 'key' => 'created_at', 'balance_key' => 'balance'),
    // also include offset balances
    // first get summarised data
    array('query' => "SELECT *, 'offsets' AS exchange FROM graph_data_summary WHERE user_id=:user_id AND summary_type=CONCAT('offsets', :currency) AND
      data_date >= DATE_SUB(NOW(), INTERVAL " . ($days + $extra_days) . " DAY) ORDER BY data_date DESC", 'key' => 'data_date', 'balance_key' => 'balance_closing'),
    // and then get more recent data
    array('query' => "SELECT *, 'offsets' AS exchange FROM summary_instances WHERE is_daily_data=1 AND summary_type=CONCAT('offsets', :currency) AND
      user_id=:user_id ORDER BY created_at DESC LIMIT " . ($days + $extra_days), 'key' => 'created_at', 'balance_key' => 'balance'),
  );

  render_sources_graph($graph, $sources, array('currency' => $currency), $user_id, 'get_exchange_name', false /* $has_subheadings */, $stacked, $proportional);
}

function render_balances_btc_equivalent_graph($graph, $user_id, $stacked = false, $proportional = false) {

  $days = get_graph_days($graph);
  $extra_days = extra_days_necessary($graph);

  $sources = array();
  $summary_currencies = get_all_summary_currencies();
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

  render_sources_graph($graph, $sources, array(/* args */), $user_id, 'render_graph_return_exchange_currency', 'last_total' /* $has_subheadings */, $stacked, $proportional);
}

function render_graph_return_exchange_currency($exchange, $args) { return get_currency_abbr($exchange); }

/**
 * Renders a collection of $sources with a given set of arguments $args, a user ID $user_id
 * and a heading callback function $get_heading_title.
 *
 * @param $has_subheadings true (default), false (no subheading), 'last_total' (total the most recent data)
 * @param $stacked if true, renders the graph as a stacked graph rather than line graph. defaults to false.
 * @param $make_proportional if true, converts all values to proportional data w.r.t. each date point, up to 100%. defaults to false.
 */
function render_sources_graph($graph, $sources, $args, $user_id, $get_heading_title /* callback */, $has_subheadings = true, $stacked = false, $make_proportional = false) {

  $data = array();
  $last_updated = false;
  $days = get_graph_days($graph);
  $extra_days = extra_days_necessary($graph);
  $exchanges_found = array();
  $maximum_balances = array();  // only used to check for non-zero accounts

  $data_temp = array();
  $hide_missing_data = !require_get("debug_show_missing_data", false);
  $latest = array();
  foreach ($sources as $source) {
    $q = db()->prepare($source['query']);
    $q_args = $args;
    $q_args['user_id'] = $user_id;
    $q->execute($q_args);
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
  if ($make_proportional) {
    $first_heading['min'] = 0;
    $first_heading['max'] = 100;
  }
  $headings = array($first_heading);
  $i = 0;
  // sort them so they're always in the same order
  ksort($exchanges_found);
  foreach ($exchanges_found as $key => $ignored) {
    $headings[$key] = array(
      'title' => $get_heading_title($key, $args),
      'line_width' => 2,
      // if the key is a currency, use the same currency colour across all graphs
      'color' => default_chart_color(in_array(strtolower($key), get_all_currencies()) ? array_search(strtolower($key), get_all_currencies()) : $i++),
    );
  }
  $data[0] = $headings;

  // add '0' for exchanges that we've found at one point, but don't have a data point
  // but reset to '0' for exchanges that are no longer present (i.e. from graph_data_balances archives)
  // this fixes a bug where old securities data is still displayed as present in long historical graphs
  $previous_row = array();
  foreach ($data_temp as $date => $values) {
    $row = array('new Date(' . date('Y, n-1, j', strtotime($date)) . ')',);
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
    if (count($row) > 1) {
      // don't add empty rows
      $data[$date] = $row;
      $previous_row = $row;
    }
  }

  // make proportional?
  if ($make_proportional) {
    $data_temp = array();
    foreach ($data as $row => $columns) {
      $row_temp = array();
      if ($row == 0) {
        foreach ($columns as $key => $value) {
          if ($key !== 0) {
            $value['title'] .= " %";
          }
          $row_temp[$key] = $value;
        }
      } else {
        $total = 0;
        foreach ($columns as $key => $value) {
          $total += ($key === 0) ? 0 : $value;
        }
        foreach ($columns as $key => $value) {
          $row_temp[$key] = ($key === 0) ? $value : graph_number_format($total == 0 ? 0 /* prevent div/0 */ : ($value / $total) * 100);
        }
      }
      $data_temp[$row] = $row_temp;
    }
    $data = $data_temp;
  }

  // sort each row by the biggest value in the most recent data
  // so e.g. BTC comes first, LTC comes second, regardless of order of summary_instances, balances etc
  $keys = array_keys($data);
  $last_row = $data[$keys[count($keys)-1]];
  arsort($last_row);
  $data_temp = array();
  foreach ($data as $row => $columns) {
    $temp = array();
    $temp[0] = $columns[0];   // keep row 0 the same
    foreach ($last_row as $key => $ignored) {
      if ($key !== 0) {
        $temp[$key] = $columns[$key];
      }
    }
    $data_temp[$row] = $temp;
  }
  $data = $data_temp;

  if (count($data) > 1) {
    // calculate deltas if necessary
    $data = calculate_graph_deltas($graph, $data);

    // calculate technicals
    // (only if there is at least one point of data, otherwise calculate_technicals() will throw an error)
    $data = calculate_technicals($graph, $data);

    // discard early data
    $data = discard_early_data($data, $days);

    // sort by key, but we only want values
    // we also need to sort by time *before* calculating subheadings
    uksort($data, 'cmp_time');
    if ($has_subheadings) {
      if ($has_subheadings == 'last_total') {
        $graph['subheading'] = format_subheading_values_subtotal($graph, $data);
      } else {
        $graph['subheading'] = format_subheading_values($graph, $data);
      }
    }
    $graph['last_updated'] = $last_updated;

    render_linegraph_date($graph, array_values($data), $stacked);
  } else {
    if ($user_id == get_site_config('system_user_id')) {
      render_text($graph, t("No data to display."));  // or Invalid balance type.
    } else {
      render_text($graph, t("Either you have not enabled this balance, or your summaries for this balance have not yet been updated by :site_name.") .
          "<br><a class=\"add_accounts\" href=\"" . htmlspecialchars(url_for('wizard_currencies')) . "\">" . ht("Configure currencies") . "</a>");
    }
  }

}

function render_external_graph($graph) {

  if (!isset($graph['arg0_resolved'])) {
    $q = db()->prepare("SELECT * FROM external_status_types WHERE id=?");
    $q->execute(array($graph['arg0']));
    $resolved = $q->fetch();
    if (!$resolved) {
      render_text($graph, "Invalid external status type ID.");
      return;
    } else {
      $graph['arg0_resolved'] = $resolved['job_type'];
    }
  }
  $job_type = $graph['arg0_resolved'];

  $data = array();
  $data[0] = array(t("Date"),
    array(
      'title' => t("% success"),
      'line_width' => 2,
      'color' => default_chart_color(0),
      'min' => 0,
      'max' => 100,
    ),
  );
  $last_updated = false;
  $days = get_graph_days($graph);
  $extra_days = extra_days_necessary($graph);

  $sources = array(
    // we can't LIMIT by days here, because we don't have is_daily_data => multiple points for one day
    // TODO first get summarised data
    // and then get more recent data
    // TODO this gets ALL data (24 points a day); should summarise instead
    /*
    array('query' => "SELECT * FROM external_status WHERE is_daily_data=1 AND job_type=:job_type
      ORDER BY created_at DESC LIMIT " . ($days + $extra_days), 'key' => 'created_at', 'balance_key' => 'balance'),
      */
    array('query' => "SELECT * FROM external_status WHERE job_type=:job_type
      AND created_at >= DATE_SUB(NOW(), INTERVAL " . ($days + $extra_days) . " DAY)
      ORDER BY is_recent ASC, created_at DESC", 'key' => 'created_at', 'balance_key' => 'balance'),
  );

  foreach ($sources as $source) {
    $q = db()->prepare($source['query']);
    $q->execute(array(
      'job_type' => $job_type,
    ));
    while ($ticker = $q->fetch()) {
      $data[date('Y-m-d', strtotime($ticker[$source['key']]))] = array(
        'new Date(' . date('Y, n-1, j', strtotime($ticker[$source['key']])) . ')',
        graph_number_format(100 * (1 - ($ticker['job_errors'] / $ticker['job_count']))),
      );
      $last_updated = max($last_updated, strtotime($ticker['created_at']));
    }
  }

  // discard early data
  $data = discard_early_data($data, $days);

  // sort by key, but we only want values
  uksort($data, 'cmp_time');
  $graph['subheading'] = format_subheading_values($graph, $data, "%");
  $graph['last_updated'] = $last_updated;

  if (count($data) > 1) {
    render_linegraph_date($graph, array_values($data));
  } else {
    render_text($graph, t("There is not yet any historical data for this external API."));
  }

}

function render_site_statistics_queue($graph) {

  if (!is_admin()) {
    render_text(t("This graph is for administrators only."));
    return;
  }

  $data = array();
  $data[0] = array(t("Date"),
    array(
      'title' => " " . t("Free delay"),
      'line_width' => 2,
      'color' => default_chart_color(0),
    ),
    array(
      'title' => " " . t("Premium delay"),
      'line_width' => 2,
      'color' => default_chart_color(1),
    ),
  );
  $last_updated = false;
  $days = get_graph_days($graph);
  $extra_days = extra_days_necessary($graph);

  $sources = array(
    array('query' => "SELECT * FROM site_statistics
      ORDER BY created_at DESC LIMIT " . ($days + $extra_days), 'key' => 'created_at'),
  );

  foreach ($sources as $source) {
    $q = db()->prepare($source['query']);
    $q->execute();
    while ($ticker = $q->fetch()) {
      $data[date('Y-m-d H-i-s', strtotime($ticker[$source['key']]))] = array(
        'new Date(' . date('Y, n-1, j, H, i, s', strtotime($ticker[$source['key']])) . ')',
        graph_number_format($ticker['free_delay_minutes'] / 60),
        graph_number_format($ticker['premium_delay_minutes'] / 60),
      );
      $last_updated = max($last_updated, strtotime($ticker['created_at']));
    }
  }

  $graph['last_updated'] = $last_updated;

  if (count($data) > 1) {
    render_linegraph_date($graph, array_values($data));
  } else {
    render_text($graph, t("There is not yet any historical data for these statistics."));
  }

}

function render_site_admin_statistics($graph) {

  if (!is_admin()) {
    render_text(t("This graph is for administrators only."));
    return;
  }

  $summary = array(
    'users' => array('title' => t('Users'), 'extra' => array('is_disabled=1' => t('Disabled'))),
    'addresses' => array('title' => t('Addresses')),
    'jobs' => array('title' => t('Jobs'), 'extra' => array('is_executed=0' => t('Pending'))),
    'outstanding_premiums' => array('title' => t('Premiums'), 'extra' => array('is_paid=1' => t('Paid'))),
    'uncaught_exceptions' => array('title' => t('Uncaught exceptions')),
    'ticker' => array('title' => t('Ticker instances')),
  );
  $result = array();
  foreach ($summary as $key => $data) {
    $row = array();
    $row[0] = $data['title'];
    if (isset($data['extra'])) {
      foreach ($data['extra'] as $extra_key => $extra_title) {
        $row[0] .= " ($extra_title)";
      }
    }
    $parts = array(
      '1',
      'created_at >= date_sub(now(), interval 7 day)',
      'created_at >= date_sub(now(), interval 1 day)',
      'created_at >= date_sub(now(), interval 1 hour)',
    );
    foreach ($parts as $query) {
      $q = db()->prepare("SELECT COUNT(*) AS c FROM $key WHERE $query");
      $q->execute();
      $c = $q->fetch();
      $row[] = number_format($c['c']);

      if (isset($data['extra'])) {
        foreach ($data['extra'] as $extra_key => $extra_title) {
          $q = db()->prepare("SELECT COUNT(*) AS c FROM $key WHERE $query AND $extra_key");
          $q->execute();
          $c = $q->fetch();
          $row[count($row)-1] .= " (" . number_format($c['c']) . ")";
        }
      }

    }
    $result[] = $row;
  }

  $row = array(t("Unused premium addresses"));
  $q = db()->prepare("SELECT currency, COUNT(*) AS c FROM premium_addresses WHERE is_used=0 GROUP BY currency");
  $q->execute();
  while ($c = $q->fetch()) {
    $row[] = number_format($c['c']) . " (" . get_currency_abbr($c['currency']) . ")";
  }
  $result[] = $row;

  // issue #133: Job Queue Delay is calculated through site_statistics

  $head = array(array(
    "",
    array('title' => t("Total"), 'class' => 'number'),
    array('title' => t("Last week"), 'class' => 'number'),
    array('title' => t("Last day"), 'class' => 'number'),
    array('title' => t("Last hour"), 'class' => 'number'),
  ));
  $graph['last_updated'] = time();
  return render_table_vertical($graph, $result, $head);

}

function render_site_statistics_system_load($graph, $type = "") {

  if (!is_admin()) {
    render_text(t("This graph is for administrators only."));
    return;
  }

  $data = array();
  $data[0] = array(t("Date"),
    array(
      'title' => " 1min",
      'line_width' => 2,
      'color' => default_chart_color(0),
    ),
    array(
      'title' => " 5min",
      'line_width' => 2,
      'color' => default_chart_color(1),
    ),
    array(
      'title' => " 15min",
      'line_width' => 2,
      'color' => default_chart_color(2),
    ),
  );
  $last_updated = false;
  $days = get_graph_days($graph);
  $extra_days = extra_days_necessary($graph);

  $sources = array(
    array('query' => "SELECT * FROM site_statistics
      ORDER BY created_at DESC LIMIT " . ($days + $extra_days), 'key' => 'created_at'),
  );

  foreach ($sources as $source) {
    $q = db()->prepare($source['query']);
    $q->execute();
    while ($ticker = $q->fetch()) {
      $data[date('Y-m-d H-i-s', strtotime($ticker[$source['key']]))] = array(
        'new Date(' . date('Y, n-1, j, H, i, s', strtotime($ticker[$source['key']])) . ')',
        graph_number_format($ticker[$type . 'system_load_1min']),
        graph_number_format($ticker[$type . 'system_load_5min']),
        graph_number_format($ticker[$type . 'system_load_15min']),
      );
      $last_updated = max($last_updated, strtotime($ticker['created_at']));
    }
  }

  $graph['last_updated'] = $last_updated;

  if (count($data) > 1) {
    render_linegraph_date($graph, array_values($data));
  } else {
    render_text($graph, t("There is not yet any historical data for these statistics."));
  }

}

/**
 * @param $report_ref_table can be null
 * @param $report_reference can be null
 * @param $actual_value_key if null, use {$key_prefix}_time/{$key_prefix}_count; otherwise, use this key
 */
function render_metrics_graph($graph, $report_type, $report_table, $report_ref_table, $report_reference, $key_prefix, $key = null, $actual_value_key = null) {
  if ($key == null) {
    $key = $key_prefix;
  }

  if (!is_admin()) {
    return render_text(t("This graph is for administrators only."));
  }

  $q = db()->prepare("SELECT * FROM performance_reports WHERE report_type=? ORDER BY id DESC LIMIT 30");
  $q->execute(array($report_type));
  $reports = $q->fetchAll();
  if (!$reports) {
    return render_text($graph, "No report $report_type found.");
  }

  // construct an array of (date => )
  $data = array();
  $data[0] = array(t("Date"));
  $keys = array();
  $graph['last_updated'] = 0;

  foreach ($reports as $report) {
    // get all queries
    $q = db()->prepare("SELECT * FROM $report_table AS r " .
        ($report_ref_table ? "JOIN $report_ref_table AS q ON r.$report_reference=q.id " : "") .
        "WHERE report_id=?");
    $q->execute(array($report['id']));
    $date = date('Y-m-d H:i:s', strtotime($report['created_at']));
    $row = array('new Date(' . date('Y, n-1, j, H, i, s', strtotime($report['created_at'])) . ')');
    while ($query = $q->fetch()) {
      if (!isset($keys[$query[$key]])) {
        $keys[$query[$key]] = count($keys) + 1;
        $data[0][] = array(
          "title" => $query[$key],
        );
      }
      if ($actual_value_key === null) {
        $row[$keys[$query[$key]]] = graph_number_format($query[$key_prefix . '_time'] / $query[$key_prefix . '_count']);
      } else {
        $row[$keys[$query[$key]]] = graph_number_format($query[$actual_value_key]);
      }
    }
    $data[$date] = $row;
    $graph['last_updated'] = max($graph['last_updated'], strtotime($report['created_at']));
  }

  // fill in any missing rows, e.g. queries that may not have featured in certain reports
  foreach ($data as $date => $row) {
    if ($date === 0) continue;

    foreach ($keys as $id) {
      if (!isset($row[$id])) $data[$date][$id] = 0;
    }
  }

  if (count($data) > 1) {
    render_linegraph_date($graph, array_values($data));
  } else {
    render_text($graph, t("There is not yet any historical data for these statistics."));
  }

}

function render_metrics_db_slow_queries_graph($graph) {
  return render_metrics_graph($graph, 'db_slow_queries', 'performance_report_slow_queries', 'performance_metrics_queries', 'query_id', 'query');
}

function render_metrics_curl_slow_urls_graph($graph) {
  return render_metrics_graph($graph, 'curl_slow_urls', 'performance_report_slow_urls', 'performance_metrics_urls', 'url_id', 'url');
}

function render_metrics_slow_jobs_graph($graph) {
  return render_metrics_graph($graph, 'jobs_slow', 'performance_report_slow_jobs', null, null, 'job', 'job_type');
}

function render_metrics_slow_jobs_db_graph($graph) {
  return render_metrics_graph($graph, 'jobs_slow', 'performance_report_slow_jobs', null, null, null, 'job_type', 'job_database');
}

function render_metrics_slow_pages_graph($graph) {
  return render_metrics_graph($graph, 'pages_slow', 'performance_report_slow_pages', null, null, 'page', 'script_name');
}

function render_metrics_slow_pages_db_graph($graph) {
  return render_metrics_graph($graph, 'pages_slow', 'performance_report_slow_pages', null, null, null, 'script_name', 'page_database');
}

function render_metrics_slow_graphs_graph($graph) {
  return render_metrics_graph($graph, 'graphs_slow', 'performance_report_slow_graphs', null, null, 'graph', 'graph_type');
}

function render_metrics_slow_graphs_db_graph($graph) {
  return render_metrics_graph($graph, 'graphs_slow', 'performance_report_slow_graphs', null, null, null, 'graph_type', 'graph_database');
}

function render_metrics_slow_graphs_count_graph($graph) {
  return render_metrics_graph($graph, 'graphs_slow', 'performance_report_slow_graphs', null, null, null, 'graph_type', 'graph_count');
}

function render_metrics_jobs_frequency_graph($graph) {
  return render_metrics_graph($graph, 'jobs_frequency', 'performance_report_job_frequency', null, null, null, 'job_type', 'jobs_per_hour');
}
