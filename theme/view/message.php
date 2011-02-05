<!doctype html>
<html lang="en">
<head>
	<meta charset="utf-8"/>
	<title><?php if(!empty($title)) print $title; ?></title>
	<style type="text/css">
		body {
			padding: 0;
			margin: 0;
			background: #eee;
			font-size: 14px;
			line-height: 1.4em;
		}
		* {font-family: Arial,sans-serif;}
		#message {
			background: #f8f8f8;
			width: 400px;
			margin: 4em auto 0;
			padding: 1em 2em;
			-webkit-border-radius: 8px;
			-moz-border-radius: 8px;
			border-radius: 8px;
			border: 2px solid #fff;
			-moz-box-shadow: 0px 0px 5px #bbb;
			-webkit-box-shadow: 0px 0px 5px #bbb;
			box-shadow: 0px 0px 5px #bbb;
		}
	</style>
	<?php
	//Print all CSS files
	if( ! empty($css)) foreach($css as $file) print '<link rel="stylesheet" media="all" href="'. site_url($file). '" />';
	
	//Print all JS files
	if( ! empty($javascript)) foreach($javascript as $file) print '<script type="text/javascript" src="'.site_url($file). '"></script>';
	
	//Print any other header data
	if( ! empty($head_data)) print $head_data;
	?>
</head>
<body>
<div id="container">

	<div id="message">
		<?php print message();?>
		<?php print $content; ?>
	</div>

</div>
<?php if( ! empty($footer)) print $footer; //JS snippets and such ?>
</body>
</html>
