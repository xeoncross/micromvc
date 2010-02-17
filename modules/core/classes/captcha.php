<?php defined('SYSTEM_PATH') or die('No direct access');
/**
 * Captcha Class
 *
 * Creates Captcha images and math problems for forms
 *
 * @package		MicroMVC
 * @author		David Pennington
 * @copyright	(c) 2010 MicroMVC Framework
 * @license		http://micromvc.com/license
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

		//IF no text for the captcha image was set
		if(!$text) {
			$text = random_charaters(6, TRUE);
		}

		//IF no size is set = defualt to "3"
		if(!$size) {$size=3;}

		$font = 4;
		$cosrate = rand(10,19);
		$sinrate = rand(10,18);


		$charwidth = @imagefontwidth($font);
		$charheight = @imagefontheight($font);
		$width=(strlen($text)+2)*$charwidth;
		$height=1.5*$charheight;

		$im = @imagecreatetruecolor($width, $height)
		or trigger_error('Cannot Initialize new GD image stream! (is GD installed?)');
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
		imagestring($im, $font, $charwidth, $charheight/3, $text, $fcol);

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

		/*
		//Adds the same number of vertical lines as chars (2px thick each)
		$start = rand(0, 10);
		for($a = 1; $a <= strlen($text); $a++) {
			$x = $start+$a*30;
			$color = imagecolorallocate($im2, rand(90,120), rand(90,120), rand(90,120));
			imageline($im2, $x, 0, $x, $height*$size, $color);
			imageline($im2, $x + 1, 0, $x + 1, $height*$size, $color);
		}
		*/


		//Adds three polygons to radom places
		for($a = 1; $a < 4; $a++) {
			$points = array(
				rand(0, $width*$size),
				rand(0, $height*$size),
				rand(0, $width*$size),
				rand(0, $height*$size),
				rand(0, $width*$size),
				rand(0, $height*$size),
				rand(0, $width*$size),
				rand(0, $height*$size)
			);

			imagepolygon($im2, $points, 4, imagecolorallocate($im2, rand(60, 120),rand(60, 120),rand(60, 120)));
		};


		//If we are just sending this to the browser
		if(empty($file)) {
			header('Content-type: image/png');
		}

		//Create final png file
		imagepng($im2, $file);

		//Destroy the copies
		imagedestroy($im);
		imagedestroy($im2);

		return $text;
	}


	//functions to extract RGB values from combined 24bit color value
	public function rbg_red($col) {return (($col >> 8) >> 8) % 256;}
	public function rbg_green($col) {return ($col >> 8) % 256;}
	public function rbg_blue($col) {return $col % 256;}


	/**
	 * Create a text based math captcha. Returns
	 * and array of the numeric answer and textual
	 * question.
	 *
	 * @param	int	$min
	 * @param	int	$max
	 * @param	bool $textual
	 * @return	array
	 */
	function math_captcha($min = 0, $max = 10, $textual = TRUE) {

		//textual numbers zero to tweenty
		$numbers = array(
			'zero', 'one', 'two', 'three', 'four', 'five', 'six',
			'seven', 'eight', 'nine', 'ten', 'eleven', 'twelve',
			'thirteen', 'fourteen', 'fifteen', 'sixteen', 'seventeen',
			'eighteen', 'nineteen', 'twenty'
		);

		$first = rand($min, $max);
		$second = rand($min, $max);
		$operator = rand(0, 1);

		//Force the first number to be larger than the second
		if( ! $operator && $first < $second) {

			while( $first < $second ) {
				$first = rand($min, $max);
				$second = rand($min, $max);
			}
		}

		//Solve answer
		$answer = $operator ? $first + $second : $first - $second;

		//Question in textual form?
		if( $textual ) {
			$string = $numbers[$first] . ($operator ? ' plus ' : ' minus '). $numbers[$second];

		} else {
			$string = $first. ($operator ? ' + ' : ' - '). $second;
		}

		//Return the math question and answer
		return array('answer' => $answer, 'question' => $string);

	}
}

