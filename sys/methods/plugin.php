<?php

/**
 * Load all plugins
 * @return undefined
 */
function plugins_require(){

	$plugin_root = __ROOT__ . '\\plugins';

	$plugins = scandir($plugin_root);

	foreach($plugins as $plugin){
		if(is_dir($plugin)){
			$plugin_path = sprintf('%1%s%2$sindex.php', plugin_path($plugin), DIRECTORY_SEPARATOR);
			if(file_exists($plugin_path)){
				require_once $plugin_path;
			}
		}
	}

}

/**
 * The path to a plugin folder
 * @param string $zip_path [required] The path to the zip file to unzip
 * @param string $destination [required] The path to the directory to unzip the zip file to
 * @return bool
 */
function plugin_path(string $plugin) : string {
	return sprintf('%1$s%2$splugins%2$s%3$s', __ROOT__, DIRECTORY_SEPARATOR, $plugin);
}

?>