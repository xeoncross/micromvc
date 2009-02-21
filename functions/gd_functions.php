<?php
/**
 * GD functions
 *
 * This file contains a growing list of functions 
 * related to working with images using the GD lib
 * for php. Examples include merging jpeg's and 
 * cropping images. Currently, only png, gif, and jpeg
 * file types are supported by this file.
 *
 * @package		MicroMVC
 * @author		David Pennington
 * @copyright	Copyright (c) 2009 MicroMVC
 * @license		http://www.gnu.org/licenses/gpl-3.0.html
 * @link		http://micromvc.com
 * @version		1.0.0 <2/20/2009>
 ********************************** 80 Columns *********************************
 */
 
///////////////////////////////////////////////////////////
// A function to create thumbnails (small copies) of an image
// and then store (and name) the new thumbnail. 
///////////////////////////////////////////////////////////
// What info does this function need?
/////////////////
//
// $data['file_name']           The name of the image to create a thumbnail of          (Required) ("./images/myimage.jpg")
// $data['new_file_name']       The name of the new thumbnail image                     (Required) ("./images/thumbs/myimage.jpg)
// $data['width']               The width you want the thumbnail to be                  (Required) (any number you want)
// $data['height']              The height you want the thumbnail to be                 (Required) (any number you want)
// $data['ext']                 The type of image we are making a thumbnail of          (Required) "jpg", "gif", or "png" ONLY!
// $data['quality']             The quality of the thumbnail                            (Required) (a number from 0-100)
//
/////////////////
// Example data
/////////////////
//
// $data['file_name']           = "./images/myimage.jpg";
// $data['new_file_name']       = "./images/thumbs/myimage_thumb.jpg;
// $data['width']               = 200;
// $data['height']              = 200;
// $data['ext']                 = 'jpg';
// $data['quality']             = 80;
//
//////////////////////////////////////////////////
// Core of createthumb function taken from 
// http://icant.co.uk/articles/phpthumbnails/
// by Christian Heilmann
//////////////////////////////////////////////////
function createthumb($data) {

    if(!isset($data['quality'])) {
        $data['quality'] = 80;
    }
    
    if($data['ext'] == 'png') {
        $src_img=imagecreatefrompng($data['file_name']);
    } elseif ($data['ext'] == 'gif') {
        $src_img =imagecreatefromgif($data['file_name']);
    } else {
        $src_img=imagecreatefromjpeg($data['file_name']);
    }

    if (!$src_img) { /* See if it failed */
    
        trigger_error('Couldn\'t load image', E_USER_WARNING);
        
        $src_img  = imagecreatetruecolor($data['width'], $data['height']); /* Create a black image */
        $bgc = imagecolorallocate($src_img, 255, 255, 255);
        $tc  = imagecolorallocate($src_img, 255, 0, 0);
        imagefilledrectangle($src_img, 0, 0, $data['width'], $data['height'], $bgc);
        /* Output an errmsg */
        imagestring($src_img, 2, 5, 5, "Opps! Error Loading Image!", $tc);
    }
    
    $old_x=imageSX($src_img);
    $old_y=imageSY($src_img);
    if ($old_x > $old_y) {
        $thumb_w=$data['width'];
        $thumb_h=$old_y*($data['height']/$old_x);
    }
    if ($old_x < $old_y) {
        $thumb_w=$old_x*($data['width']/$old_y);
        $thumb_h=$data['height'];
    }
    if ($old_x == $old_y) {
        $thumb_w=$data['width'];
        $thumb_h=$data['height'];
    }
    $dst_img=ImageCreateTrueColor($thumb_w,$thumb_h);
    imagecopyresampled($dst_img,$src_img,0,0,0,0,$thumb_w,$thumb_h,$old_x,$old_y);
    
    if ($data['ext'] == 'png') {
        imagepng($dst_img,$data['new_file_name'],$data['quality']);
    } elseif ($data['ext'] == 'gif') {
        imagegif($dst_img,$data['new_file_name'],$data['quality']);
    } else {
        imagejpeg($dst_img,$data['new_file_name'],$data['quality']); 
    }
    
    imagedestroy($dst_img); 
    imagedestroy($src_img); 
}






