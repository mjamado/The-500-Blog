<?php
class MainAdminController
{
	private $title = "Main Administration Page";

	public function Index()
	{
		$postObj = new Post();
		$numPosts = count($postObj->GetAll());

		$categoryObj = new Category();
		$numCategories = count($categoryObj->GetAll());

		$commentObj = new Comment();
		$numComments = count($commentObj->GetAll());

		$view = 'adminMain/index.php';
		require_once('default/adminMaster.php');
	}

	public function Login()
	{
		if(isset($_POST['username']) && isset($_POST['password']))
		{
			App::$WebUser->Login($_POST['username'], $_POST['password']);
			if(App::$WebUser->LoggedIn())
			{
				header('Location: /admin/');
				exit();
			}
		}

		$view = 'adminMain/login.php';
		require_once('default/adminMaster.php');
	}

	public function Logout()
	{
		App::$WebUser->LogOut();
		header('Location: /admin/Main/Login/');
		exit();
	}
}
?>
