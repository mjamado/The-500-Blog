<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8" />
		<link href="/includes/css/screen.css" media="screen, projection" rel="stylesheet" type="text/css" />
		<!--[if lt IE 8]><link href="/includes/css/ie.css" media="screen, projection" rel="stylesheet" type="text/css" /><![endif]-->
		<title><?php if(isset($title)): ?><?php echo $title; ?> » <?php endif; ?>500 Blog</title>
	</head>
	<body>
		<header>
			<h1>500 Blog</h1>
		</header>
		<div class="main">
			<?php require_once($view); ?>
		</div>
		<div class="sidebar">
			<?php if(isset($categories) && (count($categories) > 0)): ?>
			<ul>
				<li class="separator">Categories</li>
				<?php foreach($categories as $cat): ?>
				<li><a href="/categories/<?php echo $cat->slug; ?>/" title="View posts from category '<?php echo $cat->title; ?>'"><?php echo $cat->title; ?></a></li>
				<?php endforeach; ?>
			</ul>
			<?php endif; ?>
			<?php if(isset($lastComments) && (count($lastComments) > 0)): ?>
			<ul class="lastComments">
				<li class="separator">Last Comments</li>
				<?php foreach($lastComments as $comment): ?>
				<li>
					<a href="/<?php echo $comment->post->slug; ?>/#comment<?php echo $comment->id; ?>" title="View comments to '<?php echo $comment->post->title; ?>'">
						<?php echo date_format(date_create($comment->posted), "l, j<\s\u\p>S</\s\u\p> F Y @ H:i"); ?> by <strong><?php echo $comment->screenName; ?></strong>
					</a>
					<p><?php echo $comment->content; ?></p>
				</li>
				<?php endforeach; ?>
			</ul>
			<?php endif; ?>
		</div>
	</body>
</html>