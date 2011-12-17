<div class="navigation"><a href="/" title="Go back to the main page">Main Page</a></div>
<section><?php require("_post.php"); ?></section>
<?php if(count($post->comments) > 0): ?>
<section class="comments">
	<h1>Comments</h1>
	<?php foreach($post->comments as $comment): ?>
	<div>
		<div id="comment<?php $comment->id; ?>"><?php echo date_format(date_create($comment->posted), "l, j<\s\u\p>S</\s\u\p> F Y @ H:i"); ?> by <strong><?php if(!empty($comment->url)): ?><a href="<?php echo $comment->url; ?>" title="<?php echo $comment->screenName; ?>'s website"><?php echo $comment->screenName; ?></a><?php else: ?><?php echo $comment->screenName; ?><?php endif; ?></strong></div>
		<p><?php echo $comment->content; ?></p>
	</div>
	<?php endforeach; ?>
</section>
<form method="post" action="/<?php echo $post->slug; ?>/" enctype="multipart/form-data">
	<ul class="form">
		<li class="separator">Speak your mind</li>
		<?php $user = App::$WebUser->GetUser(); ?>
		<li>
			<label for="screenName">Name</label>
			<input type="text" name="comment[screenName]" id="title" value="<?php echo !is_null($user) ? $user->screenName : ''; ?>" />
		</li>
		<li>
			<label for="email">e-Mail</label>
			<input type="text" name="comment[email]" id="email" value="<?php echo !is_null($user) ? $user->email : ''; ?>" />
		</li>
		<li>
			<label for="content">Comment</label>
			<textarea name="comment[content]" id="content"></textarea>
		</li>
		<li><input type="submit" value="Submit" alt="Submit" /></li>
	</ul>
</form>
<?php endif; ?>