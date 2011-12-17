<section>
	<h1>List Posts</h1>
	<p>In this section you can view, alter and create posts.</p>
	<?php if(count($items) > 0): ?>
	<?php include('widgets/paginator.php') ?>
	<table>
		<thead>
			<tr>
				<td><a class="order<?php echo ($orderBy == 'title') ? $orderDir : 'None' ?>" href="/admin/Post/Index/?orderby=title&orderdir=<?php echo ($orderDir == 'ASC') ? 'DESC' : 'ASC'; ?>" title="Order by title">Title</a></td>
				<td><a class="order<?php echo ($orderBy == 'posted') ? $orderDir : 'None' ?>" href="/admin/Post/Index/?orderby=posted&orderdir=<?php echo ($orderDir == 'ASC') ? 'DESC' : 'ASC'; ?>" title="Order by publish date">Publish date</a></td>
				<td><a class="order<?php echo ($orderBy == 'status') ? $orderDir : 'None' ?>" href="/admin/Post/Index/?orderby=status&orderdir=<?php echo ($orderDir == 'ASC') ? 'DESC' : 'ASC'; ?>" title="Order by status">Status</a></td>
				<td>User</td>
				<td>Comments</td>
				<td>Actions</td>
			</tr>
		</thead>
		<tbody>
			<?php foreach($items as $post): ?>
			<tr>
				<td><?php echo $post->title; ?></td>
				<td><?php echo date_format(date_create($post->posted), "d M Y H:i"); ?></td>
				<td><?php echo ($post->status != App::STATUS_NORMAL) ? ($post->status != App::STATUS_INVISIBLE) ? ($post->status != App::STATUS_STICKY) ? 'Unknown' : 'Sticky' : 'Draft' : 'Published'; ?></td>
				<td><?php echo $post->user->screenName; ?></td>
				<td><?php echo count($post->comments); ?></td>
				<td>
					<a href="/admin/Post/Edit/<?php echo $post->id; ?>" title="Edit post '<?php echo $post->title; ?>'">Edit</a>
					<a href="/admin/Post/Delete/<?php echo $post->id; ?>" title="Delete post '<?php echo $post->title; ?>'">Delete</a>
				</td>
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
	<?php include('widgets/paginator.php') ?>
	<?php else: ?>
	<p>There's no post to show.</p>
	<?php endif; ?>
	<aside><a href="/admin/Post/Add" title="Add new post">Add new post</a></aside>
</section>