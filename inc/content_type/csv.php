<?php
require_once(__DIR__ . "/../csv.php");

header("Content-Type: text/csv");

function my_content_type_exception_handler($e) {
	$message = "Error: " . htmlspecialchars($e->getMessage());
	echo csv_encode("error") . "," . csv_encode($e->getMessage()) . "\n";
}