///////////////////////////////////////////////////////////
// A function to crop a gif, jpeg, or png image based on a start axis 
// and lenght/height values. NOTE: if "new_image" and "image" are 
// the same file - "image" will be over-written.
///////////////////////////////////////////////////////////
// What info does this function need?
/////////////////
//
// $data['image']               = (Required) (The image to crop: png, gif, or jpg only!)
// $data['new_image']           = (Required) The name of the new image file 
// $data['x']                   = (Required) The X coordinate to start the crop from
// $data['y']                   = (Required) The Y coordinate to start the crop from
// $data['width']               = (Required) The number of pixels to continue the x cut on
// $data['height']              = (Required) The number of pixels to continue the y cut on
// $data['quality']             = (Required) The quality of the final image (0 to 100)
//
/////////////////
// Example data
/////////////////
//
// $data['image']               = "myimage.jpg";
// $data['new_image']           = "myimage_cropped.jpg";
// $data['x']                   = 0;
// $data['y']                   = 0;
// $data['width']               = 88;
// $data['height']              = 31;
//
//////////////////////////////////////////////////
function crop_image($data){

    //Check to see if the file exists
    if(!file_exists($data['image'])) { return false; }

    //Get the file type
    //$end = end(explode('.', $data['image']));
    
    //Get the file type
    $info = getimagesize($data['image']);
    
    /*
    // Posible image types returned by getimagesize()
    $types = array(
        1 => 'GIF',
        2 => 'JPG',
        3 => 'PNG',
        4 => 'SWF',
        5 => 'PSD',
        6 => 'BMP',
        7 => 'TIFF(intel byte order)',
        8 => 'TIFF(motorola byte order)',
        9 => 'JPC',
        10 => 'JP2',
        11 => 'JPX',
        12 => 'JB2',
        13 => 'SWC',
        14 => 'IFF',
        15 => 'WBMP',
        16 => 'XBM'
    );
    */
    
    //Find the matching image type
    switch($info[2]) {
    
        //It is a GIF Image
        case 1:
            //If we successfully crop the gif
            if(crop_gif($data)) { return true; }
        break;
    
    
        //It is a JPEG Image
        case 2:
            //If we successfully crop the jpeg
            if(crop_jpeg($data)) { return true; }
        break;
        
        
        //It is a PNG Image
        case 3: 
            //If we successfully crop the png
            if(crop_png($data)) { return true; }
        break;
        
        
        default:
            //If we don't support the type of image
            return false;
    }

}



///////////////////////////////////////////////////////////
// A function to crop a gif image based on a start axis and 
// lenght/height values. NOTE: if "new_image" and "image" are 
// the same file - "image" will be over-written.
///////////////////////////////////////////////////////////
// What info does this function need?
/////////////////
//
// $data['image']               = (Required) (The image to crop)
// $data['new_image']           = (Required) The name of the new image file 
// $data['x']                   = (Required) The X coordinate to start the crop from
// $data['y']                   = (Required) The Y coordinate to start the crop from
// $data['width']               = (Required) The number of pixels to continue the x cut on
// $data['height']              = (Required) The number of pixels to continue the y cut on
// $data['quality']             = (Required) The quality of the final image (0 to 100)
//
/////////////////
// Example data
/////////////////
//
// $data['image']               = "myimage.gif";
// $data['new_image']           = "myimage_cropped.gif";
// $data['x']                   = 0;
// $data['y']                   = 0;
// $data['width']               = 88;
// $data['height']              = 31;
//
//////////////////////////////////////////////////
function crop_gif($data) {

    //If this build of GD doesn't have gif support
    if(!function_exists('imagecreatefromgif')) { return false; }
    
    //Open a copy of the original image that we can work with
    $orginal_image = @imagecreatefromgif($data['image']);
    
    //Create a blank image the size of the cropped area
    $new_image = @imagecreatetruecolor($data['width'],$data['height']);
    
    //If we couldn't make either image
    if( (!$orginal_image) || (!$new_image) ) {
        trigger_error('Could not create new image/open image file');
        return false;
    }
    
    //Paste the original image over the new cropped image
    imagecopy($new_image, $orginal_image, 0, 0, $data['x'], $data['y'], $data['width'], $data['height']);
    
    //Save the new image with the pasted/cropped copy of the old image to the hard drive
    if(!imagegif($new_image, $data['new_image'])) { 
        trigger_error('Could not save new gif image'. $data['new_image']);
        return false;
    }
    
    //Distroy both images
    imagedestroy($new_image);
    imagedestroy($orginal_image);

    return true;
}



