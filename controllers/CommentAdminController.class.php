<?php class CommentAdminController{
	private $title = "Comments";
	public function Index() {
		$page = empty($_GET['page']) ? 1 : $_GET['page'];
		$orderBy = empty($_GET['orderby']) ? 'posted' : $_GET['orderby'];
		$orderDir = empty($_GET['orderdir']) ? 'DESC' : $_GET['orderdir'];
		$obj = new Comment();
		$items = $obj->GetAll(null, array($orderBy, $orderDir), array('page' => $page, 'numItemsPage' => 20));
		$paginator = $items->paginator;
		$view = 'adminComment/index.php';
		require_once('default/adminMaster.php');
	}
	public function Delete() {
		$id = empty($_GET['id']) ? 0 : $_GET['id'];
		$comment = new Comment($id);
		$comment->ToDelete();
		header('Location: /admin/Comment/');
		exit();
	}
	public function Visibility() {
		$id = empty($_GET['id']) ? 0 : $_GET['id'];
		$comment = new Comment($id);
		$comment->status = ($comment->status == App::STATUS_NORMAL) ? App::STATUS_INVISIBLE : App::STATUS_NORMAL;
		$comment->Save();
		header('Location: /admin/Comment/');
		exit();
	}
} ?>