<?php
/**
 * File Upload
 *
 * Example uploading a file
 *
 * @package		MicroMVC
 * @author		David Pennington
 * @copyright	(c) 2010 MicroMVC Framework
 * @license		http://micromvc.com/license
 ********************************** 80 Columns *********************************
 */
namespace Example\Controller;

class Upload extends \Core\Controller
{
	public function action()
	{
		// Check to see if we are uploading a new file
		if($_FILES AND !empty($_FILES['userfile']))
		{
			$upload = new \Core\Upload();

			if($file = $upload->file($_FILES['userfile'], SP . 'Public/uploads/'))
			{
				message('message', '<a href="/uploads/'. $file. '">'. $file. '</a> Uploaded!');
			}
		}

		// Load the theme sidebar since we don't need the full page
		$this->sidebar = new \Core\View('Sidebar');

		// Load form view ( or you can use the Form class like above )
		$this->content = new \Core\View('Upload', 'Example');

		// Get all current files
		$this->content->files = \Core\Directory::contents(SP . 'Public/uploads/');

	}
}