///////////////////////////////////////////////////////////
// A function to crop a jpeg image based on a start axis and 
// lenght/height values. NOTE: if "new_image" and "image" are 
// the same file - "image" will be over-written.
///////////////////////////////////////////////////////////
// What info does this function need?
/////////////////
//
// $data['image']               = (Required) (The image to crop)
// $data['new_image']           = (Required) The name of the new image file 
// $data['x']                   = (Required) The X coordinate to start the crop from
// $data['y']                   = (Required) The Y coordinate to start the crop from
// $data['width']               = (Required) The number of pixels to continue the x cut on
// $data['height']              = (Required) The number of pixels to continue the y cut on
// $data['quality']             = (Required) The quality of the final image (0 to 100)
//
/////////////////
// Example data
/////////////////
//
// $data['image']               = "myimage.jpg";
// $data['new_image']           = "myimage_cropped.jpg";
// $data['x']                   = 0;
// $data['y']                   = 0;
// $data['width']               = 300;
// $data['height']              = 300;
// $data['quality']             = 80;
//
//////////////////////////////////////////////////
function crop_jpeg($data) {
    //If this build of GD doesn't have jpeg support
    if(!function_exists('imagecreatefromjpeg')) { return false; }
    
    //Open a copy of the original image that we can work with
    $orginal_image = @imagecreatefromjpeg($data['image']);
    
    //Create a blank image the size of the cropped area
    $new_image = @imagecreatetruecolor($data['width'],$data['height']);
    
    //If we couldn't make either image
    if( (!$orginal_image) || (!$new_image) ) {return false; }
    
    //Paste the original image over the new cropped image
    imagecopy($new_image, $orginal_image, 0, 0, $data['x'], $data['y'], $data['width'], $data['height']);
    
    //Save the new image with the pasted/cropped copy of the old image to the hard drive
    if(!imagejpeg($new_image, $data['new_image'], $data['quality'])) {
        
        trigger_error('Could not save new jpg image'. $data['new_image']);
        return false;
    }
    
    //Distroy both images
    imagedestroy($new_image);
    imagedestroy($orginal_image);

    return true;
}





///////////////////////////////////////////////////////////
// A function to crop a png image based on a start axis and 
// lenght/height values. NOTE: if "new_image" and "image" are 
// the same file - "image" will be over-written.
///////////////////////////////////////////////////////////
// What info does this function need?
/////////////////
//
// $data['image']               = (Required) (The image to crop)
// $data['new_image']           = (Required) The name of the new image file 
// $data['x']                   = (Required) The X coordinate to start the crop from
// $data['y']                   = (Required) The Y coordinate to start the crop from
// $data['width']               = (Required) The number of pixels to continue the x cut on
// $data['height']              = (Required) The number of pixels to continue the y cut on
//
/////////////////
// Example data
/////////////////
//
// $data['image']               = "myimage.png";
// $data['new_image']           = "myimage_cropped.png";
// $data['x']                   = 0;
// $data['y']                   = 0;
// $data['width']               = 300;
// $data['height']              = 300;
//
//////////////////////////////////////////////////
function crop_png($data) {

    //If this build of GD doesn't have png support
    if(!function_exists('imagecreatefrompng')) { return false; }
    
    //Open a copy of the original image that we can work with
    $orginal_image = imagecreatefrompng($data['image']);
    
    //Create a blank image the size of the cropped area
    $new_image = imagecreatetruecolor($data['width'],$data['height']);
    
    //If we couldn't make either image
    if( (!$orginal_image) || (!$new_image) ) {return false; }
    
    //Allows for two different modes of drawing on truecolor images
    if(!imagealphablending($new_image, false)){ return false; }

    //Save full alpha channel information (as opposed to single-color transparency)
    if(!imagesavealpha($new_image, true)){ return false; }

    //Paste the original image over the new cropped image
    imagecopy($new_image, $orginal_image, 0, 0, $data['x'], $data['y'], $data['width'], $data['height']);
    
    //Save the new image with the pasted/cropped copy of the old image to the hard drive
    if(!imagepng($new_image, $data['new_image'])) { 
        trigger_error('Could not save new png image'. $data['new_image']);
        return false;
    }

    //Distroy both images
    imagedestroy($new_image);
    imagedestroy($orginal_image);
    
    return true;
}






