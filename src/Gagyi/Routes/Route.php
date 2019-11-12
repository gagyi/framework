<?php

namespace Saettem\Gomba\Routes;

use Saettem\Gomba\Base;
use Saettem\Gomba\Routes\Router;
use Saettem\Gomba\Support\Support;
use Saettem\Gomba\Architecture\Controller;
use Saettem\Gomba\Architecture\Architecture;

class Route extends Base
{
	/**
	 * Optional route name.
	 * @var string
	 */
	public $name = "";


	/**
	 * Middleware object
	 * @var object
	 */
	public $middleware;



	/**
	 * The URI to match, as specified in the routes file.
	 *
	 * @var string
	 */
	public $uri;



	/**
	 *  The real URI.
	 *  @var  [type]
	 */
	public $real_uri;



	/**
	 * One of the supported methods.
	 * Currently: get, post.
	 *
	 * @var string
	 */
	public $method;



	/**
	 * If there's a closure supplied,
	 * it will be stored here.
	 *
	 * @var mixed
	 */
	protected $closure;



	/**
	 * If a controller with an action has
	 * been supplied, it will be stored here.
	 *
	 * @var string
	 */
	protected $controller;



	/**
	 * Array of the models as defined in the url by wildcards.
	 *
	 * @var array
	 */
	public $models = [];



	/**
	 * Array of the modelvalues.
	 *
	 * @var array
	 */
	public $modelValues = [];



	/**
	 * New route
	 *
	 * @param string $uri                Link to the page.
	 * @param mixed $controllerOrClosure Either a supplied controller or closure.
	 * @param string $method             Supported 'get'
	 */
	public function __construct(string $uri, $controllerOrClosure, string $method = "get")
	{
		$this->uri = $uri;
		$this->models = $this->getModels();
		$this->method = $method;
		$this->real_uri = $_SERVER['REQUEST_URI'];

		if (is_string($controllerOrClosure)) {
			$this->controller = $controllerOrClosure;
		}
		else {
			$this->closure = $controllerOrClosure;
		}
		return $this;
	}



	/**
	 * Get an array of all models requested.
	 *
	 * @return array models
	 */
	protected function getModels(): Array
	{
		preg_match_all("/\{(\w+)\}/", $this->uri, $routemodels);
		return Support::regex_assoc($routemodels);
	}



	/**
	 * Get an array of the values of the models.
	 *
	 * @return array model values
	 */
	protected function getModelValues(): Array
	{
		preg_match_all($this->regex(), Router::$uri, $matches);
		for($x = 1; $x < count($matches); $x++) {
			$this->modelValues[] = $matches[$x];
		}
		return $this->modelValues;
	}



	/**
	 * Model count
	 *
	 * @return int
	 */
	public function modelCount(): Int
	{
		return count($this->models);
	}



	/**
	 * Build the route's regex.
	 *
	 * @return string Regex to use
	 */
	public function regex(): String
	{
		$a = preg_replace("/\{(\w+)\}/", 'foo', $this->uri, -1, $count);
		$a = str_replace('/', '\/', $a);
		$a = str_replace('foo', '([a-zA-Z0-9.\-_]+)', $a);
		$a = "/$a/";
		return $a;
	}



	/**
	 * Is this route a controller or Closure?
	 *
	 * @return boolean
	 */
	public function isControllerAction(): Bool
	{
		return isset($this->controller);
	}



	/**
	 * Execute the controller function or the closure.
	 *
	 * @return string The return value of the controller/closure method
	 */
	public function run()
	{
		$this->runMiddleware();

		global $baseDir;
		$args = [];
		$args[0] = "";
		$args[1] = "";
		$args[2] = "";
		$arg = 0;
		$this->modelValues = $this->getModelValues();

		// Arguments should be the models requested.
		foreach ($this->models as $key => $model) {
			$className = 'Saettem\\Gold\\' . $model;
			include_once($baseDir . '/Gold/'. $model . '.php');
			$whatToFind = $this->modelValues[$arg][0];
			$args[$arg] = $className::find($whatToFind);
			$arg++;
		}

		if ($this->isControllerAction()) {
			$controller = new Controller($this->controller);
			return $controller->run($args[0], $args[1], $args[2]);
		}
		else {
			$closure = $this->closure;
			return $closure($args);
		}
	}



	/**
	 * Method to set middleware;
	 *
	 * @param  string $middleware
	 * @return Route
	 */
	public function middleware(string $middleware): Route
	{
		return $this->setMiddleware($middleware);
	}



	/**
	 * Set a middleware.
	 *
	 * @param string $middleware
	 */
	protected function setMiddleware(string $middleware)
	{
		if ($this->middleware == null) {
			global $baseDir;
			// run the middleware specified.
			$className = 'Saettem\\Gold\\Middleware\\' . $middleware;
			try {
				$this->middleware = $this->loadClass($className);
			} catch (\Exception $e) {
				include($baseDir . '/Gold/Middleware/'. $middleware . '.php');
				$this->middleware = $this->loadClass($className);
			}
			return $this;
		}
	}



	/**
	 * If the route has middleware, run it.
	 * @return void
	 */
	protected function runMiddleware(): Route
	{
		if ($this->middleware != null) {
			try {
				$this->middleware->run();
			}
			catch (\Exception $e) {



				/*
				 *  Redirect to login page
				 */
				Router::redirect('/login', ['real_uri' => $this->real_uri]);
			}
		}
		return $this;
	}



	/**
	 * Try to load a class
	 *
	 * @param  string $className The class
	 * @return Object Object in the new class type.
	 */
	protected function loadClass(string $className): Object
	{
		if (class_exists($className)) {
			return new $className();
		}
		else {
			throw new \Exception();
		}
	}


	public function name(string $name): Route
	{
		$this->name = $name;
		return $this;
	}
}
