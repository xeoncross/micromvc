<?php defined('SYSTEM_PATH') or die('No direct access');
/**
 * GD Class
 *
 * Handles scaling, cropping, and converting images to given sizes, aspect 
 * ratios, and even file formats. Definitely not you average image library!
 * Requires the GD extension and only works with PNG, GIF, and JPEG formats.
 *
 * @package		MicroMVC
 * @author		David Pennington
 * @copyright	(c) 2010 MicroMVC Framework
 * @license		http://micromvc.com/license
 ********************************** 80 Columns *********************************
 */
class gd {

	//Should we overwrite existing files?
	public $overwrite			= FALSE;
	//Location to save thumbnails
	public $destination_path	= FALSE;
	//Should we print to the screen?
	public $dynamic_output		= FALSE;
	//Should we add the px size to the filename? (file_[width]x[height].ext)
	public $append_dimensions	= TRUE;
	//Quality of image from 0 (no compression) to 9.
	public $quality				= 8;
	//The aspect ratio ratio to enforce (In format "4:3" or "1:1")
	public $aspect_ratio		= FALSE;
	//The new size of process images (In format "[width]x[height]")
	public $dimensions			= FALSE;
	//Save files as what type? ('gif', 'jpg', or 'png')
	public $save_as				= FALSE;
	//Save transparency? (for PNG/GIF images)
	public $transparency		= TRUE;


	public function __construct()
	{
		//Default to the thumbnails folder
		$this->destination_path = UPLOAD_PATH. 'thumbnails/';
		
		// GD must be compiled
		if( ! function_exists('gd_info'))
		{
			throw new Exception('The GD extension is not installed');
		}
	}


	//Generate a hash of a given file
	public function image_hash($filename = '', $dimensions = NULL, $aspect_ratio = NULL, $quality = NULL)
	{

		// If values not given - use object values
		$dimensions = $dimensions ? $dimensions : $this->dimensions;
		$quality = $quality ? $quality : $this->quality;
		$aspect_ratio = $aspect_ratio ? $aspect_ratio : $this->aspect_ratio;

		//return sha1($filename). '_'. $dimensions. '_'. $quality. '_'. str_replace(':', 'to', $aspect_ratio);
		return ($dimensions ? $dimensions : 'full')
			.'/'. str_replace(':', 'to', $aspect_ratio)
			.'/'. $quality
			.'/'. sha1($filename);
	}


	/*
	 * Shortcut wrapper to create a small thumbnail of a file
	 */
	public function thumbnail($file = '', $dimensions = '100x', $aspect_ratio = '2:1')
	{

		//Set options
		$this->dimensions = $dimensions;
		$this->aspect_ratio = $aspect_ratio;

		$new_file = $this->process($file);

		//Reset now
		$this->dimensions = FALSE;
		$this->aspect_ratio = FALSE;

		//Remove filesystem paths from filenames
		$file = remove_path($file);
		$new_file = remove_path($new_file);

		//Return array of data about this thumbnail
		return array(
			'path'	=> $new_file, //filepath of new thumbnail
			'url'	=> normalize_path(UPLOAD_URL. $new_file), //URL to the new image
			'link'	=> normalize_path(UPLOAD_URL. $file)	//Link to the orginal image
		);

	}


