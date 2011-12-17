<?php class App {
	const STATUS_NORMAL = 0;
	const STATUS_INVISIBLE = 1;
	const STATUS_STICKY = 2;
	public static $WebUser;
	public function __construct() {
		set_include_path(get_include_path() . PATH_SEPARATOR . $_SERVER['DOCUMENT_ROOT'] . '/core' . PATH_SEPARATOR . $_SERVER['DOCUMENT_ROOT'] . '/models' . PATH_SEPARATOR . $_SERVER['DOCUMENT_ROOT'] . '/controllers' . PATH_SEPARATOR . $_SERVER['DOCUMENT_ROOT'] . '/views');
		spl_autoload_register("App::classLoader");
		self::$WebUser = new WebUser();
		self::$WebUser->Touch();
		$this->Route();
	}
	public static function classLoader($class) {
		require_once($class . ".class.php");
	}
	private function Route() {
		$admin = empty($_GET['admin']) ? false : true;
		$controller = empty($_GET['controller']) ? 'Main' : $_GET['controller'];
		$action = empty($_GET['action']) ? 'Index' : $_GET['action'];
		if($admin && !self::$WebUser->LoggedIn() && !(($controller == 'Main') && ($action == 'Login'))) {
			header('Location: /admin/Main/Login/');
			exit();
		}
		$callClass = $controller . ($admin ? 'Admin' : '') . 'Controller';
		$obj = new $callClass();
		$obj->$action();
	}
} ?>