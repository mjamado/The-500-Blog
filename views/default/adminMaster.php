<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8" />
		<link href="/includes/css/screen.css" media="screen, projection" rel="stylesheet" type="text/css" />
		<!--[if lt IE 8]><link href="/includes/css/ie.css" media="screen, projection" rel="stylesheet" type="text/css" /><![endif]-->
		<title><?php echo $this->title; ?> » Administration » The 500 Blog</title>
	</head>
	<body>
		<header>
			<h1>The 500 Blog Administration</h1>
			<?php if(App::$WebUser->LoggedIn()): ?>
			<nav>
				<ul>
					<li><a href="/admin/" title="Main page">Main page</a></li>
					<li><a href="/admin/User" title="Users">Users</a></li>
					<li><a href="/admin/Post" title="Posts">Posts</a></li>
					<li><a href="/admin/Category" title="Categories">Categories</a></li>
					<li><a href="/admin/Comment" title="Comments">Comments</a></li>
					<li><a href="/admin/Main/Logout" title="Log Out">Log Out</a></li>
				</ul>
			</nav>
			<?php endif; ?>
		</header>
		<?php require_once($view); ?>
	</body>
</html>