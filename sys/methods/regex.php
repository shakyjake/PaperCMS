<?php

/**
 * Returns a boolean value indicating whether the subject string matches the pattern.
 * @param string $pattern [required] A valid regex pattern
 * @param string $subject [required] The string to be tested
 * @return bool
*/
function regex_test(string $pattern = '', string $subject = ''){

	if(!has_value($pattern)){
		return true;
	}

	if(!has_value($subject)){
		return false;
	}

	$matched = preg_match('/' . $pattern . '/', $subject);

	if($matched === 1){
		return true;
	}

	if($matched === false){
		/* PHP continues execution on error here for some stupid reason */
		throw new \Exception('Regex pattern is not a valid Regex: ' . $pattern);
	}

	return false;

}

/**
 * Escapes a string for use in a regular expression.
 * @param string $value [required] The string to be escaped
 * @return string
*/
function regex_escape(string $value = ''){
	return preg_quote($value, '/');
}

/**
 * Performs a regular expression search and replace on the subject string.
 * @param string $pattern [required] A valid regex pattern
 * @param string $replacement [required] The replacement string
 * @param string $subject [required] The string to be tested
 * @return string
*/
function regex_replace(string $pattern = '', string $replacement = '', string $subject = ''){

	if(!has_value($subject)){
		return '';
	}

	if(!has_value($pattern)){
		return $subject;
	}
	
	$matches = regex_match($pattern, $subject);
	
	foreach($matches as $match){
		$full_match = array_shift($match);
		if(str_contains($subject, $full_match)){
			$replacement_single = $replacement;
			foreach($match as $index => $sub_match){
				$replacement_single = str_replace('$' . $index + 1, $sub_match, $replacement_single);
			}
			$subject = str_replace($full_match, $replacement_single, $subject);
		}
	}

	return $subject;

}

/**
 * Returns an array of matches found in the subject string.
 * @param string $pattern [required] A valid regex pattern
 * @param string $subject [required] The string to be tested
 * @return array
*/
function regex_match(string $pattern = '', string $subject = ''){

	if(!has_value($subject)){
		return [];
	}

	if(!has_value($pattern)){
		return [];
	}

	$matches = [];

	$matched = preg_match_all('/' . $pattern . '/', $subject, $matches, PREG_SET_ORDER);

	if($matched === false){
		/* PHP continues execution on error here for some stupid reason */
		throw new \Exception('Regex pattern is not a valid Regex: ' . $pattern);
	}

	return $matches;

}

?>