<?php print $this->upload->display_errors(); ?>

<h2>File Upload</h2>
<form enctype="multipart/form-data" method="post">
	Select File: <input type="file" name="userfile" />
	<p>Files must be <?php print $this->upload->error_messages['allowed_types']; ?>.</p>
	<input type="submit" name="submit" />
</form>