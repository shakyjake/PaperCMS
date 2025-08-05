<?php

/**
 * Unzip a zip
 * @param string $zip_path [required] The path to the zip file to unzip
 * @param string $destination [required] The path to the directory to unzip the zip file to
 * @return bool
*/
function unzip(string $zip_path, string $destination) : bool {

	if(!file_exists($zip_path)){
		throw new Exception('Zip file does not exist at the specified location: ' . $zip_path);
	}

	if(!is_dir($destination)){
		throw new Exception('Destination directory does not exist at the specified location: ' . $destination);
	}

	$zip = new ZipArchive;

	if($zip->open($zip_path) === true){
		$zip->extractTo($destination);
		$zip->close();
		return true;
	}

	return false;

}

?>