<?php
/**
 * Captcha
 *
 * Creates CAPTCHA images for forms
 *
 * @package		MicroMVC
 * @author		Ioannis Cherouvim
 * @author		David Pennington
 * @copyright	Copyright (c) 2008 CodeXplorer
 * @license		http://www.gnu.org/licenses/gpl-3.0.html
 * @link		http://hack.gr
 * @link		http://codexplorer.com
 * @version		1.0.0 <9/23/2008>
 ********************************** 80 Columns *********************************
 */

/**
 *
 * Sometimes you need to verify that the client posting
 * or registering in your site is actually a human. By
 * asking him/her to type in the word s/he sees, you ensure
 * that the client is human, and not a bot/spider which
 * is probably trying to harm your system.
 * Play around with the values when constructing this
 * object, but also feel free to experiment with the
 * maths inside the image manipulating loops.
 * Note that this class is writing a png file on disk,
 * so you might need to have a png with the same name
 * already present in that location with write
 * permissions set.
 *
 * Note: you must have the GDImage lib installed!
 */

class captcha {

	/**
	 * Create
	 *
	 * Create a captcha PNG image
	 *
	 * @param	string	the text for the captcha
	 * @param	string	the file name
	 * @param	array	params to pass to the model constructor
	 * @return	void
	 */

	public function create($text=null, $file=null, $size=null) {
	  
		//Default to {site_root}/captcha.png
		$file = SITE_DIR. ($file ? $file : 'captcha'). '.png';
	  
	  
		//IF no text for the captcha image was set
		if(!$text) {
			//trigger_error('No text for the CAPTCHA was given', E_USER_NOTICE);
			//return;
			$text = random_charaters(6);
		}
	  
		//IF no size is set = defualt to "3"
		if(!$size) {$size=3;}

		$font = 4;
		$cosrate = rand(10,19);
		$sinrate = rand(10,18);
	  
	  
		$charwidth = @imagefontwidth($font);
		$charheight = @imagefontheight($font);
		$width=(strlen($text)+2)*$charwidth;
		$height=2*$charheight;

		$im = @imagecreatetruecolor($width, $height)
		or trigger_error('Cannot Initialize new GD image stream! (Is GD installed?)');
		$im2 = imagecreatetruecolor($width*$size, $height*$size);
	  
		//Here we make the background and text alternate between light and dark
		$bcol = imagecolorallocate($im, rand(80,100), rand(80,100), rand(80,100));
		$fcol = imagecolorallocate($im, rand(150,200), rand(150,200), rand(150,200));
	  
	  
		imagefill($im, 0, 0, $bcol);
		imagefill($im2, 0, 0, $bcol);
	  
		$dotcol = imagecolorallocate($im, (abs($this->rbg_red($fcol)-$this->rbg_red($bcol)))/4,
		(abs($this->rbg_green($fcol)-$this->rbg_green($bcol)))/4,
		(abs($this->rbg_blue($fcol)-$this->rbg_blue($bcol)))/4);
	  
		$dotcol2 = imagecolorallocate($im, (abs($this->rbg_red($fcol)-$this->rbg_red($bcol)))/2,
		(abs($this->rbg_green($fcol)-$this->rbg_green($bcol)))/2,
		(abs($this->rbg_blue($fcol)-$this->rbg_blue($bcol)))/2);
	  
		$linecol = imagecolorallocate($im, (abs($this->rbg_red($fcol)-$this->rbg_red($bcol)))/2,
		(abs($this->rbg_green($fcol)-$this->rbg_green($bcol)))/2,
		(abs($this->rbg_blue($fcol)-$this->rbg_blue($bcol)))/2);
	  
	  
		//Groups and warps Pixels
		for($i=0; $i<$width; $i=$i+rand(0,2)) {
			for($j=0; $j<$height; $j=$j+rand(0,2)) {
				imagesetpixel($im, $i, $j, $dotcol);
			}
		}
	  
		//Adds Text
		imagestring($im, $font, $charwidth, $charheight/2, $text, $fcol);
	  
		/*
		 //Adds Horizontal lines
		 for($j=0; $j<$height*$size; $j=$j+rand(2,6)) {
		 imageline($im2, 0, $j, $width*$size, $j, $linecol);
		 }
		 */
	  
		/*
		 //Adds Vertical lines
		 for($i=0; $i<$width*$size; $i=$i+rand(1,19)) {
		 imageline($im2, $i, 0, $i, $height*$size, $linecol);
		 }
		 */
	  
		//Adds horizontal dots
		for($i=0; $i<$width*$size; $i++) {
			for($j=0; $j<$height*$size; $j++) {
				$x = abs(((cos($i/$cosrate)*5+sin($j/$sinrate*2)*2+$i)/$size))%$width;
				$y = abs(((sin($j/$sinrate)*5+cos($i/$cosrate*2)*2+$j)/$size))%$height;
				$col = imagecolorat($im, $x, $y);
				if ($col!=$bcol) imagesetpixel($im2, $i, $j, $col);
			}
		}
	  
		//Adds more horizontal dots
		for($j=0; $j<$height*$size; $j=$j+rand(2,5)) {
			for($i=0; $i<$width*$size; $i=$i+rand(2,5)) {
				imagesetpixel($im2, $i, $j, $dotcol2);
			}
		}
	  
		//Adds the same number of vertical lines as chars
		$start = rand(0, 10);
		for($a = 1; $a < strlen($text); $a++) {
			imageline($im2, $start+$a*30, 0, $start+$a*30, $height*$size, imagecolorallocate($im2, rand(90,120), rand(90,120), rand(90,120)));
		}
	  
		//Adds three polygons to radom places
		for($a = 1; $a < 4; $a++) {
			imagepolygon(
			$im2,
			array(
			rand(0, $width*$size),
			rand(0, $height*$size),
			rand(0, $width*$size),
			rand(0, $height*$size),
			rand(0, $width*$size),
			rand(0, $height*$size),
			rand(0, $width*$size),
			rand(0, $height*$size)
			),
			4,
			ImageColorAllocate($im2, rand(60, 120),rand(60, 120),rand(60, 120))
			);
		};
	  
		//Create final png file
		imagepng($im2, $file)
		or trigger_error('Couldn\'t create CAPTCHA PNG: '. $file, E_USER_WARNING);
	  
		//Destroy the copies
		imagedestroy($im);
		imagedestroy($im2);
		
		return $text;
	}


	//functions to extract RGB values from combined 24bit color value
	public function rbg_red($col) {return (($col >> 8) >> 8) % 256;}
	public function rbg_green($col) {return ($col >> 8) % 256;}
	public function rbg_blue($col) {return $col % 256;}

}

?>