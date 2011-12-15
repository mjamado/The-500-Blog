<?php

/**
 * Simple singleton to provide the PDO object to whatever asks for it.
 *
 * @author Marco Amado <mjamado@dreamsincode.com>
 * @since 2011-11-18 [last review: 2011-11-18]
 * @license Creative Commons 2.5 Portugal BY-NC-SA
 * @link http://www.dreamsincode.com/
 */
class PDOSingleton
{
	/**
	 * @var PDO
	 */
    private static $_instance;

	/**
	 * Private constructor; avoids class direct instatiation
	 */
    private function __construct()
    {
		// private ctor
    }

	/**
	 * Factory method; ensures that only one instance exists for the course of
	 * the request
	 *
	 * @return PDO
	 */
    public static function GetObj()
    {
		if(!isset(self::$_instance))
			self::$_instance = new PDO(
				"mysql:host=localhost;dbname=c0500blog",
				"c0500blog",
				"a",
				array(
					PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'
				)
			);

        return self::$_instance;
    }

	/**
	 * Throws an exception: a singleton cannot be cloned
	 */
    public function __clone()
    {
		throw new Exception("No cloning singletons");
    }
}
?>