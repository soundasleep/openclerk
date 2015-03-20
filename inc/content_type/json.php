<?php
header("Content-Type: application/json");

// TODO refactor with js.php

function my_content_type_exception_handler($e) {
  $message = "Error: " . htmlspecialchars($e->getMessage());
  $result = array('success' => false, 'message' => $message);
  if (is_localhost()) {
    // only display trace locally
    $result['trace'] = print_exception_trace_js($e);
  }
  echo json_encode($result);
}

function print_exception_trace_js($e) {
  if (!$e) {
    return "null";
  }
  if (!($e instanceof Exception)) {
    return "Not exception: " . get_class($e) . ": " . print_r($e, true) . "";
  }
  $result = array();
  $result['message'] = $e->getMessage();
  $result['class'] = get_class($e);
  $result['file'] = $e->getFile();
  $result['line'] = $e->getLine();
  $result['trace'] = array();
  foreach ($e->getTrace() as $e2) {
    $result['trace'][] = (isset($e2['file']) ? $e2['file'] : '(unknown)') . "#" . (isset($e2['line']) ? $e2['line'] : '(unknown)') . ": " . $e2['function'] . (isset($e2['args']) ? format_args_list($e2['args']) : "");
  }
  if ($e->getPrevious()) {
    $result['cause'] = print_exception_trace_js($e->getPrevious());
  }
  return $result;
}
