<?php
class UserAdminController
{
	private $title = "Users";

	public function Index()
	{
		$page = empty($_GET['page']) ? 1 : $_GET['page'];
		$orderBy = empty($_GET['orderby']) ? 'registration' : $_GET['orderby'];
		$orderDir = empty($_GET['orderdir']) ? 'DESC' : $_GET['orderdir'];

		$obj = new User();
		$items = $obj->GetAll(null, array($orderBy, $orderDir), array('page' => $page, 'numItemsPage' => 20));
		$paginator = $items->paginator;

		$view = 'adminUser/index.php';
		require_once('default/adminMaster.php');
	}

	public function Edit()
	{
		$id = empty($_GET['id']) ? 0 : $_GET['id'];

		$user = new User($id);

		if(!empty($_POST['user']) && is_array($_POST['user']))
		{
			$user->data = $_POST['user'];
			$user->Save();

			header('Location: /admin/User/');
			exit();
		}

		$view = 'adminUser/details.php';
		require_once('default/adminMaster.php');
	}

	public function Add()
	{
		if(!empty($_POST['user']) && is_array($_POST['user']))
		{
			$user = new User();
			$user->data = $_POST['user'];
			$user->Save();

			header('Location: /admin/User/');
			exit();
		}

		$view = 'adminUser/details.php';
		require_once('default/adminMaster.php');
	}

	public function Delete()
	{
		$id = empty($_GET['id']) ? 0 : $_GET['id'];

		$user = new User($id);
		$user->ToDelete();

		header('Location: /admin/User/');
		exit();
	}
}
?>