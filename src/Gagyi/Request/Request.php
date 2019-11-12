<?php

namespace Saettem\Gomba\Request;

use Saettem\Gomba\Support\Support;
use Saettem\Gomba\Tarolas\Tarolas;

class Request
{



	/**
	 * Instantiate a new request with all post data.
	 */
	public function __construct()
	{

		$_POST = Support::sanitize($_POST);
		$_GET = Support::sanitize($_GET);

		foreach ($_POST as $key => $parameter) {
			$this->$key = $parameter;
		}


		/**
		 * GET data will override post data?
		 	*/
		foreach ($_GET as $key => $parameter) {
			$this->$key = $parameter;
		}

	}



	public static function has($key) {
		if (! isset($_SESSION[$key])) return;
		return $_SESSION[$key];
	}

	public static function get($key) {
		if (! isset($_SESSION[$key])) return;
		$value = $_SESSION[$key];
		static::destroySessionKey($key);
		return $value;
	}

	public static function destroySessionKey($key) {
		unset($_SESSION[$key]);
	}

}
