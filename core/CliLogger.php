<?php

namespace Core;

use \Monolog\Logger;

/**
 * A simple {@link Logger} for CLI output.
 */
class CliLogger extends \Monolog\Handler\AbstractHandler {
  function handle(array $record) {
    $message = $record['message'];
    $class = "";
    if ($record['level'] >= Logger::WARNING) {
      if ($record['level'] >= Logger::ERROR) {
        $message = "[ERROR] " . $message;
      } else {
        $message = "[Warning] " . $message;
      }
    }
    echo "* $message\n";
  }
}
