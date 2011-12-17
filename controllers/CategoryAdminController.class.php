<?php class CategoryAdminController {
	private $title = "Categories";
	public function Index() {
		$page = empty($_GET['page']) ? 1 : $_GET['page'];
		$orderBy = empty($_GET['orderby']) ? 'posted' : $_GET['orderby'];
		$orderDir = empty($_GET['orderdir']) ? 'DESC' : $_GET['orderdir'];
		$obj = new Category();
		$items = $obj->GetAll(null, array($orderBy, $orderDir), array('page' => $page, 'numItemsPage' => 20));
		$paginator = $items->paginator;
		$view = 'adminCategory/index.php';
		require_once('default/adminMaster.php');
	}
	public function Edit() {
		$id = empty($_GET['id']) ? 0 : $_GET['id'];
		$category = new Category($id);
		if(!empty($_POST['category']) && is_array($_POST['category'])) {
			$category->data = $_POST['category'];
			$category->Save();
			header('Location: /admin/Category/');
			exit();
		}
		$view = 'adminCategory/details.php';
		require_once('default/adminMaster.php');
	}
	public function Add() {
		if(!empty($_POST['category']) && is_array($_POST['category'])) {
			$category = new Category();
			$category->data = $_POST['category'];
			$category->Save();
			header('Location: /admin/Category/');
			exit();
		}
		$view = 'adminCategory/details.php';
		require_once('default/adminMaster.php');
	}
	public function Delete() {
		$id = empty($_GET['id']) ? 0 : $_GET['id'];
		$category = new Category($id);
		$category->ToDelete();
		header('Location: /admin/Category/');
		exit();
	}
} ?>