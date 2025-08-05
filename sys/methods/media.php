<?php

/**
 * Echo an image
 * @param string $file_path [required] The path to the image to be outputted
 * @return string
 */
function image_output(string $file_path){

	if(file_exists($file_path)){

		$last_modified = filemtime($file_path);
		$mime_type = mime_content_type($file_path);
		$etag = md5_file($file_path);
		$size = filesize($file_path);

		header('Content-Type: ' . $mime_type);
		header('Content-Length: ' . $size);
		header('Cache-Control: private,max-age=31536000');
		header("Last-Modified: " . gmdate("D, d M Y H:i:s", $last_modified) . " GMT"); 
		header("Expires: " . gmdate("D, d M Y H:i:s", strtotime("+1 year")) . " GMT"); 
		header("Etag: " . $etag);

		$file = fopen($file_path, 'rb');
		$data = fread($file, $size);
		fclose($file);

		echo $data;

	} else {

		http_response_code(404);

	}

}

/**
 * Create a resized version of an image
 * @param string $source_path [required] The path to the source image
 * @param int $width [optional] The width of the resized image
 * @param int $height [optional] The height of the resized image
 * @param string $format [optional] The format (file extension) of the resized image
 * @return string
 */
function image_resize(string $source_path, int $width = 0, int $height = 0, string $file_ext = ''){

	if(!file_exists($source_path)){
		return;
	}

	list($source_width, $source_height) = getimagesize($source_path);

	$source_file_ext = pathinfo($source_path, PATHINFO_EXTENSION);
	if(empty($file_ext)){
		$file_ext = $source_file_ext;
	}

	$source_aspect_ratio = $source_height / $source_width;

	if($width === 0 && $height === 0){
		$width = $source_width;
		$height = $source_height;
	} else if($height === 0){
		$height = (int)($width * $source_aspect_ratio);
	} else if($width === 0){
		$width = (int)($height / $source_aspect_ratio);
	}

	$resized_file_path = sprintf(
		'%1$s/resized/%2$s_%3$s_%4$s_%5$s.%6$s',
		pathinfo($source_path, PATHINFO_DIRNAME),
		pathinfo($source_path, PATHINFO_FILENAME),
		$source_file_ext,
		$width,
		$height,
		$file_ext
	);

	if(file_exists($resized_file_path)){
		return $resized_file_path;
	}

	$aspect_ratio = $height / $width;

	$image = image_from_file($source_path);
	$resized_image = imagecreatetruecolor($width, $height);
	@image_configure($resized_image, $source_path);

	$gravity = [0.5, 0.5];

	$cropped_width = $width;
	$cropped_height = $height;
	
	if($aspect_ratio < $source_aspect_ratio){
		$cropped_width = $source_width;
		$cropped_height = (int)($cropped_width * $aspect_ratio);
	} else {
		$cropped_height = $source_height;
		$cropped_width = (int)($cropped_height / $aspect_ratio);
	}

	$resample_params = [
		'dst_x' => 0,
		'dst_y' => 0,
		'src_x' => 0,
		'src_y' => 0,
		'dst_width' => $width,
		'dst_height' => $height,
		'src_width' => $source_width,
		'src_height' => $source_height
	];

	if($source_width > $width){
		$resample_params['src_x'] = floor(($source_width * $gravity[0]) - ($cropped_width * 0.5));
		if($resample_params['src_x'] + $cropped_width > $source_width){
			$resample_params['src_x'] = $source_width - $cropped_width;
		}
		if($resample_params['src_x'] < 0){
			$resample_params['src_x'] = 0;
		}
	} else {/* You can lead a horse to water but you can't make them put even the slightest bit of effort or thought into the images they try to put on their website. Pad them with white (transparent for PNG) like some sort of godawful instagram image */
		$resample_params['dst_x'] = floor(($width - $source_width) * 0.5);
		$resample_params['src_x'] = 0;
		$resample_params['dst_width'] = $source_width;
	}

	if($source_height > $height){
		$resample_params['src_y'] = floor(($source_height * $gravity[1]) - ($cropped_height * 0.5));
		if($resample_params['src_y'] + $cropped_height > $source_height){
			$resample_params['src_y'] = $source_height - $cropped_height;
		}
		if($resample_params['src_y'] < 0){
			$resample_params['src_y'] = 0;
		}
	} else {
		$resample_params['dst_y'] = floor(($height - $source_height) * 0.5);
		$resample_params['src_y'] = 0;
		$resample_params['dst_height'] = $source_height;
	}
	imagecopyresampled($resized_image, $image, $resample_params['dst_x'], $resample_params['dst_y'], $resample_params['src_x'], $resample_params['src_y'], $resample_params['dst_width'], $resample_params['dst_height'], $resample_params['src_width'], $resample_params['src_height']);

	image_save($resized_image, $resized_file_path);
	@imagedestroy($resized_image);
	@imagedestroy($image);

	return $resized_file_path;

}

/**
 * Delete resized and alternate-format versions of images
 * @param int $cat_id [optional] The category ID to delete images for
 * @param string $file_name [optional] The file name to delete images for
 * @return undefined
 */
