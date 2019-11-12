<?php

namespace Saettem\Gomba\Vakol;

use Saettem\Gomba\Base;
use Saettem\Gomba\Routes\Router;
use Saettem\Gomba\Support\Support;



trait ViewsTrait
{
	protected static $viewBody;
	protected static $variables = [];
	protected static $config;
	public static $messages = [];



	/**
	 * Return the contents of a view
	 *
	 * @param  string $view      View filename
	 * @param  array  $variables Vars that should be visible
	 * @return string
	 *
	 */
	public static function view(string $view, array $variables = [])
	{
		static::$config = Support::$config;
		static::$variables = $variables;
		static::$views[] = $view;
		static::readView($view);
		static::parse();
		return (string) static::$viewBody;
	}



	/**
	 * Read a view
	 *
	 * @param  string $view Filename
	 * @return void
	 *
	 */
	protected static function readView(string $view)
	{
		global $baseDir;
		$filename = $baseDir . '/' . Support::$config['views_path'] . "{$view}.vakol.php";

		// Make variables available to the view.
		foreach(static::$variables as $variable => $value) {
			$$variable = $value;
		}

		$config = static::$config;

		ob_start();
		try {
			include($filename);
			static::$viewBody = ob_get_clean();
		}
		catch (Exception $e) {
			echo 'Error ' . $e->getMessage();
		}
	}



	/**
	 * Parse a view
	 *
	 * @return void
	 *
	 */
	public static function parse()
	{
		// BUG: can't get it working yet.
		//static::$viewBody = str_replace("@include", "meep", static::$viewBody);
		//static::$viewBody = str_replace(" }}", " >", static::$viewBody);
		//static::$viewBody = str_replace("{{ ", "<?= ", static::$viewBody);
	}



	/**
	 * Load the page for a named route.
	 *
	 * @param  string $routeName
	 * @return array $parameters
	 */
	public static function route(string $routeName)
	{
		// See if there's a route with this name.
		$route = Router::find($routeName);
		$route->run();
	}
}
