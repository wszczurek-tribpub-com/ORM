<?php

namespace FSBO;

class PDO {

	static $instances = [
		'default' => null,
	];

	static function getInstance($name = 'default', $options = []) {

		if(static::$instances[$name] instanceof \PDO) {
			return static::$instances[$name];
		}

		switch($name) {
			case 'default' :
				$host = 'fcldfsbo02.usa.tribune.com';
				$user = 'web';
				$pass = 'jkNFjb34j8hkNF40j59';
				$db_name = true ? 'fsbo_devB' : null;
			break;
			default :
				throw new Exception("PDO instance {$name} is undefined!");
		}

		$conn = new \PDO("mysql:host={$host};dbname={$db_name}", $user, $pass);
		$conn->exec("SET NAMES latin1");
		static::$instances[$name] = $conn;

		return static::$instances[$name];

	}

}