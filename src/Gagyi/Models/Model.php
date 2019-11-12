<?php


namespace Gagyi\Models;

use Carbon\Carbon;
use Gagyi\Auth\Auth;
use Gagyi\Tarolas\Tarolas;
use Gagyi\Support\Support;
use Gagyi\Collection\Collection;



abstract class Model
{

 	public $_ORIGINAL = null;


	/**
	 * Which database column is the primary?
	 * @var string
	 */
	protected static $primaryKey = 'id';



	/**
	 * What is the name of the database table?
	 * @var string
	 */
	protected static $table = '';



	/**
	 * Sorting order
	 * @var array
	 */
	protected static $orderBy = [];


	/**
	 * Query as set by order function.
	 * @var [type]
	 */
	protected static $orderByQuery = "";


	/**
	 * Default limit for how many records to get
	 * @var [type]
	 */
	public static $limit = 1000;



	protected static $ignoreKeys = ['_ORIGINAL'];




	public function __construct() {
		// $this->moo = true;
	}





	/**
	 * All results
	 *
	 * @return Collection
	 *
	 */
	public static function all() : Collection
	{
		return static::query();
	}



	/**
	 * DB Query
	 *
	 * @param  string     $query
	 * @return Collection
	 *
	 */
	protected static function query(string $query = null)
	{
		if ($query == null) {
			$query = "SELECT * FROM `" . static::$table . "` " . static::getOrderBy() . " LIMIT " . static::$limit;
		}
		// var_dump($query);
		return Tarolas::query($query, static::class);
	}



	/**
	 * Find 1 instance
	 *
	 * @param  int    $id
	 * @return Object
	 *
	 */
	public static function find($primaryKey) : Object
	{
		$query = "SELECT * FROM `". static::$table . "` WHERE `" . static::$primaryKey . "` = '" . $primaryKey . "' LIMIT 1";
		return static::query($query);
	}



	public static function idFind($key, $primaryKey = 'id') : Object
	{
		$query = "SELECT * FROM `". static::$table . "` WHERE `{$primaryKey}` = '" . $key . "' LIMIT 1";
		return static::query($query);
	}



	public static function where(String $column, String $operatorOrValue, String $value = null, $more = null, $selects = null, $limit = null) {
		if ($value != null) {
			$operator = $operatorOrValue;
		}
		else {
			$operator = '=';
			$value = $operatorOrValue;
		}

		if (!$selects) {
			$selects = "*";
		}

		if ($limit) {
			$limit = " LIMIT $limit";
		}

		$results = static::query("SELECT {$selects} FROM `" . static::$table . "` WHERE `{$column}` {$operator} '{$value}' {$more} " . static::getOrderBy() . $limit);

		if ($results == null) return false;
		return $results;
	}

	public static function whereNull(String $column, $more = null, $selects = "*", $limit = null) {
		if ($limit) { $limit = " LIMIT $limit"; }
		$results = static::query("SELECT {$selects} FROM `" . static::$table . "` WHERE `{$column}` IS NULL {$more} " . static::getOrderBy() . $limit);
		if ($results == null) return false;
		return $results;
	}

	public static function whereNotNull(String $column, $more = null, $selects = "*", $limit = null) {
		if ($limit) { $limit = " LIMIT $limit"; }
		$results = static::query("SELECT {$selects} FROM `" . static::$table . "` WHERE `{$column}` IS NOT NULL {$more} " . static::getOrderBy() . $limit);
		if ($results == null) return false;
		return $results;
	}


	/**
	 * Same as where, just returns a count.
	 * @param  String     $column          [description]
	 * @param  String     $operatorOrValue [description]
	 * @param  [type]     $value           [description]
	 * @param  [type]     $more            [description]
	 * @return Collection                  [description]
	 */
	public static function count(String $column, String $operatorOrValue, String $value = null, $more = null)
	{
		if ($value !== null) {
			$operator = $operatorOrValue;
		}
		else {
			$operator = '=';
			$value = $operatorOrValue;
		}


		$query = "SELECT count(*) as count FROM `" . static::$table . "` WHERE `{$column}` {$operator} '{$value}' {$more} " . static::getOrderBy();

		$results = static::query($query);

		if ($results == null) return false;
		return $results->count;
	}





