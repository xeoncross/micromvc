<?php
/**
 * File Uploading Class
 *
 * @package		MicroMVC
 * @author		David Pennington
 * @copyright	Copyright (c) 2009 MicroMVC
 * @license		http://www.gnu.org/licenses/gpl-3.0.html
 * @link		http://micromvc.com
 * @version		1.0.1 <5/31/2009>
 ********************************** 80 Columns *********************************
 */
class upload {
	
	//An array of error messages
	public $error_messages	= array();
	//The array to hold upload errors (if any)
	public $errors			= array();
	//The text to put before an error
	public $error_prefix	= '<p>';
	//The text to put after an error
	public $error_sufix		= '</p>';
	//Max file size in bytes - set to 0 to disable
	public $max_size		= 0;
	//List of allowed file extensions separated by "|"
	public $allowed_types	= 'gif|jpg|jpeg|png|zip|rar|tar|gz';
	//The temp name of the file
	public $file_temp		= '';
	//The new name of the file
	public $file_name		= '';
	//The type of the file
	public $file_type		= '';
	//The size of the file
	public $file_size		= '';
	//The file extension
	public $file_ext		= '';
	//The director to upload the file too
	public $upload_dir		= '';
	//If TRUE, we will overwrite files that already exist
	public $overwrite		= FALSE;
	//Should the file name be changed to a random hash value?
	public $encrypt_name	= FALSE;

	
	/*
	 * On object creation set the error messages
	 */
	public function __construct() {
		
		//Make the allowed types an array (if not already)
		if($this->allowed_types && !is_array($this->allowed_types)) {
			$this->allowed_types = explode('|', $this->allowed_types);
		}
		
		$errors = array();
		$errors['upload_no_file_selected'] = "You did not select a file to upload.";
		$errors['upload_bad_destination'] = 'The destination directory does not exist or is not writable.';
		$errors['upload_file_exceeds_limit'] = "The uploaded file exceeds the maximum allowed size in your PHP configuration file.";
		$errors['upload_file_exceeds_form_limit'] = "The uploaded file exceeds the maximum size allowed by the submission form.";
		$errors['upload_file_partial'] = "The file was only partially uploaded.";
		$errors['upload_no_temp_directory'] = "The temporary folder is missing.";
		$errors['upload_unable_to_write_file'] = "The file could not be written to disk.";
		$errors['upload_stopped_by_extension'] = "The file upload was stopped by extension.";
		$errors['upload_invalid_filetype'] = "The filetype you are attempting to upload is not allowed.";
		$errors['upload_invalid_filesize'] = "The file you are attempting to upload is larger than the permitted size.";
		$errors['upload_destination_error'] = "A problem was encountered while attempting to move the uploaded file to the final destination.";
		
		$this->error_messages = $errors;
		
	}
	
	
	/**
	 * Perform the file upload
	 *
	 * @access	public
	 * @return	bool
	 */
	function do_upload($field = 'userfile') {
		
		// Is $_FILES[$field] set? If not, no reason to continue.
		if ( ! isset($_FILES[$field])) {
			$this->set_error('upload_no_file_selected');
			return FALSE;
		}

		// Is the upload path valid?
		if ( ! directory_usable($this->upload_dir)) {
			$this->set_error('upload_bad_destination');
			return FALSE;
		}

		// Was the file able to be uploaded? If not, determine the reason why.
		if ( ! is_uploaded_file($_FILES[$field]['tmp_name'])) {
			
			$error = ( ! isset($_FILES[$field]['error'])) ? 4 : $_FILES[$field]['error'];

			switch($error) {
				case 1:	// UPLOAD_ERR_INI_SIZE
					$this->set_error('upload_file_exceeds_limit');
					break;
				case 2: // UPLOAD_ERR_FORM_SIZE
					$this->set_error('upload_file_exceeds_form_limit');
					break;
				case 3: // UPLOAD_ERR_PARTIAL
					$this->set_error('upload_file_partial');
					break;
				case 4: // UPLOAD_ERR_NO_FILE
					$this->set_error('upload_no_file_selected');
					break;
				case 6: // UPLOAD_ERR_NO_TMP_DIR
					$this->set_error('upload_no_temp_directory');
					break;
				case 7: // UPLOAD_ERR_CANT_WRITE
					$this->set_error('upload_unable_to_write_file');
					break;
				case 8: // UPLOAD_ERR_EXTENSION
					$this->set_error('upload_stopped_by_extension');
					break;
				default :   $this->set_error('upload_no_file_selected');
				break;
			}

			return FALSE;
		}
		
		//Clean the file name and also get the extension
		$this->process_filename($_FILES[$field]['name']);
		
		//Set the full file path
		$file_path = $this->upload_path. $this->file_name. $this->file_ext;
		
		// Set the uploaded data as class variables
		$this->file_temp = $_FILES[$field]['tmp_name'];
		$this->file_size = $_FILES[$field]['size'];
		$this->file_type = preg_replace("/^(.+?);.*$/", "\\1", $_FILES[$field]['type']);
	
		// Convert the file size to kilobytes
		if ($this->file_size > 0) {
			$this->file_size = round($this->file_size/1024, 2);
		}
		
		// Is the file type allowed to be uploaded?
		if ($this->allowed_types && ( ! in_array($this->file_ext, $this->allowed_types))) {
			$this->set_error('upload_invalid_filetype');
			return FALSE;
		}

		// Is the file size within the allowed maximum?
		if ($this->max_size != 0  AND  $this->file_size > $this->max_size) {
			$this->set_error('upload_invalid_filesize');
			return FALSE;
		}

		
		/*
		 * Move the file to the final destination
		 * To deal with different server configurations
		 * we'll attempt to use copy() first.  If that fails
		 * we'll use move_uploaded_file().  One of the two should
		 * reliably work in most environments
		 */
		if ( ! @copy($this->file_temp, $file_path)) {
			if ( ! @move_uploaded_file($this->file_temp, $file_path)) {
				$this->set_error('upload_destination_error');
				return FALSE;
			}
		}

		return TRUE;
	}