///////////////////////////////////////////////////////////
// A function to merge two jpeg images onto one canvas.
// The base image is the bottom image and the cover image is the 
// top image. Must supply x/y coordinates of each image for proper
// placement. If unknown the value "0" will place the start of the
// images in the top/left corner of the canvas.
//
// NOTE: Keep the "new_image" different than "base" and "cover" 
// image names to avoid over-writing them.
///////////////////////////////////////////////////////////
// What info does this function need?
/////////////////
//
// $data['base_image']          = (Required) (The bottom image)
// $data['cover_image']         = (Required) (The overlay image)
// $data['new_image']           = (Required) The name of the new image file 
// $data['base_x']              = (Required) The X coordinate or the base image
// $data['base_y']              = (Required) The Y coordinate or the base image
// $data['cover_x']             = (Required) The X coordinate or the cover image
// $data['cover_y']             = (Required) The Y coordinate or the cover image
// $data['quality']             = (Required) The quality of the final image (0 to 100)
// $data['background']          = (Required) The background color of final image (6 digit hex)
// $data['transparency']        = (Required) The level of transparency the cover image should have
//
/////////////////
// Example data
/////////////////
//
// $data['base_image'] =        "Winter.jpg";
// $data['cover_image'] =       "Sunset.jpg";
// $data['new_image'] =         "Winter_Sunset.jpg";
// $data['base_x'] =            100;
// $data['base_y'] =            100;
// $data['cover_x'] =           200;
// $data['cover_y'] =           200;
// $data['quality'] =           50;
// $data['background'] =        'ffffff';
// $data['transparency'] =      60;
//
//////////////////////////////////////////////////
function merge_jpegs($data) {
    //If this build of GD doesn't have jpeg support
    if(!function_exists('imagecreatefromjpeg')) { return false; }
    
    //Open a copy of the first image that we will work with
    $base_image = @imagecreatefromjpeg($data['base_image']);
    
    //Open a copy of the second image that we will work with
    $cover_image = @imagecreatefromjpeg($data['cover_image']);
    
    //If we couldn't open either image
    if( (!$base_image) || (!$cover_image) ) {return false; }
    
    //Get the images width and height
    $base_info = getimagesize($data['base_image']);
    $cover_info = getimagesize($data['cover_image']);
    
    //The width/height of the image plus x/y coordinate = total width/height
    $cover_width = $cover_info[0] + $data['cover_x'];
    $base_width = $base_info[0] + $data['base_x'];
    
    //If the base width (+ x) is larger than the cover width (+ x)
    $width = ($base_width > $cover_width) ? $base_width : $cover_width;
    
    //The width of the image + the x coordinate = the total width of the cover image
    $cover_height = $cover_info[1] + $data['cover_y'];
    $base_height = $base_info[1] + $data['base_y'];
    
    //If the base height (+ y) is larger than the cover height (+ y)
    $height = ($base_height > $cover_height) ? $base_height : $cover_height;
    
    // To make sure that the final image isn't smaller than the 
    // ("x/y coordinates" + "width/height") we will make a canvas 
    // that is equal to the biggest image + offset. Then we will
    // Paste the two images onto it.
    
    //Create a blank image the size of the largest object + x/y offset coordinates
    $canvas = imagecreatetruecolor($width, $height);
    //Convert 6 digit hex to rgb
    $rgb = hex2rgb($data['background']);
    //Allocate the background color
    $background = imagecolorallocate($canvas, $rgb['r'], $rgb['g'], $rgb['b']);
    //Make the background the requested color
    imagefill($canvas, 0, 0, $background);
    
    //Paste the base image over the canvas at the x/y coordinates
    imagecopy($canvas, $base_image, $data['base_x'], $data['base_y'], 0, 0, $base_info[0], $base_info[1]);
    
    //Paste the cover image over the canvas at the x/y coordinates
    imagecopymerge($canvas, $cover_image, $data['cover_x'], $data['cover_y'], 0, 0, $cover_info[0], $cover_info[1], $data['transparency']);
    
    //Save the final image to the hard drive
    if(!imagejpeg($canvas, $data['new_image'], $data['quality'])) { 
        trigger_error('Could not save new jpg image'. $data['new_image']);
        return false;
    }
    
    //Distroy both images (not the canvas though)
    imagedestroy($base_image);
    imagedestroy($cover_image);

    return true;
}



///////////////////////////////////////////////////////////
// A function to convert a 6 digit hex value to an RGB array
//////////////////////////////////////////////////
function hex2rgb($hex) {
    return array(   'r' => hexdec(substr($hex, 0, 2)),
                    'g' => hexdec(substr($hex, 2, 2)),
                    'b' => hexdec(substr($hex, 4, 2))
                );
}

    
?>