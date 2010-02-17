<?php defined('SYSTEM_PATH') or die('No direct access');
/**
 * File Functions
 *
 * Functions that help with handling all types of file system tasks.
 *
 * @package		MicroMVC
 * @author		David Pennington
 * @copyright	(c) 2010 MicroMVC Framework
 * @license		http://micromvc.com/license
 ********************************** 80 Columns *********************************
 */

/**
 * Remove a given file system path from the file/path string.
 * If the file/path does not contain the given path - return FALSE.
 * @param	string	$file
 * @param	string	$path
 * @return	mixed
 */
function remove_path($file, $path = UPLOAD_PATH) {
	if(mb_strpos($file, $path) !== FALSE) {
		return mb_substr($file, mb_strlen($path));
	}
}

/**
 * Convert a file system path *UNIX / URL form
 * @param	string
 * @return	string
 */
function normalize_path($file = '') {
	return str_replace('\\', DS, $file);
}


/**
 * unzip a file to a new location
 */
function unzip($file, $new_file) {

	if(file_exists($file) AND class_exists('ZipArchive', FALSE))
	{
		$zip = new ZipArchive;
		$zip->open($file);
		$zip->extractTo($new_file);
		$zip->close();
		return TRUE;
	}

	return FALSE;
}


/**
 * Validates that the the given file upload worked, returns an error
 * string on failure and FALSE on success.
 *
 * @param	string	Field name to check
 * @param	string	Optional name if field name is an array
 * @return	mixed
 */
function file_upload_error($field = 'userfile', $param = NULL) {

	//Get the error from the multiple "file[1,2..]" input or the single "file" input by this name
	if( $param && isset($_FILES[$field]['error'][$param]) ) {
		$error = $_FILES[$field]['error'][$param];
	} elseif ( isset($_FILES[$field]['error']) ) {
		$error = $_FILES[$field]['error'];
	} else {
		$error = UPLOAD_ERR_NO_FILE;
	}

	//If the error is something OTHER than "OK"
	if($error !== UPLOAD_ERR_OK) {
		return lang($error);
	}

	//Get the file from the multiple "file[1,2..]" input or the single "file" input by this name
	if( $param && isset($_FILES[$field]['tmp_name'][$param]) ) {
		$file = $_FILES[$field]['tmp_name'][$param];
	} else if( isset($_FILES[$field]['tmp_name']) ) {
		$file = $_FILES[$field]['tmp_name'];
	} else {
		$file = NULL;
	}

	//Also check that the file was uploaded via a POST request
	if( ! $file OR ! is_uploaded_file($file) ) {
		log_message('Possible file upload attack: ' . filename_safe($file));
		return lang('upload_invalid_file');
	}

	return FALSE;
}


/**
 * directory
 *
 * A function to list all files within the specified directory
 * and it's sub-directories. This is a recursive function that
 * has no limit on the number of levels down you can search.
 *
 * $data['start_dir']   The directory to start searching from   (Required) ("./" = current dir, "../" = up one level)
 * $data['good_ext']    The file extensions to allow.           (Required) (set to 'array('all') to include everything)
 * $data['skip_files']  An array of files to skip.              (Required) (empty array if you don't want to skip anything)
 * $data['limit']       The limit of dir to search              (Required)
 * $data['base_dir']	The directory path to remove			(Optional)
 * $data['type']        Return files or Directories?            (Optional) (defaults to BOTH types but can also set to 'dir' or 'file')
 * $data['light']       Only return file name and path          (Optional) (defaults to false) (true or false)
 * $data['array']		Directory results as array (vs string)	(Optional) (defaults to false)
 *
 * @access	public
 * @param	array		the search config
 * @param	int			the currenct level
 * @return	Mixed
 */