	/**
	 * Set the file name
	 *
	 * This function takes a filename as input and filters it to make it safe 
	 * for use. It also gets the file extension. Finally, it looks for the
	 * existence of a file with the same name. If found, it will append a
	 * number to the end of the filename to avoid overwriting a pre-existing file.
	 *
	 * @access	public
	 * @param	string
	 * @param	string
	 * @return	string
	 */
	function process_filename($filename='') {
		
		//First get the extension of the file
		$ext = strrchr($filename, '.');
		
		//Then get the file name
		$filename = ($ext === FALSE) ? $filename : substr($filename, 0, -strlen($ext));
		
		//Should we encrypt the filename?
		if ($this->encrypt_name == TRUE) {
			mt_srand();
			//Set the file name to a random value
			$filename = md5(uniqid(mt_rand()));
			
		} else {
		
			//Make the files name "filename" safe (no weird chars allowed like "/")
			$filename = sanitize_text($filename, 4);
			
			/*
			 * Separate extensions with "_" to prevent possible script execution 
			 * from Apache's handling of files with multiple extensions.
			 * http://httpd.apache.org/docs/1.3/mod/mod_mime.html#multipleext
			 */
			if(strpos($filename, '.')) {
				//First, break apart the name into pieces
				$parts = explode('.', $filename);
				
				foreach($parts as &$part) {
					//If this extension is not allowed
					if(!in_array($part, $this->allowed_types)) {
						//add a underscore to the name to make it un-usable
						$part .= '_';
					}
				}
				
				//Put the filename back togeither
				$filename = implode('.', $parts);
			}
			
		}
		
		
		//Check to see if the file with this name exists already
		if (file_exists($this->upload_dir. $filename. $ext)) {
			
			//If we should NOT overwrite the existing files
			if( ! $this->overwrite) {
				//Then keep adding a number to the file name 
				//until you find one that doesn't exist!
				for ($i = 1; $i < 1000; $i++) {
					if ( ! file_exists($this->upload_dir. $filename. $i. $ext)) {
						$filename .= $i;
						break;
					}
				}
			}
		}
		
		//Set the name and file extension
		$this->file_name = $filename;
		$this->file_ext = $ext;
		
	}


	/**
	 * Display the error message
	 *
	 * @access	public
	 * @param	string
	 * @param	string
	 * @return	string
	 */
	function display_errors() {
		//If no errors
		if(empty($this->errors)) {
			return;
		}

		$output = '';
		//Format each error
		foreach($this->errors as $error) {
			$output .= $this->error_prefix. $error. $this->error_sufix. "\n\n";
		}

		//Return the full errors string
		return $output;
	}


	/**
	 * Return the error (if any) for a given field
	 *
	 * @param $field
	 * @return string
	 */
	function display_error($field) {
		return empty($this->errors[$field]) ? NULL : $this->errors[$field];
	}

}