function media_regenerate(int $cat_id = 0, string $file_name = ''){
	$path = sprintf(
		'%1$s/cat-%2$s/resized/%3$s_%4$s_*_*.*', 
		__UPLOADS_PATH__,
		$cat_id === 0 ? '*' : $cat_id,
		$file_name === '' ? '*' : pathinfo($file_name, PATHINFO_FILENAME),
		$file_name === '' ? '*' : pathinfo($file_name, PATHINFO_EXTENSION)
	);
	foreach(glob($path) as $file){
		unlink($file);
	}
}

/**
 * Configure GD image for saving or output
 * @param GdImage $image [required] The image to configure
 * @param string $file_name [required] The name of the file
 */
function image_configure(GdImage &$image, string $file_name){

	$file_ext = pathinfo($file_name, PATHINFO_EXTENSION);

	@imagesetinterpolation($image, IMG_HERMITE);
	@imagealphablending($image, ($file_ext === 'png' || $file_ext === 'webp'));
	@imagesavealpha($image, ($file_ext === 'png' || $file_ext === 'webp'));
	@imageinterlace($image, true);

}

/**
 * Save an image to a file
 * @param GdImage $image [required] The image to save
 * @param string $file_path [required] The path to save the image to
 * @param int $quality [optional] The quality of the image (0-100)
 */
function image_save(GdImage &$image, string $file_path, int $quality = 85){

	image_configure($image, $file_path);
	$file_ext = pathinfo($file_path, PATHINFO_EXTENSION);

	switch($file_ext){
		case 'jpg':
		case 'jpeg':
			imagejpeg($image, $file_path, $quality);
			break;
		case 'png':
			$shrunked = shrink_png($image);
			if($shrunked){
				file_put_contents($file_path, $shrunked);
			} else {
				$quality = 9;
				imagepng($image, $file_path, $quality);
			}
			break;
		case 'webp':
			imagewebp($image, $file_path, $quality);
			break;
		case 'avif':
			imageavif($image, $file_path, $quality);
			break;
		case 'gif':
			imagegif($image, $file_path);
			break;
	}

}

/**
 * Return a GD image from a file
 * @param string $file_path [required] The path to the file
 * @return GdImage
 */
function image_from_file(string $file_path){

	$file_ext = pathinfo($file_path, PATHINFO_EXTENSION);

	$image = false;

	switch($file_ext){
		case 'jpg':
		case 'jpeg':
			$image = imagecreatefromjpeg($file_path);
			break;
		case 'png':
			$image = imagecreatefrompng($file_path);
			break;
		case 'webp':
			$image = imagecreatefromwebp($file_path);
			break;
		case 'avif':
			$image = imagecreatefromavif($file_path);
			break;
		case 'gif':
			$image = imagecreatefromgif($file_path);
			break;
	}

	return $image;

}

/**
 * Get binary data for a PNG image
 * @param GdImage $image [required] The image to get the data from
 * @return string
 */
function png_binary_get(GdImage &$image){
	ob_start();
	imagepng($image, null, 9);
	$image_data = ob_get_clean();
	if($image_data === false){
		return null;
	}
	return $image_data;
}


/**
 * Make a TinyPNG API request
 * @param string $endpoint [required] The API endpoint to call
 * @param string $method [optional] The HTTP method to use
 * @param string $data [optional] The data to send
 */
function tinypng_request(string $endpoint = '', string $method = 'POST', string $data = ''){

	$tinypng_api_key = option('tinypng_api_key');

	if(has_value($tinypng_api_key)){

		$curly = curl_init();
		curl_setopt($curly, CURLOPT_USERPWD, sprintf('api:%1$s', $tinypng_api_key));
		curl_setopt($curly, CURLOPT_URL, sprintf('https://api.tinify.com/%1$s', $endpoint));
		curl_setopt($curly, CURLOPT_RETURNTRANSFER, 1);
		if($method === 'POST'){
			curl_setopt($curly, CURLOPT_POST, 1);
			curl_setopt($curly, CURLOPT_POSTFIELDS, $data);
		}
		$result = curl_exec($curly);
		$status = curl_getinfo($curly, CURLINFO_HTTP_CODE);
		curl_close($curly);

		if(has_value($result)){
			if($status >= 200 && $status <= 299){
				return json_decode($result, true);
			}
		}

	}

	return false;

}

/**
 * Send a PNG image to TinyPNG for shrinking
 * @param GdImage $image [required] The image to get the data from
 * @return string
 */
function shrink_png(GdImage $image){

	$image_data = png_binary_get($image);

	if(!has_value($image_data)){
		return null;
	}

	$shrunked = tinypng_request('shrink', 'POST', $image_data);
	
	if($shrunked !== false){

		$location = $shrunked['output']['url'];

		$curly = curl_init();
		curl_setopt($curly, CURLOPT_URL, $location);
		curl_setopt($curly, CURLOPT_HEADER, 0);
		curl_setopt($curly, CURLOPT_RETURNTRANSFER, 1);
		$response = curl_exec($curly);
		curl_close($curly);
		return $response;
	}

	return '';

}

?>