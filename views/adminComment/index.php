<section>
	<h1>List Comments</h1>
	<p>In this section you can view and delete comments.</p>
	<?php if(count($items) > 0): ?>
	<?php include('widgets/paginator.php') ?>
	<table>
		<thead>
			<tr>
				<td><a class="order<?php echo ($orderBy == 'posted') ? $orderDir : 'None' ?>" href="/admin/Comment/Index/?orderby=posted&orderdir=<?php echo ($orderDir == 'ASC') ? 'DESC' : 'ASC'; ?>" title="Order by publish date">Publish date</a></td>
				<td><a class="order<?php echo ($orderBy == 'status') ? $orderDir : 'None' ?>" href="/admin/Comment/Index/?orderby=status&orderdir=<?php echo ($orderDir == 'ASC') ? 'DESC' : 'ASC'; ?>" title="Order by status">Status</a></td>
				<td><a class="order<?php echo ($orderBy == 'screenName') ? $orderDir : 'None' ?>" href="/admin/Comment/Index/?orderby=screenName&orderdir=<?php echo ($orderDir == 'ASC') ? 'DESC' : 'ASC'; ?>" title="Order by user name">User name</a></td>
				<td><a class="order<?php echo ($orderBy == 'email') ? $orderDir : 'None' ?>" href="/admin/Comment/Index/?orderby=email&orderdir=<?php echo ($orderDir == 'ASC') ? 'DESC' : 'ASC'; ?>" title="Order by user e-mail">User e-mail</a></td>
				<td><a class="order<?php echo ($orderBy == 'url') ? $orderDir : 'None' ?>" href="/admin/Comment/Index/?orderby=url&orderdir=<?php echo ($orderDir == 'ASC') ? 'DESC' : 'ASC'; ?>" title="Order by user URL">User URL</a></td>
				<td>Content</td>
				<td>Actions</td>
			</tr>
		</thead>
		<tbody>
			<?php foreach($items as $comment): ?>
			<tr>
				<td><?php echo date_format(date_create($comment->posted), "d M Y H:i"); ?></td>
				<td><?php echo ($comment->status != App::STATUS_NORMAL) ? ($comment->status != App::STATUS_INVISIBLE) ? 'Unknown' : 'Invisible' : 'Visible'; ?></td>
				<td><?php echo $comment->screenName; ?></td>
				<td><?php echo $comment->email; ?></td>
				<td><?php echo isset($comment->email) ? $comment->email : 'n/a'; ?></td>
				<td><?php echo nl2br($comment->content); ?></td>
				<td>
					<a href="/admin/Comment/Visibility/<?php echo $comment->id; ?>" title="Make comment <?php echo ($comment->status != App::STATUS_NORMAL) ? 'visible' : 'invisible'; ?>">Set <?php echo ($comment->status != App::STATUS_NORMAL) ? 'visible' : 'invisible'; ?></a>
					<a href="/admin/Comment/Delete/<?php echo $comment->id; ?>" title="Delete comment">Delete</a>
				</td>
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
	<?php include('widgets/paginator.php') ?>
	<?php else: ?>
	<p>There's no comments to show.</p>
	<?php endif; ?>
</section>