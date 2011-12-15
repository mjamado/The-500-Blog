<article>
	<p class="pubdate"><?php echo date_format(date_create($post->posted), "l, j<\s\u\p>S</\s\u\p> F Y @ H:i"); ?></p>
	<h1><a href="<?php echo "/" . $post->slug . "/"; ?>" title="View full post"><?php echo $post->title; ?></a></h1>
	<?php echo $post->content; ?>
	<p class="categories">Posted by <strong><?php echo $post->user->screenName; ?></strong> in <?php for($i = 0; $i < count($post->categories); $i++): ?><?php if($i > 0): ?>, <?php endif; ?><a href="/categories/<?php echo $post->categories[$i]->slug; ?>/" title="View posts from this categorie"><?php echo $post->categories[$i]->title; ?></a><?php endfor; ?></p>
</article>