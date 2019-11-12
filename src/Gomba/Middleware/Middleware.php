<?php

namespace Saettem\Gomba\Middleware;

abstract class Middleware
{
	public function run()
	{
		if (!$this->authorize()) {
			throw new \Exception('Unauthorized');
		}

	}
}
