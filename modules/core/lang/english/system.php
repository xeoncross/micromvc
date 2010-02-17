<?php defined('SYSTEM_PATH') or die('No direct access');
/**
 * System Language
 *
 * This is the english language file where you will put all your language strings.
 * To modify any existing system language strings just create a file named 
 * "system.php" in your site language directory with the changes.
 *
 * @package		MicroMVC
 * @author		David Pennington
 * @copyright	(c) 2010 MicroMVC Framework
 * @license		http://micromvc.com/license
 ********************************** 80 Columns *********************************
 */

/*
 * Uploads
 */

$lang[UPLOAD_ERR_INI_SIZE] = 'The uploaded file exceeds the maximum file size allowed on this server.';
$lang[UPLOAD_ERR_FORM_SIZE] = 'The uploaded file exceeds the maximum file size that was specified in the HTML form.';
$lang[UPLOAD_ERR_PARTIAL] = 'The file was only partially uploaded.';
$lang[UPLOAD_ERR_NO_FILE] = 'You did not select a file to upload.';
$lang[UPLOAD_ERR_NO_TMP_DIR] = 'Missing a temporary folder to upload too.';
$lang[UPLOAD_ERR_CANT_WRITE] = 'The file could not be written to disk.';
$lang[UPLOAD_ERR_EXTENSION] = 'THe file upload was stopped because of a bad file extension.';

$lang['upload_invalid_file'] = 'Invalid file given.';
//$lang['upload_invalid_destination'] = 'The destination directory given is invalid.';
$lang['upload_bad_destination'] = 'The destination directory does not exist or is not writable.';
$lang['upload_invalid_filetype'] = 'The file you are attempting to upload is not allowed.';
$lang['upload_invalid_filesize'] = 'The file you are attempting to upload is larger than the permitted size.';
$lang['upload_destination_error'] = 'A problem was encountered while attempting to move the uploaded file.';


/*
 * Form Validation
 */

$lang['validation_no_rules'] = 'The %s field does not contain a valid rule (%s)';
$lang['validation_rule_not_found'] = 'The %s form rule was not found.';
$lang['validation_set']= 'The %s field must be submitted.';
$lang['validation_required'] = 'The %s field is required and cannot be empty.';

$lang['validation_alpha'] = 'The %s field may only contain alphabetical characters.';
$lang['validation_alpha_numeric'] = 'The %s field may only contain alpha-numeric characters.';
$lang['validation_numeric'] = 'The %s field must contain only numbers.';
$lang['validation_min_length'] = 'The %s field must be at least %s characters in length.';
$lang['validation_max_length'] = 'The %s field can not exceed %s characters in length.';
$lang['validation_exact_length'] = 'The %s field must be exactly %s characters in length.';
$lang['validation_valid_email'] = 'The %s field must contain a valid email address.';
$lang['validation_valid_base64'] = 'The %s field must contian valid Base 64 characters.';
$lang['validation_matches'] = 'The %s and %s fields do not match.';
$lang['validation_invalid_token'] = 'Your session token was invalid. Please try again.';


/*
 * GD Image Lib
 */
$lang['gd_no_ext'] = 'Bad or missing file extension.';
$lang['gd_bad_file'] = 'Bad filename given or file not found.';
$lang['gd_bad_image_file'] = 'Could not create image from file.';
$lang['gd_error'] = 'The GD library is not loaded.';
$lang['gd_save_error'] = 'Could not save the new %s image.';
$lang['gd_create_error'] = 'Could not create a new %s GD image.';


/*
 * Hooks
 */
$lang['hooks_no_function'] = 'Trying to call a hook with no class or function.';
$lang['hooks_no_file'] = 'Cannot run the %s hook function without a file';
$lang['base_controller_not_loaded'] = 'The controller is not loaded yet.';
$lang['hooks_bad_object'] = 'The "%s" class was not found or does not contain the requested method.';



/*
 * HTML class
 */
$lang['html_pagination_previous'] = 'Previous';
$lang['html_pagination_first'] = '&lt;';
$lang['html_pagination_last'] = '&gt;';
$lang['html_pagination_next'] = 'Next';




/*
 * HTTP status codes
 */
$lang[400] = 'The request contains bad syntax, an invalid URL, or cannot be fulfilled.';
$lang[401] = 'Sorry, you are not authorized to access this.';
$lang[403] = 'You are not allowed here.';
$lang[404] = 'We\'re sorry, it appears the page you are looking for is missing.';
$lang[500] = 'Sorry, it appears our server is having some trouble right now. Please try again in a minute.';


/*
 * Routes
 */
$lang['invalid_uri_characters'] = 'Sorry, there are invalid characters in the URL above this page.';