	/**
	 * Process an image, optionally croping to an aspect ratio and/or
	 * resizing it. If the second param is FALSE then we will save the
	 * new image as a hash in the thumbnails folder. If it is a string
	 * then we will save it in the same folder as the parent $image.
	 *
	 * @param string $file the path to the image
	 * @param string|bool $new_file the optional new name of a file
	 * @return string|bool
	 */
	public function process($file = '', $new_file = FALSE)
	{

		//Break up the path
		$pathinfo = pathinfo($file);

		//No filename or extension?
		if(empty($pathinfo['filename']) OR empty($pathinfo['extension']))
			return FALSE;

		$dirname  = $pathinfo['dirname'];
		$basename = $pathinfo['basename'];
		$ext = $pathinfo['extension'];
		$filename = $pathinfo['filename'];

		//Force JPEG's into JPG
		$ext = ($ext === 'jpeg' ? 'jpg' : $ext);

		//Must be a jpg, gif or png
		if( ! in_array($ext, array('jpg', 'png', 'gif')))
			return FALSE;

		// If we are saving this file to the server, then parse the new file name
		if( ! $this->dynamic_output)
		{
			// New file will be auto-placed in the thumbnail folder
			if( ! $new_file)
			{
				$new_file = $this->destination_path. $this->image_hash($filename);
				
				//Break up the path
				$pathinfo = pathinfo($new_file);
				
				// If the directory does NOT exist
				if( ! file_exists($pathinfo['dirname']))
				{
					// Create it!
					mkdir($pathinfo['dirname'], 0777, TRUE);
				}

			}
			elseif ($this->append_dimensions AND  $this->dimensions)
			{
				$new_file .= '_'. $this->dimensions;
			}

			//Add file extension
			$new_file .= '.'. ($this->save_as ? $this->save_as : $ext);

			
			//If a folder path is not set (if a only a new "name" was given)
			if(strpos($new_file, '/') === FALSE)
			{
				// Use the original images path
				$new_file = $dirname. '/'. $new_file;
			}
			
			//If this file already exists - then we are done!
			if( ! $this->overwrite AND file_exists($new_file))
				return $new_file;

		}
		else
		{
			$new_file = NULL;
		}


		// Choose function from ext
		$func = 'imagecreatefrom'. ($ext === 'jpg' ? 'jpeg' : $ext);
		
		// Load parent image
		$image = $func($file);

		// If we couldn't load the image
		if ( ! is_resource($image))
		{
			$this->error = lang('gd_bad_image_file');
			return FALSE;
		}

		$x = 0;
		$y = 0;
		$width = imagesx($image);		//Get image width
		$height = imagesy($image);		//Get image height
		$crop = $this->aspect_ratio;	//Fetch aspect ratio to crop too
		$size = $this->dimensions;		//Get final image dimensions
		$save_as = $ext;				//Save file as the same type

		//If we want to save as a different type than what the image is
		if($this->save_as)
		{
			$save_as = $this->save_as;
		}


		// CROP (Aspect Ratio) Section
		if ( ! $crop)
		{
			$crop = array($width, $height); //If we are NOT to crop this image
		}
		else
		{
			//Split the ratio
			$crop = array_filter(explode(':', $crop));

			//If invalid
			if (empty($crop))
			{
				$crop = array($width, $height);
			}
			else
			{
				//If the width is missing - default to height
				if (empty($crop[0]) OR ! is_numeric($crop[0]))
				{
					$crop[0] = $crop[1];

					//If the height is missing - default to width
				}
				elseif (empty($crop[1]) OR ! is_numeric($crop[1]))
				{
					$crop[1] = $crop[0];
				}
			}

			$ratio = array(0 => $width / $height, 1 => $crop[0] / $crop[1]);

			if ($ratio[0] > $ratio[1])
			{
				$width = $height * $ratio[1];
				$x = (imagesx($image) - $width) / 2;
			}
			elseif ($ratio[0] < $ratio[1])
			{
				$height = $width / $ratio[1];
				$y = (imagesy($image) - $height) / 2;
			}

		}

		// Resize Section
		if ( ! $size)
		{
			$size = array($width, $height);
		}
		else
		{
			$size = array_filter(explode('x', $size));

			if (empty($size))
			{
				$size = array(imagesx($image), imagesy($image));
			}
			else
			{
				if (empty($size[0]) OR ! is_numeric($size[0]))
				{
					$size[0] = round($size[1] * $width / $height);
				}
				elseif (empty($size[1]) OR ! is_numeric($size[1]))
				{
					$size[1] = round($size[0] * $height / $width);
				}
			}
		}

		//Create a new empty image the correct size
		$result = imagecreatetruecolor($size[0], $size[1]);

		if ( ! is_resource($result))
			return FALSE;

		// Preserve transparency of PNG/GIF images
		if ($this->transparency AND ($ext === 'png' OR $ext === 'gif'))
		{
			$transparency = imagecolortransparent($image);

			if ($transparency >= 0)
			{
				$transparent_color = imagecolorsforindex($image, $transparency);
				$transparency = imagecolorallocate($image_resized, $transparent_color['red'], $trnprt_color['green'], $trnprt_color['blue']);
				imagefill($result, 0, 0, $transparency);
				imagecolortransparent($result, $transparency);
			}
			elseif ($ext === 'png')
			{
				imagealphablending($result, false);
				$color = imagecolorallocatealpha($result, 0, 0, 0, 127);
				imagefill($result, 0, 0, $color);
				imagesavealpha($result, true);
			}

		}
		else
		{
			//Fill image background with white
			imagefill($result, 0, 0, imagecolorallocate($result, 255, 255, 255));
		}

		//Copy and resize image with resampling
		imagecopyresampled($result, $image, 0, 0, $x, $y, $size[0], $size[1], $width, $height);

		//Enable interlace
		//imageinterlace($result, true);

		if($this->dynamic_output)
		{
			//There is no destination file so we are just printing to the browser.
			header('Content-type: image/'. ($ext == 'jpg' ? 'jpeg' : $ext));
		}

		//Output the correct file (or send to the browser)
		if ($save_as === 'png')
		{
			imagepng($result, $new_file, $this->quality);
		}
		elseif ($save_as === 'gif')
		{
			imagegif($result, $new_file);
		}
		else
		{
			imagejpeg($result, $new_file, $this->quality * 10);
		}

		imagedestroy($result);
		imagedestroy($image);

		//Return the name of the new file
		return $new_file;
	}

}

