<?php class PDOSingleton {
	private static $_instance;
	private function __construct() {}
	public static function GetObj() {
		if(!isset(self::$_instance)) self::$_instance = new PDO("mysql:host=localhost;dbname=c0500blog","c0500blog","a",array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'));
		return self::$_instance;
	}
	public function __clone() {
		throw new Exception("No cloning singletons");
	}
} ?>