if (!function_exists('directory')) {

	function directory($data, $level=1) {

		//Set optional params to false
		foreach(array('type', 'light', 'base_dir', 'array') as $type) {
			if(empty($data[$type])) {
				$data[$type] = false;
			}
		}

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
							$files[$path]['file'] = null;
							$files[$path]['ext'] = FALSE;
							$files[$path]['level'] = $level;

							//Remove the base dir from the path?
							$files[$path]['dir'] = $data['base_dir'] ? mb_substr($path, mb_strlen($data['base_dir'])) : $path;

							//If we are getting detailed data about the directory
							if(!$data['light']) {
								$files[$path]['stat'] = stat($path);
							}

						}

						//If the dir is NOT deeper than the limit && 'recursive' is set to true
						if($data['limit'] > $level){

							//Run this function on on the directory to see what is in it (this is where the recursive part starts)
							$files2 = directory(array(
										'start_dir' => $path. DIRECTORY_SEPARATOR, 'good_ext' => $data['good_ext'],
										'skip_files' => $data['skip_files'], 'limit' => $data['limit'],
										'type' => $data['type'], 'light' => $data['light'],
										'base_dir' => $data['base_dir']), $level + 1
							);

							//then combine the output with the current $files array
							if(is_array($files2) && ! $data['array']) {
								$files = array_merge($files, $files2);
							} else {
								$files[$path]['children'] = $files2;
							}
							$files2 = null;
						}

						//Else if it is a file
					} else {

						//get the extension of the file
						//$ext = preg_replace('/(.+)\.([a-z0-9]{2,4})/i', '\\2', $file);
						$ext = strrchr($file, '.');

						//And if it is in the GOOD file extension list OR if the list is set to allow ALL files
						if( (($data['good_ext'][0] == "all") || (array_search($ext, $data['good_ext']) > -1)) && (!$data['type'] || $data['type'] == 'file') ) {

							//Add the file to our list
							$files[$path]['file'] = $file;

							//Remove the base dir from the file path?
							$start = $data['base_dir'] ? strlen($data['base_dir']) : 0;

							//Get dir path
							$files[$path]['dir'] = substr($path, $start, -strlen($file));

							//Get the LAST "." followed by 0+ letters/numbers
							$files[$path]['ext'] = $ext;

							//Get the level of the file
							$files[$path]['level'] = $level;

							//If we are getting detailed data about the file
							if(!$data['light']) {
								$files[$path]['stat'] = stat($path);
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
				//ksort($files);
				uasort($files, 'sort_by_directory');
			}

			//Return the result
			return $files;

		}
	}
}


/**
 * Helper function to sort the array of files by directory,
 * then level, and finally file name.
 *
 * @param $a
 * @param $b
 * @return unknown_type
 */
function sort_by_directory($a, $b) {

	/*
	 * First sort by directory
	 */
	//If a is not a file and b is
	if(!$a['file'] && $b['file']) {
		return -1;
	}

	//If a is file and b is not
	if($a['file'] && !$b['file']) {
		return 1;
	}

	/*
	 * Then by level
	 */
	//If a is higher up
	if($a['level'] > $b['level']) {
		return 1;

		//If b is higher up
	} elseif ($a['level'] < $b['level']) {
		return -1;
	}

	/*
	 * Finally by file name
	 */
	return strcasecmp($a['file'], $b['file']);

	/*
	 * Or file size if you want...
	 */
	return ($a['stat']['size'] > $b['stat']['size'] ? -1 :
	($a['stat']['size'] < $b['stat']['size'] ? 1 : 0));

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
	if( ! $dh = opendir($dir) ) {
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
			if( ! destroy_directory($obj, $remove) ) {
				return FALSE;
			}

			//Else it must be a file
		} else {
			if( ! unlink($obj)) {
				return FALSE;
			}
		}

	}

	//Close the handle
	closedir($dh);

	if ( $remove && ! rmdir($dir) ){
		return FALSE;
	}

	return TRUE;
}



/**
 *
 * This function takes a filename as input and filters it to make it safe
 * for use. This includes sanitizing the name, encrypting the name (optional),
 * checking for the existence of a file with the same name, and prefixing
 * all secondary extensions with an underscore.
 *
 * @param	string	$file
 * @param	boolean	$overwrite
 * @param	boolean	$encrypt
 * @return	mixed
 */
