<section>
	<h1>List Categories</h1>
	<p>In this section you can view, alter and create categories.</p>
	<?php if(count($items) > 0): ?>
	<?php include('widgets/paginator.php') ?>
	<table>
		<thead>
			<tr>
				<td><a class="order<?php echo ($orderBy == 'title') ? $orderDir : 'None' ?>" href="/admin/Category/Index/?orderby=title&orderdir=<?php echo ($orderDir == 'ASC') ? 'DESC' : 'ASC'; ?>" title="Order by title">Title</a></td>
				<td>Num. Posts</td>
				<td>Actions</td>
			</tr>
		</thead>
		<tbody>
			<?php foreach($items as $category): ?>
			<tr>
				<td><?php echo $category->title; ?></td>
				<td><?php echo count($category->posts); ?></td>
				<td>
					<a href="/admin/Category/Edit/<?php echo $category->id; ?>" title="Edit category '<?php echo $category->title; ?>'">Edit</a>
					<a href="/admin/Category/Delete/<?php echo $category->id; ?>" title="Delete category '<?php echo $category->title; ?>'">Delete</a>
				</td>
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
	<?php include('widgets/paginator.php') ?>
	<?php else: ?>
	<p>There's no categories to show.</p>
	<?php endif; ?>
	<aside>
		<a href="/admin/Category/Add" title="Add new category">Add new category</a>
	</aside>
</section>