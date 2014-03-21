<?php

if (!isset($_GET['code'])) {
	throw new Exception("No code found");
}

require(__DIR__ . "/phpqrcode.php");
QRcode::png(substr($_GET['code'], 0, 1024 /* max 1024 chars */));

?>