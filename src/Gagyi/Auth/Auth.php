<?php

/*
 * Name        : Gomba
 * Category    : Framework
 * File        : Auth
 * Author      : Joakim Saettem <joakim@saettem-mucsi.com>
 * Organization: Saettem-Mucsi
 * Updated     : 2019-10-29
 * CHANGELOG
 *  - V1.0: Initial version.
 *  - V1.1: user function updated to find user by user_name instead of user_id.
 *  - V1.2: user function search by id as user_name is now automatic as the user
 *          model was updated to use user_name as primary key.
*/

namespace Saettem\Gomba\Auth;

use Saettem\Gold\User;

class Auth
{
	private static $user;



	public static function loggedIn(): bool {
		return isset($_SESSION['user_id']);
	}



	public static function user() {
		if (!static::$user) {
			if (!static::loggedIn()) return;
			global $baseDir;
			include_once($baseDir . '/Gold/User.php');
			static::$user = User::find($_SESSION['user_id'])->get();
		}
		return static::$user;
	}



	public static function validatePassword(User $user, string $password) {
		if ($user->user_hash == null) return false;

		if (hash_equals($user->user_hash, crypt($password, $user->user_hash))) {
			return $user;
		}
		return false;
	}



	public static function logIn(User $user) {
		static::$user = $user;
		$primaryKey = User::$primaryKey;
		$_SESSION['user_id'] = $user->$primaryKey;
	}



	/*
	 * Doesn't work :)
	 */
	public static function logOut()
	{
		if (!static::$user) { return false; };

		static::$user = null;
		session_unset();
		session_destroy();
		return true;
	}



}
