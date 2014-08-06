<?php

spl_autoload_register('classloader');

function classloader($classname) {
	if (class_exists($classname)) {
		return true;
	}

	$renamed = str_replace("_", "/", $classname);
	$renamed = preg_replace("#/+#", "/", $renamed);
	if (file_exists(__DIR__ . "/classes/" . $renamed . ".php")) {
		require(__DIR__ . "/classes/" . $renamed . ".php");
	} else {
		// throw new Exception("Could not load class '$classname' from '$renamed'");
	}
}
