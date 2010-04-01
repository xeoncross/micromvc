<?php defined('SYSTEM_PATH') or die('No direct access');
/**
 * UploadTest
 *
 * This controller demonstrates the use of the upload class to allow file 
 * uploads.
 *
 * Note, this controller is only for demonstration purposes! Remove this file
 * before you put your site online!
 *
 * @package		MicroMVC
 * @author		David Pennington
 * @copyright	(c) 2010 MicroMVC Framework
 * @license		http://micromvc.com/license
 ********************************** 80 Columns *********************************
 */

class Controller_UploadTest extends Controller {

	public function index()
	{

		//Load the upload library
		$uploads = new Uploads;
		
		// Only allow images
		$uploads->allowed_types = 'jpg|png|gif';

		//If there is a file given to upload *AND* the upload was a success!
		if($uploads->exists() && $uploads->do_upload())
		{
			//Show success page!
			$this->views['content'] = load::view('uploadtest/done');
			return;
		}

		//Else show the upload form
		$this->views['content'] = load::view('uploadtest/form', array('upload' => $uploads));

		// Load the sidebar
		$this->views['sidebar'] = load::view('sidebar');
	}

}
