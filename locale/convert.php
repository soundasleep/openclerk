<?php

/**
 * Batch script to convert `translated/*_locale.json` into local `locale.php` includes.
 */

$dir = __DIR__ . "/translated/";
if ($dh = opendir($dir)) {
	while (($file = readdir($dh)) !== false) {
		$matches = false;
		if (preg_match("/_([a-z@]+).json$/i", $file, $matches)) {
			$locale = $matches[1];

			// switch over specific locales
			switch ($locale) {
				case "en@lolcat":
					$locale = "lolcat";
					continue;
			}

			echo $dir . $file . " -> " . $locale . "\n";

			$json = json_decode(file_get_contents($dir . $file));
			if (!$json) {
				throw new Exception("Could not load $dir$file: invalid JSON");
			}

			// now write locale.php
			$fp = fopen(__DIR__ . "/" . $locale . ".php", "w");
			fwrite($fp, "<?php\n\n/**\n * $locale template file\n * Generated from '$file' at " . date('r') . "\n */\n\n");
			fwrite($fp, '$' . "result = array(\n");
			foreach ($json as $key => $value) {
				fwrite($fp, "\t\"" . addslashes($key) . "\" => \"" . addslashes($value) . "\",\n");
			}
			fwrite($fp, ");\n");
			fclose($fp);

		}
	}
	closedir($dh);
}
