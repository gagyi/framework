<?php

namespace Saettem\Gomba\Routes;

use Saettem\Gomba\Base;
use Saettem\Gomba\Support\Support;
use Saettem\Gomba\Architecture\Architecture;


/**
 * Responsible for routing page requests
 *
 */
class Router extends Base
{



	/**
	 * The method, get, post etc.
	 * @var string
	 */
	public static $method;



	/**
	 * All registered routes
	 * @var array
	 */
	public static $routes = [];



	/**
	 * Current address, as parsed.
	 * @var string
	 */
	public static $uri;



	/**
	 * Query supplied
	 * @var array
	 */
	protected static $query = [];



	protected static $uriReplacements;



	/**
	 * Load all routes, default to routes.php filename.
	 * @param string $routesFilename
	 */
	public function __construct(string $routesFilename = 'routes.php')
	{
		global $baseDir;
		static::$uri = static::parseURI();
		static::$method = static::getMethod();
		require($baseDir . '/' . $routesFilename);
	}



	protected static function getMethod()
	{
		return strtolower($_SERVER['REQUEST_METHOD']);
	}



	protected static function getURI()
	{
		if (isset($_SERVER['REDIRECT_URL'])) {
			return $_SERVER['REDIRECT_URL'];
		}
		return $_SERVER['REQUEST_URI'];
	}



	/**
	 * Parse the URI supplied
	 * @param  string $uri
	 * @return void
	 */
	protected static function parseURI()
	{

		$uri = static::getURI();

		// Check if the uri has atrailing characters slash, if so remove all.
		$uri = Support::str_scrub_right($uri, ['/', '#']);

		// Check if uri has a query supplied
		static::$query = static::query();

		return $uri;
	}



	/**
	 * Register a new get route.
	 * @param  string $uri                 Unparsed URI
	 * @param  mixed $ControllerOrClosure
	 * @return Route
	 */
	public static function get(string $uri, $ControllerOrClosure)
	{
		return static::newRoute($uri, $ControllerOrClosure, 'get');
	}



	/**
	 * Register a new POST route
	 * @param  string $uri                 Unparsed URI
	 * @param  mixed $ControllerOrClosure
	 * @return Route
	 */
	public static function post(string $uri, $ControllerOrClosure)
	{
		return static::newRoute($uri, $ControllerOrClosure, 'post');
	}



	public static function newRoute(string $uri, $ControllerOrClosure, $method = 'get')
	{
		$route = new Route($uri, $ControllerOrClosure, $method);

		// Replace matches.
		static::$routes[] = $route;

		return $route;
	}



	/**
	 * Execute the route's handling.
	 * @return Closure
	 */
	public static function run(Architecture $instance)
	{
		return static::getRoute()->run($instance);
	}



	/**
	 * Find the actual route based on the current URI
	 * @return Route
	 */
	protected static function getRoute() {

		// Find a defined route for this URI.
		$route = array_filter(static::$routes, function ($route) {
			$a = $route->uri == static::$uri;
			$b = $route->method == static::$method;
			return ($a && $b);
		});


		// Was there no defined route?
		if (!count($route)) {
			$matchArray = [];
			foreach(static::$routes as $route) {
				preg_match_all($route->regex(), static::$uri, $matches);
				if (isset($matches[1]) && count($matches[0]) == 1) {
					$count = count($matches)-1;
					if ($count) {
						$route->rank = $count;
						$matchArray[] = $route;
					}
				}
			}
			$route = $matchArray;
			arsort($route);
		}


		// Break the route out.
		foreach($route as $routed_item) {
			return $routed_item;
		}
		return static::response('404', 'Page not found');
	}



	/**
	 * Return a header response code.
	 * @param  integer $response
	 * @param  string  $text
	 * @return void
	 */
	public static function response(int $response = 404, string $text = 'Page not found')
	{
		header("HTTP/1.0 {$response} {$text}");
		exit();
	}



	/**
	 * Get the request method
	 * @return string
	 */
	public static function method(): String
	{
		return (string) $_SERVER['REQUEST_METHOD'];
	}



	/**
	 * Set up and return the query string
	 * @return array
	 */
	public static function query()
	{
		if ($_SERVER['QUERY_STRING'] == '') {
			return [];
		}

		$queries = explode('&', $_SERVER['QUERY_STRING']);
		$returnQuery = [];

		foreach($queries as $query) {
			if (strpos($query, "=")) {
				list($key, $value) = explode('=', $query);
				$returnQuery[$key] = $value;
			}
		}
		return (array) $returnQuery;
	}



	/**
	 * Redirect page
	 * @param  string $url
	 * @return void
	 */
	public static function redirect(string $url = 'back', array $messages = null)
	{
		if ($url == 'back') {
			if (isset($_SERVER['HTTP_REFERER'])) {
				$url = $_SERVER['HTTP_REFERER'];
			}
			else {
				$url = '/';
			}
		}

		if ($messages != null) {
			foreach ($messages as $key => $message) {
				$_SESSION[$key] = $message;
			}
		}
		header('Location: ' . $url);
	}




	/**
	 * Find a route by name
	 * @param  string $routeName
	 * @return Route
	 */
	public static function find(string $routeName): Route
	{
		$route = array_filter(static::$routes, function ($route) use ($routeName) {
			return $route->name == $routeName;
		});
		return Support::array_breakout($route);
	}




	public static function link($where)
	{
		if ($where == 'back') {
			return $_SERVER['HTTP_REFERER'];
		}
		return;
	}
}
