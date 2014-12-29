<?php

namespace Core;

use \Monolog\Logger;

class MyLogger extends \Monolog\Handler\AbstractHandler {
  function handle(array $record) {
    $message = $record['message'];
    $class = "";
    if ($record['level'] >= Logger::WARNING) {
      if ($record['level'] >= Logger::ERROR) {
        $class = "error";
        $message = "[ERROR] " . $message;
      } else {
        $class = "warning";
        $message = "[Warning] " . $message;
      }
    }
    echo "<li class=\"$class\">" . htmlspecialchars($message) . "</li>\n";
  }
}
