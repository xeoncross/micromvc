<?php

// Check to see if we are uploading a new file
if($_FILES AND !empty($_FILES['userfile']))
{
	if($file = upload::file($_FILES['userfile'], SP.'uploads/'))
	{
		message('message', '<a href="/uploads/'. $file. '">'. $file. '</a> Uploaded!');
	}
}

// Load the theme sidebar since we don't need the full page
$this->sidebar = new View('sidebar');

// Load form view ( or you can use the Form class like above )
$this->content = new View('upload', 'example');

// Get all current files
$this->content->files = dir::contents(SP.'uploads/');