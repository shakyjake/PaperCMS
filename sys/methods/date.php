<?php

	/**
	 * Try to obtain a DateTime object from a string
	 * @param string $date_string [required] The string to attempt to parse
	 * @return DateTime
	 */
	function date_from_string(string $date_string) : ?DateTime {

		if(empty($date_string)){
			return null;
		}

		$date = false;

		if(regex_test('^\d{4}\-\d{2}\-\d{2}$', $date_string)){
			$date = DateTime::createFromFormat('Y-m-d', $date_string);
		}
		if(regex_test('^\d{4}\-\d{2}\-\d{2} \d{2}\:\d{2}\:\d{2}$', $date_string)){
			$date = DateTime::createFromFormat('Y-m-d H:i:s', $date_string);
		}
		if(regex_test('^\d{2}\/\d{2}\/\d{4}$', $date_string)){
			$date = DateTime::createFromFormat('d/m/Y', $date_string);
		}
		if(regex_test('^\d{2}\/\d{2}\/\d{4} \d{2}\:\d{2}\:\d{2}$', $date_string)){
			$date = DateTime::createFromFormat('d/m/Y H:i:s', $date_string);
		}
		if(regex_test('^[A-Za-z]{3}\, \d{2} [A-Za-z]{3} \d{4} \d{2}\:\d{2}\:\d{2} \+|\-\d{4}', $date_string)){
			$date = DateTime::createFromFormat('D, d M Y H:i:s O', $date_string);
		}
		if(regex_test('^\d{2} [A-Za-z]{3} \d{4} \d{2}\:\d{2}\:\d{2} \+|\-\d{4}', $date_string)){
			$date = DateTime::createFromFormat('d M Y H:i:s O', $date_string);
		}
		/*
			Since users think computers are Magic, they expect your website to understand whatever idiotic nonsense they enter.
			As such, we should also check for a 2-digit year and hope that whatever century it spits out is the correct one. 
		*/
		if(regex_test('^\d{2}\-\d{2}\-\d{2}$', $date_string)){
			$date = DateTime::createFromFormat('y-m-d', $date_string);
		}
		if(regex_test('^\d{2}\/\d{2}\/\d{2}$', $date_string)){
			$date = DateTime::createFromFormat('d/m/y', $date_string);
		}

		if($date === false){
			return null;
		}

		$date->setTimezone(new DateTimeZone('UTC'));

		return $date;
	}

	/**
	 * Obtain a yyyy-mm-dd string from a DateTime instance
	 * @param DateTime $date_object [required] The DateTime to be parsed
	 * @return string
	 */
	function date_std(?DateTime $date_object) : string {
		
		if(empty($date_object)){
			return '';
		}

		return date_format($date_object, 'Y-m-d');

	}

	/**
	 * Obtain a yyyy-mm-dd hh:ii:ss string from a DateTime instance
	 * @param DateTime $date_object [required] The DateTime to be parsed
	 * @return string
	 */
	function datetime_std(?DateTime $date_object) : string {
		
		if(empty($date_object)){
			return '';
		}

		return date_format($date_object, 'Y-m-d H:i:s');

	}

	/**
	 * Obtain a SQL Server friendly date string from a DateTime instance
	 * @param DateTime $date_object [required] The DateTime to be parsed
	 * @return string
	 */
	function date_db(?DateTime $date_object) : string {
		
		if(empty($date_object)){
			return '';
		}

		return date_format($date_object, 'd/m/Y');

	}

	/**
	 * Obtain a SQL Server friendly datetime string from a DateTime instance
	 * @param DateTime $date_object [required] The DateTime to be parsed
	 * @return string
	 */
	function datetime_db(?DateTime $date_object) : string {
		
		if(empty($date_object)){
			return '';
		}

		return date_format($date_object, 'd/m/Y H:i:s');

	}

	/**
	 * Return a string indicating how long ago the given DateTime was
	 * @param DateTime $date_object [required] The DateTime to be diffed
	 * @return string
	 */
	function time_ago(?DateTime $date_object) : string {

		if(empty($date_object)){
			return '';
		}
	
		$diff = date_diff($date_object, new DateTime(), true);
		
		/*
			bit of fuzzy logic:
			 - round years cos fine-grained detail at that point is mostly worthless
			 - use months up to 18 months, unless it's 1 year exactly
		*/
		if($diff->y > 1){
			if($diff->m > 6){
				return (($diff->y + 1) . ' years ago');
			}
			return ($diff->y . ' years ago');
		}
		if($diff->y === 1){
			if($diff->m === 0){
				return '1 year ago';
			}
			if($diff->m <= 6){
				return (($diff->y + 12) . ' months ago');
			}
			return '2 years ago';
		}
		if($diff->m > 1){
			return ($diff->m . ' months ago');
		}
		if($diff->m === 1){
			return '1 month ago';
		}
		if($diff->d > 1){
			return ($diff->d . ' days ago');
		}
		if($diff->d === 1){
			return '1 day ago';
		}
		if($diff->h > 1){
			return ($diff->h . ' hours ago');
		}
		if($diff->h === 1){
			return '1 hour ago';
		}
		if($diff->i > 1){
			return ($diff->i . ' minutes ago');
		}
		if($diff->i === 1){
			return '1 minute ago';
		}
		if($diff->s > 1){
			return ($diff->s . ' seconds ago');
		}
		return '1 second ago';
		
	}

	/**
	 * Obtain a human-readable date string
	 * @param DateTime $date_object [required] The DateTime to be parsed
	 * @return string
	 */
	function human_date(?DateTime $date_object) : string {
		
		if(empty($date_object)){
			return '';
		}
		
		return date_format($date_object, 'jS F, Y');
	
	}

	/**
	 * Obtain a human-readable time string
	 * @param DateTime $date_object [required] The DateTime to be parsed
	 * @return string
	 */
	function human_time(DateTime $date_object) : string {
		
		if(empty($date_object)){
			return '';
		}
		
		if(date_format($date_object, 'H:i') === '00:00'){
			return 'Midnight';
		}
		if(date_format($date_object, 'H:i') === '12:00'){
			return 'Noon';
		}
		return date_format($date_object, 'h:ia');
	
	}

	/**
	 * Obtain a human-readable date and time
	 * @param DateTime $date_object [required] The DateTime to be parsed
	 * @return string
	 */
	function human_date_time(DateTime $date_object) : string {
		
		if(empty($date_object)){
			return '';
		}
		
		return date_format($date_object, 'jS F, Y @ h:ia');
	
	}

?>