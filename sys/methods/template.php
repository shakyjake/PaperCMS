<?php

/**
 * Get the path for a template file - returns template from the active theme if it exists
 * @param string $template_name [required] The template filename to check
 * @return string
 */
function template_path(string $template_name){

	$template_name = trim_right($template_name, '.html');
	$template_name = trim_left($template_name, '/');

	/*
		$theme_name = option('active_theme');
		
		$template_folder = map_path(sprintf('/media/themes/%1$s%2$s', $theme_name, config_path('Template')));
		
		$template_path = sprintf('%1$s/%2$s.html', $template_folder, $template_name);
		
		if(file_exists($template_path)){
			return $template_path;
		}

	*/
	
	$template_folder = map_path(sprintf(config_path('Template')));
	
	return sprintf('%1$s/%2$s.html', $template_folder, $template_name);
}


/**
 * Does a template file exist?
 * @param string $template_name [required] The template path/filename to check
 * @return bool
 */
function template_exists(string $template_name){
	
	$template_path = template_path($template_name);
	
	return file_exists($template_path);
}

/**
 * Get the contents of an HTML template file
 * @param string $template_name [required] The template path/filename to retrieve
 * @return string
 */
function template_get(string $template_name){
	
	$template_path = template_path($template_name);
	
	if(file_exists($template_path)){
		return file_get_contents($template_path);
	}
	
	return '';

}

/**
 * Get the contents of an HTML template file in the email subdirectory, wrapped in a master email file
 * @param string $template_name [required] The template path/filename to retrieve
 * @return string
 */
function email_template_get(string $template_name){

	$root = template_get('email/root');
	if(!strlen($root)){
		$root = tag_build('EMAIL.BODY');
	}

	return tag_parse($root, 'EMAIL.BODY', template_get('email/' . $template_name));

}

/**
 * Does a string contain a tag?
 * @param string $html [required] The template contents to parse
 * @param string $tag_name [required] The tag to find 
 * @return bool
 */
function tag_exists(string $html, string $tag_name){

	$html = do_check($html, '');
	$tag = tag_build($tag_name);

	return str_contains($html, $tag);

}

/**
 * Replace a tag in an HTML template with a given value
 * @param string $html [required] The template contents to parse
 * @param string $tag_name [required] The tag to find
 * @param string $value [required] The value to replace the tag with 
 * @return string
 */
function tag_parse(?string $html = '', ?string $tag_name = '', ?string $value = ''){

	$html = do_check($html, '');
	$value = do_check($value, '');

	if(tag_exists($html, $tag_name)){
		return str_replace(tag_build($tag_name), $value, $html);
	}

	return $html;

}

/**
 * Wrap a string in the tag formatting prefix/suffix
 * @param string $tag_name [required] The unformatted tag to build
 * @return string
 */
function tag_build(string $tag_name){
	
	$tag = do_check($tag_name, 'UNDEFINED_TAG');

	if(left($tag, strlen(TAGSTART)) !== TAGSTART){
		$tag = TAGSTART . $tag;
	}

	if(right($tag, strlen(TAGEND)) !== TAGEND){
		$tag .= TAGEND;
	}

	return $tag;

}

/**
 * Parse user data
 * @param string $html [required] The html to parse
 * @return string
 */
function template_parse_user(string $html){

	global $current_user;

	$logged_in = $current_user->logged_in();

	if(tag_exists($html, 'USER.IMG')){
		if($logged_in){
			$html = tag_parse($html, 'USER.IMG', $current_user->profile_picture);
		} else {
			$html = tag_parse($html, 'USER.IMG', 'https://gravatar.com/avatar/000000000000000000000000000000000000000000000000000000');
		}
	}

	if(tag_exists($html, 'USER.NAME')){
		if($logged_in){
			$name = $current_user->data('name');
			$name = empty($name) ? $current_user->user_name : $name;
			$html = tag_parse($html, 'USER.NAME', $name);
		} else {
			$html = tag_parse($html, 'USER.NAME', 'Guest');
		}
	}

	return $html;

}

/**
 * Parse miscellaneous tags
 * @param string $html [required] The html to parse
 * @return string
 */
