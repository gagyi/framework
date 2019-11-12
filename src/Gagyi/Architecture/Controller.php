<?php


namespace Gagyi\Architecture;

use Gagyi\Base;
use Gagyi\Support;
use Gagyi\Architecture\Architecture;


class Controller extends Base {
	protected $name;
	protected $method;
	protected $controller;
	protected $prefix = '\Saettem\\Gold\\Controllers\\';
	protected $dirPrefix = 'Gold/Controllers/';



	/**
	 * New controller instance.
	 *
	 * @param string $controllerText Format: 'ControllerName/method'
	 *
	 */
	public function __construct(string $controllerText)
	{
		// TODO: check for no / in controllerText and throw exception.
		[$this->name, $this->method] = explode('/', $controllerText);
		return $this->boot();
	}



	/**
	 * Boot the controller
	 *
	 * @return Controller
	 *
	 */
	protected function boot()
	{
		$this->load();
		return $this->getController();
	}



	/**
	 * Get a controller of the type specified.
	 *
	 * @return Controller A controller of the type specified.
	 *
	 */
	protected function getController() {
		$controller = $this->prefix . $this->name;
		$this->controller = new $controller();
		return $this->controller;
	}



	/**
	 * Load controller
	 *
	 * @return void
	 *
	 */
	public function load()
	{
		// TODO: avoid making baseDir global.
		global $baseDir;

		// Attempt to load the controller.
		// FIXME: what happens if require fails?
		require_once($baseDir . '/' . $this->dirPrefix . $this->name . '.php');
	}



	/**
	 * Run a controller method
	 * @param  $arguments The parameters supplied
	 * @return string The output of the controller method.
	 */
	public function run($argOne = null, $argTwo = null, $argThree = null)
	{
		$method = $this->method;
		return $this->controller->$method($argOne, $argTwo, $argThree);
	}
}
