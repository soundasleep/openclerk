<?php

spl_autoload_register('classloader');

class ClassLoaderException extends Exception { }

function classloader($classname) {
  if (class_exists($classname)) {
    return true;
  }

  $renamed = str_replace("_", "/", $classname);
  $renamed = preg_replace("#/+#", "/", $renamed);
  if (file_exists(__DIR__ . "/classes/" . $renamed . ".php")) {
    require(__DIR__ . "/classes/" . $renamed . ".php");
  } else {
    // we can expect certain namespace classes _should_ always exist
    if (strtolower(substr($renamed, 0, strlen("GraphRenderer/"))) == "graphrenderer/") {
      throw new ClassLoaderException("Could not find class '$classname' from '$renamed'");
    }
  }
}
