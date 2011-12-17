<?php if(isset($category)): ?>
<h2>Posts from category <?php echo $category->title; ?></h2>
<div class="navigation"><a href="/" title="Show all posts">Show all posts</a></div>
<?php endif; ?>
<?php if(isset($stickyPost) && (count($stickyPost) > 0)): ?>
<section class="sticky">
<?php
	$post = $stickyPost[0];
	require("_post.php");
?>
</section>
<?php endif; ?>
<?php if(isset($posts) && (count($posts) > 0)): ?>
<section>
	<?php foreach($posts as $post) require("_post.php"); ?>
	<?php include('widgets/paginator.php') ?>
</section>
<?php endif; ?>