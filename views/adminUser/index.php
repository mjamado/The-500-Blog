<section>
	<h1>List Users</h1>
	<p>In this section you can view, alter and create users.</p>
	<?php if(count($items) > 0): ?>
	<?php include('widgets/paginator.php') ?>
	<table>
		<thead>
			<tr>
				<td><a class="order<?php echo ($orderBy == 'username') ? $orderDir : 'None' ?>" href="/admin/User/Index/?orderby=username&orderdir=<?php echo ($orderDir == 'ASC') ? 'DESC' : 'ASC'; ?>" title="Order by username">Username</a></td>
				<td><a class="order<?php echo ($orderBy == 'fullName') ? $orderDir : 'None' ?>" href="/admin/User/Index/?orderby=fullName&orderdir=<?php echo ($orderDir == 'ASC') ? 'DESC' : 'ASC'; ?>" title="Order by full name">Full name</a></td>
				<td><a class="order<?php echo ($orderBy == 'screenName') ? $orderDir : 'None' ?>" href="/admin/User/Index/?orderby=screenName&orderdir=<?php echo ($orderDir == 'ASC') ? 'DESC' : 'ASC'; ?>" title="Order by screen name">Screen name</a></td>
				<td><a class="order<?php echo ($orderBy == 'registration') ? $orderDir : 'None' ?>" href="/admin/User/Index/?orderby=registration&orderdir=<?php echo ($orderDir == 'ASC') ? 'DESC' : 'ASC'; ?>" title="Order by registration">Registration</a></td>
				<td><a class="order<?php echo ($orderBy == 'lastLogin') ? $orderDir : 'None' ?>" href="/admin/User/Index/?orderby=lastLogin&orderdir=<?php echo ($orderDir == 'ASC') ? 'DESC' : 'ASC'; ?>" title="Order by last login">Last Login</a></td>
				<td>Actions</td>
			</tr>
		</thead>
		<tbody>
			<?php foreach($items as $user): ?>
			<tr>
				<td><?php echo $user->username; ?></td>
				<td><?php echo $user->fullName; ?></td>
				<td><?php echo $user->screenName; ?></td>
				<td><?php echo is_null($user->registration) ? 'n/a' : date_format(date_create($user->registration), 'd M Y H:i'); ?></td>
				<td><?php echo is_null($user->lastLogin) ? 'n/a' : date_format(date_create($user->lastLogin), 'd M Y H:i'); ?></td>
				<td>
					<a href="/admin/User/Edit/<?php echo $user->id; ?>" title="Edit user '<?php echo $user->username; ?>'">Edit</a>
					<a href="/admin/User/Delete/<?php echo $user->id; ?>" title="Delete user '<?php echo $user->username; ?>'">Delete</a>
				</td>
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
	<?php include('widgets/paginator.php') ?>
	<?php else: ?>
	<p>There are no users (nobody should ever see this).</p>
	<?php endif; ?>
	<aside>
		<a href="/admin/User/Add" title="Add new user">Add new user</a>
	</aside>
</section>