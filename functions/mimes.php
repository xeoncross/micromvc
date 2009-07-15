<?php
/**
 * Mime handling
 *
 * This file contains serveral functions that help with sending or retriving
 * files and their appropriate headers.
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
 * Returns mime type for a given extension. If the extension is not found
 * then FALSE will be returned. If an extention is not given then the full
 * array of all mime types will be returned.
 *
 * @param	string $type
 * @return	mixed
 */
function mime_type($type = NULL) {

	$mimes = array(
		'acx'     => 'application/internet-property-stream',
    	'ai'      => 'application/postscript',
    	'aif'     => 'audio/x-aiff',
	    'aifc'    => 'audio/x-aiff',
	    'aiff'    => 'audio/x-aiff',
	    'asc'     => 'text/plain',
        'asf'     => 'video/x-ms-asf',
        'asr'     => 'video/x-ms-asf',
        'asx'     => 'video/x-ms-asf',
	    'atom'    => 'application/atom+xml',
	    'au'      => 'audio/basic',
	    'avi'     => 'video/x-msvideo',
        'axs'     => 'application/olescript',
        'bas'     => 'text/plain',
	    'bcpio'   => 'application/x-bcpio',
	    'bin'     => 'application/octet-stream',
	    'bmp'     => 'image/bmp',
        'c'       => 'text/plain',
        'cat'     => 'application/vnd.ms-pkiseccat',
	    'cdf'     => 'application/x-netcdf',
        'cer'     => 'application/x-x509-ca-cert',
	    'cgm'     => 'image/cgm',
	    'class'   => 'application/octet-stream',
	    'clp'     => 'application/x-msclip',
        'cmx'     => 'image/x-cmx',
        'cod'     => 'image/cis-cod',
	    'cpio'    => 'application/x-cpio',
        'crd'     => 'application/x-mscardfile',
        'crl'     => 'application/pkix-crl',
        'crt'     => 'application/x-x509-ca-cert',
	    'cpt'     => 'application/mac-compactpro',
	    'csh'     => 'application/x-csh',
	    'css'     => 'text/css',
	    'dcr'     => 'application/x-director',
        'der'     => 'application/x-x509-ca-cert',
	    'dir'     => 'application/x-director',
	    'djv'     => 'image/vnd.djvu',
	    'djvu'    => 'image/vnd.djvu',
        'dll'     => 'application/x-msdownload',
	    'dmg'     => 'application/octet-stream',
	    'dms'     => 'application/octet-stream',
	    'doc'     => 'application/msword',
        'dot'     => 'application/msword',
	    'dtd'     => 'application/xml-dtd',
	    'dvi'     => 'application/x-dvi',
	    'dxr'     => 'application/x-director',
	    'eps'     => 'application/postscript',
	    'etx'     => 'text/x-setext',
	    'exe'     => 'application/octet-stream',
	    'ez'      => 'application/andrew-inset',
        'fif'     => 'application/fractals',
        'flac'    => 'audio/flac',
        'flr'     => 'x-world/x-vrml',
	    'gif'     => 'image/gif',
	    'gram'    => 'application/srgs',
	    'grxml'   => 'application/srgs+xml',
	    'gtar'    => 'application/x-gtar',
        'gz'      => 'application/x-gzip',
        'h'       => 'text/plain',
	    'hdf'     => 'application/x-hdf',
        'hlp'     => 'application/winhlp',
        'hqx'     => 'application/mac-binhex40',
        'hta'     => 'application/hta',
        'htc'     => 'text/x-component',
	    'htm'     => 'text/html',
	    'html'    => 'text/html',
        'htt'     => 'text/webviewhtml',
	    'ice'     => 'x-conference/x-cooltalk',
	    'ico'     => 'image/x-icon',
	    'ics'     => 'text/calendar',
	    'ief'     => 'image/ief',
	    'ifb'     => 'text/calendar',
	    'iges'    => 'model/iges',
	    'igs'     => 'model/iges',
        'iii'     => 'application/x-iphone',
        'ins'     => 'application/x-internet-signup',
        'isp'     => 'application/x-internet-signup',
        'jfif'    => 'image/pipeg',
	    'jpe'     => 'image/jpeg',
	    'jpeg'    => 'image/jpeg',
	    'jpg'     => 'image/jpeg',
	    'js'      => 'application/x-javascript',
	    'kar'     => 'audio/midi',
	    'latex'   => 'application/x-latex',
	    'lha'     => 'application/octet-stream',
        'lsf'     => 'video/x-la-asf',
        'lsx'     => 'video/x-la-asf',
	    'lzh'     => 'application/octet-stream',
        'm13'     => 'application/x-msmediaview',
        'm14'     => 'application/x-msmediaview',
	    'm3u'     => 'audio/x-mpegurl',
	    'man'     => 'application/x-troff-man',
        'mdb'     => 'application/x-msaccess',
	    'mathml'  => 'application/mathml+xml',
	    'me'      => 'application/x-troff-me',
	    'mesh'    => 'model/mesh',
        'mht'     => 'message/rfc822',
        'mhtml'   => 'message/rfc822',
	    'mid'     => 'audio/midi',
	    'midi'    => 'audio/midi',
	    'mif'     => 'application/vnd.mif',
	    'mov'     => 'video/quicktime',
	    'movie'   => 'video/x-sgi-movie',
	    'mp2'     => 'audio/mpeg',
	    'mp3'     => 'audio/mpeg',
        'mpa'     => 'video/mpeg',
	    'mpe'     => 'video/mpeg',
	    'mpeg'    => 'video/mpeg',
	    'mpg'     => 'video/mpeg',
	    'mpga'    => 'audio/mpeg',
        'mpp'     => 'application/vnd.ms-project',
        'mpv2'    => 'video/mpeg',
	    'ms'      => 'application/x-troff-ms',
        'mvb'     => 'application/x-msmediaview',
        'nws'     => 'message/rfc822',
	    'msh'     => 'model/mesh',
	    'mxu'     => 'video/vnd.mpegurl',
	    'nc'      => 'application/x-netcdf',
	    'oda'     => 'application/oda',
        'oga'     => 'audio/ogg',
        'ogg'     => 'audio/ogg',
        'ogv'     => 'video/ogg',
        'ogx'     => 'application/ogg',
        'p10'     => 'application/pkcs10',
        'p12'     => 'application/x-pkcs12',
        'p7b'     => 'application/x-pkcs7-certificates',
        'p7c'     => 'application/x-pkcs7-mime',
        'p7m'     => 'application/x-pkcs7-mime',
        'p7r'     => 'application/x-pkcs7-certreqresp',
        'p7s'     => 'application/x-pkcs7-signature',
	    'pbm'     => 'image/x-portable-bitmap',
	    'pdb'     => 'chemical/x-pdb',
	    'pdf'     => 'application/pdf',
	    'pgm'     => 'image/x-portable-graymap',
        'pko'     => 'application/ynd.ms-pkipko',
        'pma'     => 'application/x-perfmon',
        'pmc'     => 'application/x-perfmon',
        'pml'     => 'application/x-perfmon',
        'pmr'     => 'application/x-perfmon',
        'pmw'     => 'application/x-perfmon',
	    'pgn'     => 'application/x-chess-pgn',
	    'png'     => 'image/png',
	    'pnm'     => 'image/x-portable-anymap',
        'pot'     => 'application/vnd.ms-powerpoint',
	    'ppm'     => 'image/x-portable-pixmap',
        'pps'     => 'application/vnd.ms-powerpoint',
	    'ppt'     => 'application/vnd.ms-powerpoint',
        'prf'     => 'application/pics-rules',
	    'ps'      => 'application/postscript',
        'pub'     => 'application/x-mspublisher',
	    'qt'      => 'video/quicktime',
	    'ra'      => 'audio/x-pn-realaudio',
	    'ram'     => 'audio/x-pn-realaudio',
	    'ras'     => 'image/x-cmu-raster',
	    'rdf'     => 'application/rdf+xml',
	    'rgb'     => 'image/x-rgb',
	    'rm'      => 'application/vnd.rn-realmedia',
	    'roff'    => 'application/x-troff',
	    'rss'     => 'application/rss+xml',
	    'rtf'     => 'text/rtf',
	    'rtx'     => 'text/richtext',
        'scd'     => 'application/x-msschedule',
        'sct'     => 'text/scriptlet',
        'setpay'  => 'application/set-payment-initiation',
        'setreg'  => 'application/set-registration-initiation',
	    'sgm'     => 'text/sgml',
	    'sgml'    => 'text/sgml',
	    'sh'      => 'application/x-sh',
	    'shar'    => 'application/x-shar',
	    'silo'    => 'model/mesh',
	    'sit'     => 'application/x-stuffit',
	    'skd'     => 'application/x-koan',
	    'skm'     => 'application/x-koan',
	    'skp'     => 'application/x-koan',
	    'skt'     => 'application/x-koan',
	    'smi'     => 'application/smil',
	    'smil'    => 'application/smil',
	    'snd'     => 'audio/basic',
	    'so'      => 'application/octet-stream',
	    'spl'     => 'application/x-futuresplash',
	    'src'     => 'application/x-wais-source',
        'sst'     => 'application/vnd.ms-pkicertstore',
        'stl'     => 'application/vnd.ms-pkistl',
        'stm'     => 'text/html',
        'svg'     => "image/svg+xml",
	    'sv4cpio' => 'application/x-sv4cpio',
	    'sv4crc'  => 'application/x-sv4crc',
	    'svg'     => 'image/svg+xml',
	    'svgz'    => 'image/svg+xml',
	    'swf'     => 'application/x-shockwave-flash',
	    't'       => 'application/x-troff',
	    'tar'     => 'application/x-tar',
	    'tcl'     => 'application/x-tcl',
	    'tex'     => 'application/x-tex',
	    'texi'    => 'application/x-texinfo',
	    'texinfo' => 'application/x-texinfo',
        'tgz'     => 'application/x-compressed',
	    'tif'     => 'image/tiff',
	    'tiff'    => 'image/tiff',
	    'tr'      => 'application/x-troff',
	    'tsv'     => 'text/tab-separated-values',
	    'txt'     => 'text/plain',
        'uls'     => 'text/iuls',
	    'ustar'   => 'application/x-ustar',
	    'vcd'     => 'application/x-cdlink',
	    'vrml'    => 'model/vrml',
	    'vxml'    => 'application/voicexml+xml',
	    'wav'     => 'audio/x-wav',
	    'wbmp'    => 'image/vnd.wap.wbmp',
	    'wbxml'   => 'application/vnd.wap.wbxml',
        'wcm'     => 'application/vnd.ms-works',
        'wdb'     => 'application/vnd.ms-works',
	    'wml'     => 'text/vnd.wap.wml',
	    'wmlc'    => 'application/vnd.wap.wmlc',
	    'wmls'    => 'text/vnd.wap.wmlscript',
	    'wmlsc'   => 'application/vnd.wap.wmlscriptc',
	    'wrl'     => 'model/vrml',
	    'xbm'     => 'image/x-xbitmap',
	    'xht'     => 'application/xhtml+xml',
	    'xhtml'   => 'application/xhtml+xml',
        'xla'     => 'application/vnd.ms-excel',
        'xlc'     => 'application/vnd.ms-excel',
	    'xls'     => 'application/vnd.ms-excel',
        'xlw'     => 'application/vnd.ms-excel',
	    'xml'     => 'application/xml',
        'xof'     => 'x-world/x-vrml',
	    'xpm'     => 'image/x-xpixmap',
	    'xsl'     => 'application/xml',
	    'xslt'    => 'application/xslt+xml',
	    'xul'     => 'application/vnd.mozilla.xul+xml',
	    'xwd'     => 'image/x-xwindowdump',
	    'xyz'     => 'chemical/x-xyz',
        'z'       => 'application/x-compress',
	    'zip'     => 'application/zip'
	);

	//If no type was given - return all
	if($type == NULL) {
		return $mimes;
	}

	//If type was found - return it
	if(isset($mimes[$type])) {
		return $mimes[$type];
	}
}


/**
 * Prompts user for direct download of HTTP attachment.
 * @param	string	$content
 * @param	string	$ext
 * @return	void
 */
function download_document($content = '', $ext = ''){

	//Try to get the mime type
	if( ! $mime = mime_type($ext)) {
		$mime = 'application/octet-stream';
	}

	//Send header data
	header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
	header('Content-Disposition: attachment; filename='.time(). $ext);
	header("Content-Length: " . mb_strlen($content));
	header("Content-Type: $mime");

	//Send file
	exit($content);
}
