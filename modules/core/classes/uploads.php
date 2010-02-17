<?php defined('SYSTEM_PATH') or die('No direct access');
/**
 * File Upload
 *
 * Allows easy uploading of files.
 *
 * @package		MicroMVC
 * @author		David Pennington
 * @copyright	(c) 2010 MicroMVC Framework
 * @license		http://micromvc.com/license
 ********************************** 80 Columns *********************************
 */
class uploads
{

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
	public $allowed_types	= 'gif|jpg|jpeg|png|txt|zip|rar|tar|gz';
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
	//Array of data about the file
	public $file_data		= array();
	//The folder directory to upload the file too (relative of the upload_path)
	public $upload_dir		= '';
	//If TRUE, we will overwrite files that already exist
	public $overwrite		= FALSE;
	//Should the file name be changed to a random hash value?
	public $encrypt_name	= FALSE;

	/*
	 * This is the root uploads directory path as set in the config.
	 * For saftey, it is recommened that you do not change this!
	 */
	public $upload_path		= UPLOAD_PATH;

	/**
	 * Verifiy that the given file upload field exists
	 * 
	 * @param	string
	 * @return	boolean
	 */
	function exists($field = 'userfile')
	{
		return ! empty($_FILES[$field]);
	}


	/**
	 * Perform the file upload
	 * 
	 * @param string
	 * @return mixed
	 */
	function do_upload($field = 'userfile')
	{

		// Is $_FILES[$field] set? If not, no reason to continue.
		if (empty($_FILES[$field]))
		{
			$this->set_error(UPLOAD_ERR_NO_FILE, $field);
			return FALSE;
		}
		
		//Normalize the destation folder
		$this->upload_dir = str_replace('\\', DS, trim($this->upload_dir, '/\\')). DS;

		//Create full upload path
		$path = $this->upload_path. $this->upload_dir;


		/*
		//Don't allow uploads lower than the upload path
		if( $this->upload_to && is_sub_dir($path, $this->upload_path) == FALSE ) {
			$this->set_error('upload_invalid_destination', $field);
			return FALSE;
		}
		*/

		// Is the upload path valid?
		if ( ! directory_usable($path))
		{
			$this->set_error('upload_bad_destination', $field);
			return FALSE;
		}

		// Was the file able to be uploaded? If not, determine the reason why.
		if ( ! is_uploaded_file($_FILES[$field]['tmp_name']))
		{
			//Check the error type
			$error = isset($_FILES[$field]['error']) ? $_FILES[$field]['error'] : 4;

			//Set the Error
			$this->set_error($error, $field);

			return FALSE;
		}

		//Make the allowed types an array (if not already)
		if($this->allowed_types AND ! is_array($this->allowed_types))
		{
			$this->allowed_types = explode('|', $this->allowed_types);
		}

		//Clean the file name and also get the extension
		$this->process_filename($_FILES[$field]['name'], $path);

		//Set the full file path
		$file_path = $path. $this->file_name. '.'. $this->file_ext;

		// Set the uploaded data as class variables
		$this->file_temp = $_FILES[$field]['tmp_name'];
		$this->file_size = $_FILES[$field]['size'];
		$this->file_type = preg_replace("/^(.+?);.*$/u", "\\1", $_FILES[$field]['type']);

		// Convert the file size to kilobytes
		if ($this->file_size > 0)
		{
			$this->file_size = round($this->file_size/1024, 2);
		}

		// Is the file type allowed to be uploaded?
		if ($this->allowed_types AND ( ! in_array($this->file_ext, $this->allowed_types)))
		{
			$this->set_error('upload_invalid_filetype', $field);
			return FALSE;
		}

		// Is the file size within the allowed maximum?
		if ($this->max_size != 0  AND  $this->file_size > $this->max_size)
		{
			$this->set_error('upload_invalid_filesize', $field);
			return FALSE;
		}


		/*
		 * Move the file to the final destination
		 * To deal with different server configurations
		 * we'll attempt to use copy() first.  If that fails
		 * we'll use move_uploaded_file().  One of the two should
		 * reliably work in most environments
		 */
		if ( ! @copy($this->file_temp, $file_path))
		{
			if ( ! @move_uploaded_file($this->file_temp, $file_path))
			{
				$this->set_error('upload_destination_error', $field);
				return FALSE;
			}
		}

		//Save data about the final file
		$this->file_data[$field] = array(
			'temp'	=> $this->file_temp,
			'name'	=> $this->file_name. '.'. $this->file_ext,
			'dir'	=> $this->upload_dir,
			'path'	=> $path,
			'ext'	=> $this->file_ext,
			'size'	=> $this->file_size,
			'type'	=> $this->file_type
		);

		//Return the array
		return $this->file_data[$field];
	}


	/**
	 * Set the file name
	 *
	 * This function takes a filename as input and filters it to make it safe
	 * for use. It also gets the file extension. Finally, it looks for the
	 * existence of a file with the same name. If found, it will append a
	 * number to the end of the filename to avoid overwriting a pre-existing file.
	 *
	 * @param string
	 * @param string
	 * @return string
	 */
	function process_filename($filename = '', $path = '')
	{

		//First get the extension of the file
		$ext = strrchr($filename, '.');

		//Then get the file name
		$filename = ($ext === FALSE) ? $filename : substr($filename, 0, -strlen($ext));

		//Should we encrypt the filename?
		if ($this->encrypt_name == TRUE)
		{
			mt_srand();
			//Set the file name to a random value
			$filename = md5(uniqid(mt_rand()));

		}
		else
		{
			//Make the file name safe (no weird chars allowed like "/")
			$filename = String::slug($filename);

			/*
			 * Separate extensions with "_" to prevent possible script execution
			 * from Apache's handling of files with multiple extensions.
			 * http://httpd.apache.org/docs/1.3/mod/mod_mime.html#multipleext
			 */
			if(strpos($filename, '.') !== FALSE)
			{
				//First, break apart the name into pieces
				$parts = explode('.', $filename);

				foreach($parts as &$part)
				{
					//If this extension is not allowed
					if(!in_array($part, $this->allowed_types))
					{
						//add a underscore to the name to make it un-usable
						$part .= '_';
					}
				}
				
				//Put the filename back togeither
				$filename = implode('.', $parts);
			}
		}


		//Check to see if the file with this name exists already
		if (file_exists($path. $filename. $ext))
		{
			//If we should NOT overwrite the existing files
			if( ! $this->overwrite)
			{
				//Then keep adding a number to the file name until you find one that doesn't exist!
				for ($i = 1; $i < 1000; $i++)
				{
					if ( ! file_exists($path. $filename. $i. $ext))
					{
						$filename .= $i;
						break;
					}
				}
			}
		}

		//Set the name and file extension
		$this->file_name = $filename;
		$this->file_ext = substr($ext, 1);

	}


	/**
	 * Display the error message for a given field
	 *
	 * @param string
	 * @param boolean
	 * @return string
	 */
	function display_errors($field = 'userfile', $prefix = TRUE)
	{
		if(empty($this->errors[$field]))
		{
			return FALSE;
		}

		if($prefix)
		{
			return $this->error_prefix. $this->errors[$field]. $this->error_sufix;
		}

		return $this->errors[$field];
	}


	/**
	 * Add an error message to the array
	 * @param string $line
	 * @param string $field
	 */
	function set_error($line, $field = 'userfile')
	{
		$this->errors[$field] = lang($line);
	}
}
