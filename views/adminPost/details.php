<section>
	<h1><?php if(isset($post->id)): ?>Edit<?php else: ?>Add<?php endif; ?> Post</h1>
	<p>In this section you can <?php if(isset($post->id)): ?>edit the post <strong><?php echo $post->title; ?></strong><?php else: ?>add a new post<?php endif; ?>.</p>
	<form method="post" action="/admin/Post/<?php if(isset($post->id)): ?>Edit/<?php echo $post->id; else: ?>Add/<?php endif; ?>" enctype="multipart/form-data">
		<ul class="form">
			<li>
				<label for="posted">Publish date</label>
				<input type="text" name="post[posted]" id="posted" value="<?php echo isset($post->posted) ? $post->posted : ''; ?>" />
			</li>
			<li>
				<label for="status">Status</label>
				<select name="post[status]" id="status">
					<option value="<?php echo App::STATUS_INVISIBLE; ?>" <?php if(!isset($post->status) || ($post->status == App::STATUS_INVISIBLE)): ?>selected="selected"<?php endif; ?>>Draft</option>
					<option value="<?php echo App::STATUS_NORMAL; ?>" <?php if(isset($post->status) && ($post->status == App::STATUS_NORMAL)): ?>selected="selected"<?php endif; ?>>Publish</option>
					<option value="<?php echo App::STATUS_STICKY; ?>" <?php if(isset($post->status) && ($post->status == App::STATUS_STICKY)): ?>selected="selected"<?php endif; ?>>Sticky</option>
				</select>
			</li>
			<li>
				<label for="title">Title</label>
				<input type="text" name="post[title]" id="title" value="<?php echo isset($post->title) ? $post->title : '' ?>" />
			</li>
			<li>
				<label for="slug">Slug</label>
				http://<?php echo $_SERVER['SERVER_NAME']; ?>/<input type="text" name="post[slug]" id="slug" value="<?php echo isset($post->slug) ? $post->slug : '' ?>" />
			</li>
			<li>
				<label for="content">Content</label>
				<textarea name="post[content]" id="content"><?php echo isset($post->content) ? $post->content : ''; ?></textarea>
			</li>
			<li class="separator">Categories</li>
			<?php
				if(isset($categories) && (count($categories) > 0)):
					foreach($categories as $cat):
			?>
			<li class="checklist">
				<input type="checkbox" name="category[<?php echo $cat['id'] ?>][new]" id="cat_<?php echo $cat['id']; ?>" value="1" <?php if($cat['checked']): ?>checked="checked"<?php endif; ?> />
				<label for="cat_<?php echo $cat['id']; ?>"><?php echo $cat['title']; ?></label>
				<input type="hidden" name="category[<?php echo $cat['id']; ?>][old]" value="<?php echo (($cat['checked']) ? 1 : 0); ?>" />
			</li>
			<?php
					endforeach;
				endif;
			?>
			<li>
				<input type="submit" value="Submit" alt="Submit" />
			</li>
			<li>
				<a class="formBtn" href="/admin/Post/" title="Back to Post listing">Go back to Post listing</a>
			</li>
		</ul>
	</form>
</section>