	public static final function getOrderBy() {
		if (static::$orderBy == null) return;

		static::$orderByQuery = "";

		$orderQuery = [];

		foreach(static::$orderBy as $order => $method) {
			array_push($orderQuery, "`{$order}` {$method}");
		}

		static::$orderByQuery = implode(", ", $orderQuery);
		static::$orderByQuery = " ORDER BY " . static::$orderByQuery;


		return static::$orderByQuery;
	}



	public function keyIgnored(string $prop) : bool
	{
		return (bool) array_filter(static::$ignoreKeys, function($key) use ($prop) {
			return $key == $prop;
		});
		return false;
	}




	public function delete()
	{
		// If this model doesn't have an id, return.
		if (!isset($this->{static::$primaryKey})) {
			return;
		}

		$query = "DELETE FROM `" . static::$table . "` WHERE `" . static::$primaryKey . "` = '" . $this->{static::$primaryKey} . "' LIMIT 1";
		return Tarolas::query($query);
	}


	public function save($updateTimeStamp = true, $continueOnError = false)
	{

		$propvalues = [];

		/* Does the model exist? */
		if (isset($this->{static::$primaryKey})) {
			$query = "UPDATE `" . static::$table . "` SET ";

			foreach($this as $prop => $value) {
				if (!$this->keyIgnored($prop)) {
					// echo "<pre>";
					// var_dump($prop, $this->_ORIGINAL[$prop], $value);
					if ((!is_object($value)) &&	($this->_ORIGINAL[$prop] != $value)) {
						$propvaluePair = "`{$prop}` = ";

						if ($value === false) $propvaluePair .= "'0'";
						else if ($value === true) $propvaluePair .= "'1'";
						else if ($value == null) $propvaluePair .= 'NULL';
						else {

							// Special treatment for updated_at
							if (($prop == 'updated_at') && ($updateTimeStamp)) {
								$value = Carbon::now();
							}

							$value = Support::sanitize($value);
							$propvaluePair .= "'{$value}'";
						}
						$propvalues[] = $propvaluePair;
					}
				}
			}
			$propvalues_string = implode($propvalues, ', ');
			if ($propvalues_string) {
				$query .= $propvalues_string;
				$query .= " WHERE `" . static::$primaryKey . "` = '{$this->{static::$primaryKey}}' LIMIT 1";
				$results = Tarolas::query($query, null, $continueOnError);
			}
		}
		else {
			$preQuery = "INSERT INTO ";
			$this->updated_at = $this->created_at = date('Y-m-d H:i:s');
			foreach($this as $prop => $value) {
				if (!$this->keyIgnored($prop) && !is_object($value)) {
					$props[] = "`{$prop}`";
					if ($value === null) {
						$values[] = 'NULL';
					}
					else {
						$values[] = "'{$value}'";
					}
				}
			}
			if (!isset($props)) {
				throw new \Exception("Object " . static::class . " has no properties.", 1);

			}
			$props_string = implode($props, ',');
			$values_string = implode($values, ',');

			$postQuery = "({$props_string}) VALUES({$values_string})";
			$preQuery .= "`" . static::$table . "`";
			$fullQuery = "{$preQuery} {$postQuery}";
			$results = Tarolas::query($fullQuery, null, $continueOnError);

			// What's the ID now?
			// 1 are we using standard id with auto increments?
			if (static::$primaryKey === 'id') {
				$this->id = Tarolas::query("SELECT `id` FROM `" . static::$table . "` WHERE `created_at` = '{$this->created_at}' LIMIT 1", static::class)->get()->id;
			}

		}
		return $this;
	}



	public static function findOrCreate($arguments)
	{
		foreach ($arguments as $key => $argument) {
			$filters[] = "`{$key}`='{$argument}'";
		}
		$filter = implode(" AND ", $filters);

		$query = "SELECT * FROM `" . static::$table . "` WHERE {$filter} LIMIT 1";
		$model = static::query($query)->get();

		// If there was no existing model, create a new one.
		if (!($model)) {
			$model = new static;
			foreach ($arguments as $key => $argument) {
				$model->$key = $argument;
			}
		}
		return $model;
	}


	public static function has(string $property): bool {
		return (bool) (static::$$property);
	}


}
