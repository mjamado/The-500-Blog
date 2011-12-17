<?php class PostAdminController {
	private $title = "Posts";
	public function Index() {
		$page = empty($_GET['page']) ? 1 : $_GET['page'];
		$orderBy = empty($_GET['orderby']) ? 'posted' : $_GET['orderby'];
		$orderDir = empty($_GET['orderdir']) ? 'DESC' : $_GET['orderdir'];
		$obj = new Post();
		$items = $obj->GetAll(null, array($orderBy, $orderDir), array('page' => $page, 'numItemsPage' => 20));
		$paginator = $items->paginator;
		$view = 'adminPost/index.php';
		require_once('default/adminMaster.php');
	}
	public function Edit() {
		$id = empty($_GET['id']) ? 0 : $_GET['id'];
		$post = new Post($id);
		if(!empty($_POST['post']) && is_array($_POST['post'])) {
			$post->data = $_POST['post'];
			$post->Save();
			if(!empty($_POST['category']) && is_array($_POST['category'])) $post->SetNMRelated('categories', $_POST['category']);
			header('Location: /admin/Post/');
			exit();
		}
		$obj = new Category();
		$allCategories = $obj->GetAll(null, array('title', 'ASC'));
		$categories = array();
		if(count($allCategories) > 0) foreach($allCategories as $cat) $categories[] = array('id' => $cat->id,'title' => $cat->title,'checked' => (count($post->GetRelated('categories', array(array('category_id', '=', $cat->id)))) > 0));
		$view = 'adminPost/details.php';
		require_once('default/adminMaster.php');
	}
	public function Add() {
		if(!empty($_POST['post']) && is_array($_POST['post'])) {
			$post = new Post();
			$post->data = $_POST['post'];
			$post->Save();
			header('Location: /admin/Post/');
			exit();
		}
		$obj = new Category();
		$allCategories = $obj->GetAll(null, array('title', 'ASC'));
		$categories = array();
		if(count($allCategories) > 0) foreach($allCategories as $cat) $categories[] = array('id' => $cat->id,'title' => $cat->title,'checked' => false);
		$view = 'adminPost/details.php';
		require_once('default/adminMaster.php');
	}
	public function Delete() {
		$id = empty($_GET['id']) ? 0 : $_GET['id'];
		$post = new Post($id);
		$post->ToDelete();
		header('Location: /admin/Post/');
		exit();
	}
} ?>