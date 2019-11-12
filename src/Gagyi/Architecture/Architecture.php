<?php

namespace Gagyi\Architecture;

use Gagyi\Base;
use Gagyi\Routes\Router;
use Gagyi\Request\Request;
use Gagyi\Support\Support;
use Gagyi\Architecture\Config;

class Architecture extends Base
{

	protected $configuration;
	protected $router;

	public function __construct() {
		$this->boot();
	}



	/**
	 * Boot Gagyi
	 * @return void
	 */
	protected function boot() {
		if (Request::has('user_id')) {
			// global $baseDir;
			// include_once($baseDir . '/Gold/User.php');
			// include_once($baseDir . '/Gold/Target.php');
		}

		/* Load configuration. */
		$this->configuration = new Config('.config.php');
		Support::$config = $this->configuration->settings;
		$this->configuration->run();

		/* Load the router. */
		$this->router = new Router();
	}



	/**
	 * Run Gagyi
	 * @return void
	 */
	public function run() {
		/* Declare global variables. */
		global $completion_time_float;
		$completion_time_float = microtime(true);
		$_POST['execution_time'] = $completion_time_float - $_SERVER['REQUEST_TIME_FLOAT'];

		/* Run the router functions. */
		$this->output($this->router->run($this));
	}



	/**
	 *
	 * Output to client
	 * @param  string $input
	 *
	 */
	protected function output($input = null) {
		echo $input;
	}
}
