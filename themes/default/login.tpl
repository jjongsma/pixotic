<h2>Administration Login</h2>

<? if ($failed) { ?>
<div class="error">
	Login failed.
</div>
<? } ?>
<form method="post">
	<input type="hidden" name="view" value="login" />
	<input type="hidden" name="forward" value="<?= $forward; ?>" />
	<table border="0">
		<tr>
			<td>Username</td>
			<td><input name="username" type="text" /></td>
		</tr>
		<tr>
			<td>Password</td>
			<td><input name="password" type="password" /></td>
		</tr>
	</table>
	<input type="submit" value="Login" />
</form>
