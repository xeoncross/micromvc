<?php print $upload->display_errors(); ?>

<h2>File Upload</h2>
<form enctype="multipart/form-data" method="post">
	Select File: <input type="file" name="userfile" />
	<p>Files must be JPG, GIF, or PNG.</p>
	<input type="submit" name="submit" />
</form>