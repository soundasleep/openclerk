<?php

namespace Core;

class MyLogger extends \Db\Logger {
  function log($s) {
    echo "<li>" . htmlspecialchars($s) . "</li>";
  }
  function error($s) {
    echo "<li class=\"error\" style=\"color:red;\">" . htmlspecialchars($s) . "</li>";
  }
}
