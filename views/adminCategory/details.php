<section>
	<h1><?php if(isset($category->id)): ?>Edit<?php else: ?>Add<?php endif; ?> Category</h1>
	<p>In this section you can <?php if(isset($category->id)): ?>edit the category <strong><?php echo $category->title; ?></strong><?php else: ?>add a new category<?php endif; ?>.</p>
	<form method="post" action="/admin/Category/<?php if(isset($category->id)): ?>Edit/<?php echo $category->id; else: ?>Add/<?php endif; ?>" enctype="multipart/form-data">
		<ul class="form">
			<li>
				<label for="title">Title</label>
				<input type="text" name="category[title]" id="title" value="<?php echo isset($category->title) ? $category->title : '' ?>" />
			</li>
			<li>
				<label for="slug">Slug</label>
				http://<?php echo $_SERVER['SERVER_NAME']; ?>/Category/<input type="text" name="category[slug]" id="slug" value="<?php echo isset($category->slug) ? $category->slug : '' ?>" />
			</li>
			<li>
				<input type="submit" value="Submit" alt="Submit" />
			</li>
			<li>
				<a class="formBtn" href="/admin/Category/" title="Back to Category listing">Go back to Category listing</a>
			</li>
		</ul>
	</form>
</section>