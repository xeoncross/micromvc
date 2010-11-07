<h2>File Upload</h2>
<form enctype="multipart/form-data" method="post">
    Send this file: <input name="userfile" type="file" />
    <input type="submit" value="Upload" />
</form>

<h3>Current Files in /uploads/</h3>
<table>
	<thead>
		<tr>
			<th>Name</th>
			<th>Size (bytes)</th>
		</tr>
	</thead>
	<tbody>
	<?php foreach($files as $file)
	{ 
		print '<tr><td><a href="/uploads/'. $file->getFilename().'">'.$file->getFilename().'</a></td><td>'. number_format($file->getSize()). '</td>';
	}?>
	</tbody>
</table>