<?php
/**
 * File Functions
 *
 * Functions that help with handling all types of file system tasks.
 *
 * @package		MicroMVC
 * @author		David Pennington
 * @copyright	Copyright (c) 2009 MicroMVC
 * @license		http://www.gnu.org/licenses/gpl-3.0.html
 * @link		http://micromvc.com
 * @version		1.0.1 <5/31/2009>
 ********************************** 80 Columns *********************************
 */

/**
 * unzip a file to a new location
 */
function unzip($file, $new_file) {

	if(file_exists($file)) {
		$zip = new ZipArchive;
		$zip->open($file);
		$zip->extractTo($new_file);
		$zip->close();
		return TRUE;
	}

	return FALSE;
}



/**
 * Upload Check Errors
 *
 * Checks the given tmpfile for any errors or problems with
 * the upload
 *
 * @access	public
 * @param	string	Name of the File
 * @return	boolean
 */
function upload_check_errors($file_name='') {

	$errors = array(
	UPLOAD_ERR_INI_SIZE => 'The uploaded file exceeds the upload_max_filesize directive in php.ini.',
	UPLOAD_ERR_FORM_SIZE => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.',
	UPLOAD_ERR_PARTIAL => 'The uploaded file was only partially uploaded.',
	UPLOAD_ERR_NO_FILE => 'No file was uploaded.',
	UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder.',
	UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
	UPLOAD_ERR_EXTENSION => 'File upload stopped by extension.',
	);

	//Get the error
	$error = $_FILES[$file_name]['error'];

	//IF the error is something OTHER than "OK"
	if($error !== UPLOAD_ERR_OK) {
		if(isset($errors[$error])) {
			trigger_error($errors[$error], E_USER_WARNING);
		} else {
			trigger_error('Unknown file upload error in file: <b>'
			. clean_value($_FILES[$file_name]['name']). '</b>',
			E_USER_WARNING);
		}
		return FALSE;
	}

	//If the file never made it to the server
	if(!is_uploaded_file($_FILES[$file_name]['tmp_name'])) {
		trigger_error('Possible file upload attack in file: '
		. clean_value($_FILES[$file_name]['name']). '</b>',
		E_USER_WARNING);
		return FALSE;
	}

	return TRUE;

}



/**
 * Upload Files
 *
 * @access	public
 * @param	string	The directory to place the uploaded files
 * @return	boolean
 */
function upload_files($dir) {

	//If the upload directory is useable and there are files to upload
	if(directory_usable($dir) && isset($_FILES)) {

		//Foreach file that has been uploaded
		foreach($_FILES as $name => $file) {

			//If no errors with the file
			if(upload_check_errors($name)) {
				if(!move_uploaded_file($file['tmp_name'], $dir. $file['name'])) {
					trigger_error('Could not move file', E_USER_ERROR);
					return;
				}
			}

		}
		return TRUE;
	}

}



