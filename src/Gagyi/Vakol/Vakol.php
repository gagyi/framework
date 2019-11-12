<?php

namespace Saettem\Gomba\Vakol;

use Saettem\Gomba\Vakol\ViewsTrait;



class Vakol
{
	use ViewsTrait;
	public static $views = [];



	/**
	 * How many views
	 * @return int the count
	 */
	public static function viewCount(): int
	{
		return count(static::$views);
	}
}
