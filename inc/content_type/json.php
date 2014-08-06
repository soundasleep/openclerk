<?php
header("Content-Type: application/json");

// TODO refactor with js.php

function my_content_type_exception_handler($e) {
	$message = "Error: " . htmlspecialchars($e->getMessage());
	if ($_SERVER['SERVER_NAME'] === 'localhost') {
		// only display trace locally
		$message .= "\nTrace:" . print_exception_trace_js($e);
	}
	$result = array('success' => false, 'message' => $message);
	echo json_encode($result);
}

function print_exception_trace_js($e) {
	if (!$e) {
		return "null";
	}
	if (!($e instanceof Exception)) {
		return "Not exception: " . get_class($e) . ": " . print_r($e, true) . "";
	}
	$string = "";
	$string .= $e->getMessage() . " (" . get_class($e) . ")\n";
	$string .= "* " . $e->getFile() . "#" . $e->getLine() . "\n";
	foreach ($e->getTrace() as $e2) {
		$string .= "  * " . $e2['file'] . "#" . $e2['line'] . ": " . $e2['function'] . (isset($e2['args']) ? format_args_list($e2['args']) : "") . "\n";
	}
	if ($e->getPrevious()) {
		$string .= "Caused by:";
		$string .= print_exception_trace($e->getPrevious());
		$string .= "\n";
	}
	$string .= "\n";
	return $string;
}
