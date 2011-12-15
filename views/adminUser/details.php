<section>
	<h1><?php if(isset($user->id)): ?>Edit<?php else: ?>Add<?php endif; ?> User</h1>
	<p>In this section you can <?php if(isset($user->id)): ?>edit the user <strong><?php echo $user->username; ?></strong><?php else: ?>insert a new user<?php endif; ?>.</p>
	<form method="post" action="/admin/User/<?php if(isset($user->id)): ?>Edit/<?php echo $user->id; else: ?>Add/<?php endif; ?>" enctype="multipart/form-data">
		<ul class="form">
			<?php if(isset($user->id)): ?>
			<li>
				<label for="">Registration</label>
				<?php echo is_null($user->registration) ? 'n/a' : date_format(date_create($user->registration), 'd M Y H:i'); ?>
			</li>
			<li>
				<label for="">Last Login</label>
				<?php echo is_null($user->lastLogin) ? 'n/a' : date_format(date_create($user->registration), 'd M Y H:i'); ?>
			</li>
			<?php endif; ?>
			<li>
				<label for="username">Username</label>
				<input type="text" name="user[username]" id="username" value="<?php echo isset($user->username) ? $user->username : ''; ?>" />
			</li>
			<li>
				<label for="email">E-mail</label>
				<input type="text" name="user[email]" id="email" value="<?php echo isset($user->email) ? $user->email : ''; ?>" />
			</li>
			<li>
				<label for="password">Password</label>
				<input type="password" name="user[password]" id="password" value="" />
			</li>
			<li>
				<label for="re_password">Retype Password</label>
				<input type="password" name="user[re_password]" id="re_password" value="" />
			</li>
			<li>
				<label for="fullName">Full Name</label>
				<input type="text" name="user[fullName]" id="fullName" value="<?php echo isset($user->fullName) ? $user->fullName : ''; ?>" />
			</li>
			<li>
				<label for="screenName">Screen Name</label>
				<input type="text" name="user[screenName]" id="screenName" value="<?php echo isset($user->screenName) ? $user->screenName : ''; ?>" />
			</li>
			<li>
				<input type="submit" value="Submit" alt="Submit" />
			</li>
			<li>
				<a class="formBtn" href="/admin/User/" title="Back to User listing">Go back to User listing</a>
			</li>
		</ul>
	</form>
</section>