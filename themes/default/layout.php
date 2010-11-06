<!doctype html>
<html lang="en">
<head>
	<meta charset="utf-8"/>
	<title>MicroMVC PHP Framework</title>
	<meta name="viewport" content="initial-scale=1.0, width=device-width, maximum-scale=1.0" />
	
	<link rel="stylesheet" media="all" href="<?php print theme_url(); ?>css/my.css"/>
	
	<!--[if lt IE 9]>
	<script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
	<![endif]-->

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

	<header>
		<h1><?php print substr(DOMAIN,7); ?></h1>
		<nav>
			<ul>
				<li><a href="/">Welcome</a></li>
				<li><a href="/welcome/form">Form</a></li>
				<li><a href="/welcome/upload">Upload</a></li>
			</ul>
		</nav>
	</header>
	
	<div id="main">
		<?php print message();?>
	
		<?php if( ! empty($sidebar)) { ?>
			
			<div id="content">
				<?php print $content; ?>
			</div>
			
			<div id="sidebar">
				<?php print $sidebar; ?>
			</div>
			
		<?php } else { // Else they want to do the content layout themselves... ?>
		
			<div id="page">
				<?php print $content; ?>
			</div>
			
		<?php } ?>
		
		<?php if(isset($pagination)) print $pagination;?>
		
		<?php if(isset($debug)) print '<div id="debug">'. $debug. '</div>';?>
		
	</div>
	
	<footer>
		<div>Powered by <a href="http://micromvc.com">MicroMVC PHP Framework</a></div>
	
		<p>Page rendered in <?php print round((microtime(true) - START_TIME), 4); ?> seconds
		taking <?php print round((memory_get_usage() - START_MEMORY_USAGE) / 1024, 2); ?> KB
		(<?php print (memory_get_usage() - START_MEMORY_USAGE); ?> Bytes)</p>
	</footer>
	
</div>
<?php
if( ! empty($footer))
{
	print $footer;
}
?>
</body>
</html>