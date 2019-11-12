<?php

namespace Gagyi\Collection;

use Gagyi\Support\Support;

class Collection implements \ArrayAccess, \Iterator, \Countable {
	private $items = [];
	private $position = 0;
	protected $sortkey;



	public function __construct(array $items) {
		$this->items = $items;
		$this->position = 0;
	}



	public function offsetSet($offset, $value) {
		if (is_null($offset)) { $this->items[] = $value; }
		else { $this->items[$offset] = $value; }
	}

	public function offsetExists($offset) {
		return isset($this->items[$offset]);
	}

	public function offsetUnset($offset) {
		unset($this->items[$offset]);
	}

	public function offsetGet($offset) {
		return isset($this->items[$offset]) ? $this->items[$offset] : null;
	}

	public function current() {
		return $this->items[$this->position];
	}

	public function key() {
		return $this->position;
	}

	public function rewind() {
		$this->position = 0;
	}

	public function next() {
		++$this->position;
	}

	public function valid() {
		return isset($this->items[$this->position]);
	}

	public function count() {
		return count($this->items);
	}


	public function __get($variable) {
		// If we only have 1 item
		if (count($this->items) == 1) {
			return $this->items[0]->$variable;
		}

		if (isset($this->items[$variable])) {
			return $this->items[$variable];
		}

	}


	public function get() {
		// if ($this->count() === 1) {
			return Support::array_breakout($this->items);
		// }
	}


	public function filter(\Closure $closure)
	{
		return $this->items;
		//return array_filter($this->items, $closure);
	}




	/*
	 * Sort the collection
	 */
	public function sortBy($keyOrArray, String $order = 'asc')
	{



		/*
		 * If this is not an array, make it one.
		 */
		if (!is_array($keyOrArray)) {
			$arraykey[] = $keyOrArray;
		}
		else {
			$arraykey = $keyOrArray;
		}


		$collection = new Collection($this->items);

		foreach($arraykey as $key => $value) {
			if (!$key) {
				$key = $value;
				$value = 'asc';
			}
			$collection = $this->sortByKey($key, $order);
		}
		return $collection;
	}




	public function sortByKey(String $key, String $order = 'asc')
	{
		usort($this->items, function($a, $b) use ($key, $order) {
			if (is_numeric($a->$key)) {
				if ($order == 'asc') {
					return ($a->$key <=> $b->$key);
				}
				return ($b->$key <=> $a->$key);
			}
			return strcmp($a->$key, $b->$key);
		});
		return (new Collection($this->items));
	}

	public function toArray()
	{
		return $this->items;
	}


	public function find($primaryKey, $key = 'id') {
		if ($key === null) { $key = 'id'; }
		return Support::array_breakout(array_filter($this->items, function($item) use ($primaryKey, $key) {
			return $item->$key == $primaryKey;
		}));

	}

	public function sum(String $key) {
		$totalsum = 0;
		foreach ($this->items as $item) {
			$totalsum += $item->$key;
		}
		return $totalsum;
	}

	public function totalcount(String $key) {
		$totalcount = 0;
		foreach ($this->items as $item) {
			if ($item->$key) {
				$totalcount++;
			}
		}
		return $totalcount;
	}



	public function product(String $keyA, String $keyB) {
		$totalproduct = 0;
		foreach ($this->items as $item) {
			$totalproduct += $item->$keyA * $item->$keyB;
		}
		return $totalproduct;
	}



	public function div(String $keyA, String $keyB) {
		$totalA = 0;
		$totalB = 1;
		foreach ($this->items as $item) {
			$totalA += $item->$keyA;
			$totalB += $item->$keyB;
		}
		return $totalA/$totalB;

	}

	public function select(string $key): Collection {
		$newSelection = [];
		foreach ($this->items as $value) {
			$add = true;
			foreach ($newSelection as $ns) { if ($ns === $value->$key) $add = false; }
			if ($add) $newSelection[] = $value->$key;
		}
		return new static($newSelection);
	}



	// Morph a collection of ids into their model.
	// For example $newcollection = $collection->morph('Agent');
	public function morph($model) {
		global $baseDir;
		include_once("{$baseDir}/app/{$model}.php");
		$model = 'Saettem\\app\\' . $model;
		$newmodel = new $model;
		$newitems = [];

		// terrible approach, one sql query per line 😡
		foreach ($this->items as $item) {
			$newitems[] = $newmodel->idFind($item)->get();
		}



		return new static($newitems);
	}

}
