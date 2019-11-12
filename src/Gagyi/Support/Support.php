<?php

namespace Saettem\Gomba\Support;

use Saettem\Gomba\Auth\Auth;
use Saettem\Gomba\Tarolas\Tarolas;

class Support
{
	public static $config = [];



	/**
	 * Remove characters from the right side of a string
	 *
	 * @param  string $string The string to be scrubbed
	 * @param  mixed $scrub   Strings to be string
	 * @return string
	 *
	 */
	static function str_scrub_right(string $string, $scrub)
	{

		$scrubArray = static::arrayify($scrub);

		foreach ($scrubArray as $scrub) {
			$length = strlen($scrub);
			if (substr($string, -$length) == $scrub) {
				$string = substr($string, 0, -$length);
			}
		}
		return (string) $string;
	}



	/**
	 * Return an array if a string is supplied.
	 *
	 * @param mixed $stringOrArray
	 * @return array
	 *
	 */
	static protected function arrayify($stringOrArray)
	{
		if (is_string($stringOrArray)) {
			$array[] = $stringOrArray;
		}
		else {
			$array = $stringOrArray;
		}

		return $array;
	}




	/**
	 * Die and dump
	 * @param  string $text
	 * @return void
	 */
	static function dd($text)
	{
		var_dump($text); die();
	}



	/**
	 * Dump
	 * @param  string $text
	 * @return void
	 */
	static function d($text)
	{
		var_dump($text);
		echo "<br>";
	}



	public static function regex_assoc($matches)
	{
		$rm = [];
		for($x = 0; $x < count($matches[1]); $x++) {
			$rm[$matches[0][$x]] = $matches[1][$x];
		}
		return $rm;
	}


	public static function array_breakout (array $array)
	{
		foreach ($array as $item) {
			return $item;
		}
	}

	public static function getDate(string $dateTime)
	{
		return substr($dateTime, 0, 10);
	}

	public static function excelToUnix($time)
	{
		return (floatval($time) - 25569) * 86400;
	}

	public static function unixToExcel($time)
	{
		return 25569 + (floatval($time) / 86400);
	}



	public static function sanitize($input) {
		$output = [];
	    if (is_array($input)) {
	        foreach($input as $var=>$val) {
	            $output[$var] = static::sanitize($val);
	        }
	    }
	    else {
	        if (get_magic_quotes_gpc()) {
	            $input = stripslashes($input);
	        }
	        $input  = static::cleanInput($input);
	        //$output = mysqli_real_escape_string(Tarolas::$dbHandle, $input);
	        $output = $input;
	    }
	    return $output;
	}




	public static function cleanInput($input) {

	  $search = array(
		'@<script[^>]*?>.*?</script>@si',   // Strip out javascript
		'@<[\/\!]*?[^<>]*?>@si',            // Strip out HTML tags
		'@<style[^>]*?>.*?</style>@siU',    // Strip style tags properly
		'@<![\s\S]*?--[ \t\n\r]*>@'         // Strip multi-line comments
	  );

		$output = preg_replace($search, '', $input);
		$output = str_replace("'", "&#39;", $output);
		return $output;
	  }



	public static function default($key, $value = null, $className = null)
	{
		if ($value === null) {
			return Auth::user()->$key;
		}
		else {
		}
		return $value;
	}

}
