<?php

/**
 * This class doesn't do much, for now. It's pretty much a router service, but
 * can be expanded to do much more (for instance, as a configuration platform,
 * by means of variables or fetching from the database).
 *
 * @author Marco Amado <mjamado@dreamsincode.com>
 * @since 2011-11-20 [last review: 2011-11-20]
 * @license Creative Commons 2.5 Portugal BY-NC-SA
 * @link http://www.dreamsincode.com/
 */
class App
{
	const STATUS_NORMAL = 0;
	const STATUS_INVISIBLE = 1;
	const STATUS_STICKY = 2;

	/**
	 * @var WebUser
	 */
	public static $WebUser;

	/**
	 * Constructor; sets the include path, registers the autoloader and routes
	 * the request
	 */
	public function __construct()
	{
		set_include_path(
			get_include_path() . PATH_SEPARATOR .
			$_SERVER['DOCUMENT_ROOT'] . '/core' . PATH_SEPARATOR .
			$_SERVER['DOCUMENT_ROOT'] . '/models' . PATH_SEPARATOR .
			$_SERVER['DOCUMENT_ROOT'] . '/controllers' . PATH_SEPARATOR .
			$_SERVER['DOCUMENT_ROOT'] . '/views'
		);

		spl_autoload_register("App::classLoader");

		self::$WebUser = new WebUser();
		self::$WebUser->Touch();

		$this->Route();
	}

	/**
	 * Autoloader; nothing fancy
	 */
	public static function classLoader($class)
	{
		require_once($class . ".class.php");
	}

	/**
	 * Routes to the requested controller and action
	 */
	private function Route()
	{
		$admin = empty($_GET['admin']) ? false : true;
		$controller = empty($_GET['controller']) ? 'Main' : $_GET['controller'];
		$action = empty($_GET['action']) ? 'Index' : $_GET['action'];

		if($admin && !self::$WebUser->LoggedIn() && !(($controller == 'Main') && ($action == 'Login')))
		{
			header('Location: /admin/Main/Login/');
			exit();
		}

		$callClass = $controller . ($admin ? 'Admin' : '') . 'Controller';
		$obj = new $callClass();
		$obj->$action();
	}
}
?>