///////////////////////////////////////////////////////////
// A function to list all files within the specified directory
// and it's sub-directories. This is a recursive function that
// has no limit on the number of levels down you can search.
///////////////////////////////////////////////////////////
// What info does this function need?
/////////////////
//
// $data['start_dir']   The directory to start searching from   (Required) ("./" = current dir, "../" = up one level)
// $data['good_ext']    The file extensions to allow.           (Required) (set to 'array('all') to include everything)
// $data['skip_files']  An array of files to skip.              (Required) (empty array if you don't want to skip anything)
// $data['limit']       The limit of dir to search              (Required)
// $data['type']        Return files or Directories?            (Optional) (defaults to BOTH types but can also set to 'dir' or 'file')
// $data['light']       Only return file name and path          (Optional) (defaults to false) (true or false)
//
/////////////////
// Example data
/////////////////
//
// $data['start_dir']      = "../../";
// $data['good_ext']       = array('php', 'html');
// $data['skip_files']     = array('..', '.', 'txt', '.htaccess');
// $data['limit']          = 5;
// $data['type']           = 'file';
// $data['light']          = false;
//
//////////////////////////////////////////////////
function directory($data, $level=1) {

	//If no type was specified - default to showing BOTH
	if(!isset($data['type']) || !$data['type']) { $data['type'] = false; }

	//If light was not specified - defualt to heavy version
	if(!isset($data['light']) || !$data['light']) { $data['light'] = false; }

	//If the directory given actually IS a directory
	if (is_dir($data['start_dir'])) {

		//Then open the directory
		$handle = opendir($data['start_dir']);

		//Initialize array
		$files = array();

		//while their are files in the directory...
		while (($file = readdir($handle)) !== false) {

			//If the file is NOT in the bad file list...
			if (!(array_search($file, $data['skip_files']) > -1)) {

				//Store the full file path in a var
				$path = $data['start_dir']. $file;

				//if it is a dir
				if (filetype($path) == "dir") {

					//add it to our list of dirs
					if(!$data['type'] || $data['type'] == 'dir') {
						//Add the dir to our list
						$files[$path]['file'] = $file;
						$files[$path]['dir'] = substr($path, strlen(SYSTEM_PATH), -strlen($file));

						//If we are only getting the file names/paths
						if(!$data['light']) {
							$files[$path]['ext'] = 'dir';
							$files[$path]['level'] = $level;
							$files[$path]['size'] = 0;//@disk_total_space($path);
						}
					}

					//If the dir is NOT deeper than the limit && 'recursive' is set to TRUE
					if($data['limit'] > $level){

						//Run this function on on the directory to see what is in it (this is where the recursive part starts)
						$files2 = directory(array('start_dir' => $path. '/', 'good_ext' => $data['good_ext'],
                                                  'skip_files' => $data['skip_files'], 'limit' => $data['limit'],
                                                  'type' => $data['type'], 'light' => $data['light']), $level + 1);

						//then combine the output with the current $files array
						if(is_array($files2)) { $files = array_merge($files, $files2); }
						$files2 = null;
					}

					//Else if it is a file
				} else {

					//get the extension of the file
					$ext = preg_replace('/(.+)\.([a-z0-9]{2,4})/i', '\\2', $file);

					//And if it is in the GOOD file extension list OR if the list is set to allow ALL files
					if( (($data['good_ext'][0] == "all") || (array_search($ext, $data['good_ext']) > -1)) && (!$data['type'] || $data['type'] == 'file') ) {

						//Add the file to our list
						$files[$path]['file'] = $file;
						$files[$path]['dir'] = substr($path, strlen(SYSTEM_PATH), -strlen($file));
						//Get the LAST "." followed by 2-4 letters/numbers
						$files[$path]['ext'] = $ext;

						//If we are only getting the file names/paths
						if(!$data['light']) {
							$files[$path]['level'] = $level;
							$files[$path]['size'] = filesize($path);
						}

					}
				}
			}
		}

		//Close the dir handle
		closedir($handle);

		//If there ARE files to sort
		if($files) {
			//sort by KEYS
			ksort($files);
		}

		//Return the result
		return $files;
	}

	trigger_error($data['start_dir']. " is not a valid directory.");
	return FALSE;
}


/**
 * Checks that a directory exists and is writable. If the directory does
 * not exist, the function will try to create it and/or change the
 * CHMOD settings on it.
 *
 * @param string $dir	directory you want to check
 * @param string $chmod	he CHMOD value you want to make it
 * @return unknown
 */
function directory_usable($dir, $chmod='0777') {

	//If it doesn't exist - make it!
	if(!is_dir($dir)) {
		if(!mkdir($dir, $chmod, TRUE)) {
			trigger_error('Could not create the directory: <b>'. $dir. '</b>', E_USER_WARNING);
			return;
		}
	}

	//Make it writable
	if(!is_writable($dir)) {
		if(!chmod($dir, $chmod)) {
			trigger_error('Could not CHMOD 0777 the directory: <b>'. $dir. '</b>', E_USER_WARNING);
			return;
		}
	}

	return TRUE;
}



/**
 * A function to recursively delete files and folders
 * @thanks: dev at grind [[DOT]] lv
 *
 * @param string	$dir	The path of the directory you want deleted
 * @param boolean	$remove	Remove Files (false) or Folder and Files (true)
 * @return boolean
 */
function destroy_directory($dir='', $remove=true) {

	//Try to open the directory handle
	if(!$dh = opendir($dir)) {
		trigger_error('<b>'. $dir. '</b> cannot be opened or does not exist', E_USER_WARNING);
		return FALSE;
	}

	//While there are files and directories in this directory
	while (false !== ($obj = readdir($dh))) {

		//Skip the object if it is the linux current (.) or parent (..) directory
		if($obj=='.' || $obj=='..') continue;

		$obj = $dir. $obj;

		//If the object is a directory
		if(is_dir($obj)) {

			//If we could NOT delete this directory
			if(! destroy_directory($obj, $remove)) {
				return FALSE;
			}

			//Else it must be a file
		} else {
			if(! unlink($obj)) {
				trigger_error('Could not remove file <b>'. $obj. '</b>', E_USER_WARNING);
				return FALSE;
			}
		}

	}

	//Close the handle
	closedir($dh);

	if ($remove && !rmdir($dir)){
		 trigger_error('Could not remove directory <b>'. $dir. '</b>');
		 return FALSE;
	}

	return TRUE;
}