function check_filename($file = '', $overwrite = FALSE, $encrypt = FALSE) {

	//Break up the path
	$pathinfo = pathinfo($file);

	//No filename or extension?
	if( empty($pathinfo['filename']) OR empty($pathinfo['extension']) ) {
		return FALSE;
	}

	//Break apart
	list($dirname, $basename, $extension, $filename) = $pathinfo;

	//Should we encrypt the filename?
	if ($encrypt) {

		//Set the file name to a random value
		$filename = md5(uniqid(rand()). $filename);

	} else {

		//Make the files name "filename" safe (no weird chars allowed like "/")
		$filename = filename_safe($filename);

		/*
		 * Separate extensions with "_" to prevent possible script execution
		 * from Apache's handling of files with multiple extensions.
		 * http://httpd.apache.org/docs/1.3/mod/mod_mime.html#multipleext
		 */
		if(strpos($filename, '.') !== FALSE) {

			//First, break apart the name into pieces
			$parts = explode('.', $filename);

			//add a underscore to the name to make it un-usable
			foreach($parts as &$part) {
				$part .= '_';
			}

			//Put the filename back togeither
			$filename = implode('.', $parts);
		}

	}

	//If we should take existing files into account and protect them
	if( ! $overwrite && file_exists($file) ) {

		//Then keep adding a number to the file name
		//until you find one that doesn't exist!
		for ($i = 1; $i < 1000; $i++) {
			if ( ! file_exists($dirname. DS. $filename. '_'. $i. '.'. $extension)) {
				$filename .= '_'. $i;
				break;
			}
		}
	}

	return array($dirname, $basename, $extension, $filename);
}


/**
 * Break a path apart so that we can use CSS to color different levels
 *
 * @param string	$path
 * @return string
 */
function highlight_path($path=null) {

	//Remove start and end slashes
	$path = trim($path, '\\\/');

	//If nothing is left - return empty
	if(! $path) { return; }

	//Break apart the path
	$path = explode(DS, $path);

	$output = '';
	$x=1;
	foreach($path as $value) {
		$output .= '<span class="level'. $x. '">'. $value. DS. '</span>';
		$x++;
	}

	return $output;
}


/**
 * Check that a directory path is a sub directory (within) the given parent
 * path. Please use absolute file system paths. Returns filtered absolute
 * path on success.
 *
 *
 * @param	string
 * @param	string
 * @return	mixed
 */
function is_sub_dir($path = NULL, $parent_folder = SITE_PATH) {

	//Get directory path minus last folder
	$dir = dirname($path);
	$folder = mb_substr($path, mb_strlen($dir));

	//Check the the base dir is valid
	$dir = realpath($dir);

	//Only allow valid filename characters
	if( preg_match('/[^a-z0-9\.\-_]/ui', $folder) ) {
		return FALSE;
	}

	//If this is a bad path or a bad end folder name
	if( !$dir OR !$folder OR $folder === '.') {
		return FALSE;
	}

	//Rebuild path
	$path = $dir. DS. $folder;

	//If this path is higher than the parent folder
	if( strcasecmp($path, $parent_folder) > 0 ) {
		return $path;
	}

	return FALSE;
}


/**
 * Checks that the directory path looks like a sub directory (within) the
 * given parent path.
 *
 * @param	string
 * @param	string
 * @return	mixed
 */
function looks_like_sub_dir($path = NULL, $parent_folder = SITE_PATH) {

	//If no path was given
	if( !$path OR ! trim($path, '/\\. ') ) {
		return FALSE;
	}

	$s = (DS === '/' ? '\\' : '/');

	// Normalize paths (WINDOWS to *NIX or vice-versa)
	$path = str_replace($s, DS, $path);

	// Don't allow relative links or multiple forward slashes
	if( strpos($path, DS. '..') !== FALSE OR strpos($path, DS.DS) !== FALSE) {
		return FALSE;
	}

	// Remove start and end slashes and dots
	$path = trim($path, DS. '. ');
	$parent_folder = trim($parent_folder, DS. '. ');

	$front = '';

	//If windows
	if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {

		//Allow "C:" and "AB:" styled dives
		$front = substr($path, 0, 4);
		$path = substr($path, 4);

		//If something else is here
		if( preg_match('/[^a-z:'. ( DS == '/' ? DS : '\\\\'). ']/i', $front) ) {
			return FALSE;
		}

	}

	//Allowed filesystem path chars
	$regex = '[^a-z0-9 \.\-_ '. ( DS == '/' ? DS : '\\\\'). ']';

	//If anything funny looking is found - fail (to be safe)
	if( preg_match('#'. $regex. '#i', $path) ) {
		return FALSE;
	}

	//Rejoin
	$path = $front. $path;

	//Must be the same starting path
	if( substr($path, 0, strlen($parent_folder)) != $parent_folder) {
		return FALSE;
	}

	//If this path is higher than the parent folder
	if( strcmp($path, $parent_folder) > 0 ) {
		return TRUE;
	}

	return FALSE;
}
