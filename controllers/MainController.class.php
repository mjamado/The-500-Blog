<?php
class MainController
{
	private function GetCategories()
	{
		// all the categories, to navigation panel
		$catObj = new Category();
		return $catObj->getAll();
	}

	private function GetLastComments()
	{
		// last 5 comments, preview of recent discussions
		$commentObj = new Comment();
		return $commentObj->GetAll(
			array(array('status', '!=', App::STATUS_INVISIBLE)),
			array('posted', 'DESC'),
			array('page' => 1, 'numItemsPage' => 5)
		);
	}

	public function Index()
	{
		$page = !isset($_GET['page']) ? 1 : $_GET['page'];

		$categories = $this->GetCategories();
		$lastComments = $this->GetLastComments();

		$postObj = new Post();

		// random sticky post
		$stickyPost = $postObj->GetAll(
			array(
				array('status', '=', App::STATUS_STICKY),
				array('posted', '<', date('c'))
			),
			array('', 'RAND'),
			array('page' => 1, 'numItemsPage' => 1)
		);

		// 10 posts to show minus 1 sticky, if one is available
		// watch the page, and do not repeat the random sticky!
		$conditions = array(
			array('status', '!=', App::STATUS_INVISIBLE),
			array('posted', '<', date('c'))
		);

		if(count($stickyPost) > 0)
			$conditions[] = array('id', '!=', $stickyPost[0]->id);

		$posts = $postObj->GetAll(
			$conditions,
			array('posted', 'DESC'),
			array('page' => $page, 'numItemsPage' => 10 - count($stickyPost))
		);

		$paginator = $posts->paginator;

		$view = 'main/index.php';
		require_once('default/master.php');
	}

	public function ViewPost()
	{
		if(isset($_GET['slug']))
		{
			$postObj = new Post();
			$posts = $postObj->GetAll(
				array(
					array('slug', '=', $_GET['slug']),
					array('status', '!=', App::STATUS_INVISIBLE),
					array('posted', '<', date('c'))
				)
			);

			if(count($posts) > 0)
			{
				$post = $posts[0];

				if(isset($_POST['comment']) && is_array($_POST['comment']))
				{
					$commentObj = new Comment();
					$commentObj->data = $_POST['comment'];
					$commentObj->postId = $post->id;
					$commentObj->Save();
				}

				$title = $post->title;

				$categories = $this->GetCategories();
				$lastComments = $this->GetLastComments();

				$view = 'main/viewPost.php';
				require_once('default/master.php');
			}
			else
			{
				header('Location: /');
				exit();
			}
		}
		else
		{
			header('Location: /');
			exit();
		}
	}

	public function ViewCategory()
	{
		if(isset($_GET['slug']))
		{
			$page = !isset($_GET['page']) ? 1 : $_GET['page'];

			$categoryObj = new Category();
			$categories = $categoryObj->GetAll(
				array(
					array('slug', '=', $_GET['slug'])
				)
			);
			$category = $categories[0];

			$categories = $this->GetCategories();
			$lastComments = $this->GetLastComments();

			$posts = $category->GetRelated(
				'posts',
				array(
					array('status', '!=', App::STATUS_INVISIBLE),
					array('posted', '<', date('c'))
				),
				array('posted', 'DESC'),
				array('page' => $page, 'numItemsPage' => 10)
			);

			$paginator = $posts->paginator;

			$title = "Posts in " . $category->title;

			$view = 'main/index.php';
			require_once('default/master.php');
		}
		else
		{
			header('Location: /');
			exit();
		}
	}
}
?>