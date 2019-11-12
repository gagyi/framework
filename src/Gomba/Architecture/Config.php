<?php

/*
 * Name        : Gomba
 * Category    : Framework
 * File        : Config
 * Author      : Joakim Saettem <joakim@saettem-mucsi.com>
 * Organization: Saettem-Mucsi
 * Updated     : 2019-10-29
 * CHANGELOG
 *  - V1: Initial version.
*/

namespace Saettem\Gomba\Architecture;

use Saettem\Gomba\Base;
use Saettem\Gomba\Routes\Router;

class Config extends Base
{
	public $settings = [];



	public function __construct(string $configurationfile) {
		global $baseDir;
		$this->settings = require($baseDir . '/' . $configurationfile);
	}



	public function run(): void {
		if ($this->settings) {
			foreach($this->settings as $key => $value) {
				$this->handleSetting($key, $value);
			}
		}
	}


	protected function handleSetting(string $key, string $value) {
		switch ($key) {
			case 'error_reporting':
				switch ($value) {
					case 'all':
						error_reporting(E_ALL);
						break;
				}
				break;
			case 'display_errors':
				ini_set('display_errors', $value);
				break;
			case 'display_startup_errors':
				ini_set('display_startup_errors', $value);
				break;
			case 'approved_host':
				// Verify that the user's IP is approved.
				if ($_SERVER['REMOTE_ADDR'] != $value) {
					return Router::response('403', 'Unauthorized.');
				}
				break;
		}
	}
}
