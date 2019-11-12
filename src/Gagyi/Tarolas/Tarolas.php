<?php

namespace Saettem\Gomba\Tarolas;

use Saettem\Gomba\Support\Support;
use Saettem\Gomba\Collection\Collection;

class Tarolas
{

	protected static $query;
	public static $queries = [];
	public static $dbHandle;
	public static $highlightKeywords = [
		'SELECT ', 'FROM ', 'WHERE ', 'LIMIT ',
		'DESC', 'AND ', 'GROUP BY ', 'ORDER BY ',
		'IS NOT NULL', 'SUM(', 'ASC', 'AS ',
		'IN(', 'IN ', 'IS NULL', 'UPDATE ', ' SET ',
		'COUNT('
	];
	public static $highlightOperators = [
		' = ', ' >= ', '`', ">='", '* ', '(', ')', ' > ', ' < ', ' <= '
	];

	/**
	 *
	 * Return DB error
	 *
	 * @return string
	 *
	 */
	public static function error()
	{
		return (string) static::$dbHandle->error;
	}


	/**
	 * Database query
	 *
	 * @param  string $query     Raw SQL query
	 * @param  class $className  Optional classname
	 * @return Collection
	 *
	 */
	public static function query(string $query, $className = null, $continueOnError = false) {

		// Initialize objects array
		$objects = [];

		// Add query to query array
		static::$queries[] = $query;

		// Get results from query.
		$results = static::rawQuery($query);

		if (!$results) {
			echo "<hr>";
			echo $query . '<br>';
			echo $className . "<br>";
			echo static::error() . '<hr>';
			if (!$continueOnError) {
				die();
			}
		}

		if (is_bool($results)) {
			return true;
		}

		// TODO: Catch errors
		while($row = $results->fetch_object($className)) {
			$array = [];
			foreach ($row as $key => $value) {
				$array[$key] = $value;
			}
			$row->_ORIGINAL = $array;
			$objects[] = $row;
		}

		return new Collection($objects);
	}



	/**
	 * Raw DB query
	 *
	 * @param  string $query
	 * @return db_query
	 *
	 */
	public static function rawQuery(string $query)
	{
		if (static::$dbHandle == null) {
			static::dbConnect();
		}

		return static::$dbHandle->query($query);
	}



	/**
	 * Connect to DB
	 * @return void
	 */
	protected static function dbConnect()
	{
		static::$dbHandle = new \mysqli(
			Support::$config['db_server'],
			Support::$config['db_username'],
			Support::$config['db_password'],
			Support::$config['db_database']
		);
	}

	public static function showQueries() {
		echo "<table class='table counter bg-white text-blue-400 p-0'>";
		echo "<thead><tr><th>Query</th></tr></thead>";
		foreach (static::$queries as $query) {
			echo "<tr class='p-0 m-0'>\n\t<td class='left p-0 m-0'>" . static::highlight($query) . "</td>\n</tr>\n";
		}
		echo "</table>";
	}

	public static function countQueries() {
		return count(static::$queries);
	}



	public static function highlight(string $input): string {
		$output = $input;

		// Strings
		$output = preg_replace("/'(.+?)'/i", "'<span class='highlight-string'>\${1}</span>'", $output);

		// Any identifiers such as keys.
		$output = preg_replace('/`(\w+)`/i', "`<span class='highlight-identifier'>\${1}</span>`", $output);

		// Keywords
		foreach (static::$highlightKeywords as $keyword) {
			$output = str_ireplace($keyword, "<span class='highlight-keyword'>$keyword</span>", $output);
		}

		// Operators
		foreach (static::$highlightOperators as $operator) {
			$output = str_ireplace($operator, "<span class='highlight-operator'>$operator</span>", $output);
		}

		return $output;
	}
}