function template_parse_misc(string $html){

	if(tag_exists($html, 'SITE.URL')){
		$html = tag_parse($html, 'SITE.URL', config('SiteUrl'));
	}

	if(tag_exists($html, 'SITE.DOMAIN')){
		$domain = config('SiteUrl');
		$domain = trim_left($domain, 'https://');
		$domain = trim_left($domain, 'http://');
		$domain = trim_left($domain, 'www.');
		$html = tag_parse($html, 'SITE.DOMAIN', $domain);
	}

	return $html;

}

/**
 * Parse date/time tags
 * @param string $html [required] The html to parse
 * @return string
 */
function template_parse_datetime(string $html){

	if(tag_exists($html, 'DATETIME.FULL')){
		$html = tag_parse($html, 'DATETIME.FULL', date('Y-m-d H:i:s'));
	}

	if(tag_exists($html, 'DATETIME.DATE')){
		$html = tag_parse($html, 'DATETIME.DATE', date('Y-m-d'));
	}

	if(tag_exists($html, 'DATETIME.TIME')){
		$html = tag_parse($html, 'DATETIME.TIME', date('H:i:s'));
	}

	if(tag_exists($html, 'DATETIME.FOOTER')){
		$html = tag_parse($html, 'DATETIME.FOOTER', date('Y'));
	}

	if(tag_exists($html, 'DATETIME.HUMAN')){
		$html = tag_parse($html, 'DATETIME.HUMAN', date('H:i:s, jS \o\f F, Y'));
	}

	if(tag_exists($html, 'DATE.HUMAN')){
		$html = tag_parse($html, 'DATE.HUMAN', date('jS \o\f F, Y'));
	}

	if(tag_exists($html, 'DATE.YEAR')){
		$html = tag_parse($html, 'DATE.YEAR', date('Y'));
	}

	return $html;

}

/**
 * Parse tags using data from config.xml
 * @param string $html [required] The html to parse
 * @return string
 */
function template_parse_config(string $html){

	if(str_contains($html, TAGSTART . 'CONFIG(')){

		$matches = regex_match(regex_escape(TAGSTART) . 'CONFIG\([\'"]?([A-Za-z0-9\.\/]*)[\'"]?\)' . regex_escape(TAGEND), $html);

		foreach($matches as $match){
			$html = str_replace($match[0], config($match[1]), $html);
		}

	}

	return $html;

}

/**
 * Insert a template file from the "partials" folder
 * @param string $html [required] The html to parse
 * @return string
 */
function template_parse_partials(string $html){

	if(str_contains($html, TAGSTART . 'PARTIAL(')){

		$matches = regex_match(regex_escape(TAGSTART) . 'PARTIAL\([\'"]?([A-Za-z0-9\.\/]*)[\'"]?\)' . regex_escape(TAGEND), $html);

		foreach($matches as $match){
			$html = str_replace($match[0], partial_get($match[1]), $html);
		}

	}

	return $html;

}

/**
 * Parse tags using data from an associative array
 * @param string $html [required] The html to parse
 * @param string $tag_name [required] The tag to find
 * @param array $data [required] The data to replace the tag with
 * @return string
 */
function template_parse_assoc(string $html, string $tag_name, array $data){

	$parsed = [];

	$loops = 0;

	$tag_name = strtoupper($tag_name);

	while(str_contains($html, TAGSTART . $tag_name . '(')){

		$loops += 1;
		if($loops > 10){
			throw new Exception(sprintf(
				'Infinite recursion occurred while parsing an associated array for %1$s tags. Parsed: %2$s',
				$tag_name,
				implode(', ', array_keys($parsed))
			));
		}

		$matches = regex_match(regex_escape(TAGSTART . $tag_name) . '\([\'"]?([\da-z\/\.\-\_]*)[\'"]?\)' . regex_escape(TAGEND), $html);

		foreach($matches as $match){

			if(isset($data[$match[1]])){

				if(array_key_exists($match[1], $parsed)){/* prevent infinite recursion */
					$html = str_replace($match[0], '', $html);
				} else {
					$html = str_replace($match[0], $data[$match[1]], $html);
				}

			} else {
				$html = str_replace($match[0], '', $html);
			}

			$parsed[$match[1]] = true;/* prevent infinite recursion */
		
		}

	}

	return $html;

}

/**
 * Return the contents of an HTML file in the partials subdirectory of templates
 * @param string $name [required] The name of the file to return
 * @return string
 */
function partial_get(string $name){
	return template_get('partials/' . $name);
}

?>