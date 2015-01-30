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
    echo "<li class=\"$class\">";
    // if it's ONLY a link_to(), then render it as a link
    if (preg_match("#^(.*?)(<a href=\"[^\"<]+\">[^<]+</a>)$#s", $message, $matches)) {
      echo htmlspecialchars($matches[1]) . $matches[2];
    } else {
      echo htmlspecialchars($message);
    }
    echo "</li>\n";
  }
}
