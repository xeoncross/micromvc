<!doctype html>
<html lang="en">
<head>
	<meta charset="utf-8"/>
	<title>MicroMVC PHP Framework</title>
	<meta name="viewport" content="initial-scale=1.0, width=device-width, maximum-scale=1.0" />
	
	<link rel="stylesheet" media="all" href="/theme/view/css/base.css"/>
	<link rel="stylesheet" media="all" href="/theme/view/css/style.css"/>
	
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
		<div class="container">
			<div class="grid_5 first">
				<h1><?php print substr(DOMAIN,7); ?></h1>
			</div>
			
			<div class="grid_7">
			<nav>
				<ul>
					<li><a href="/">Welcome</a></li>
					<li><a href="/example/form">Form</a></li>
					<li><a href="/example/upload">Upload</a></li>
					<li><a href="/example/school">School</a></li>
				</ul>
			</nav>
			</div>
		</div>
	</header>
	
	<div id="main">
	
		<div class="container">
		<?php if( ! empty($sidebar)) { ?>
			
			<div class="grid_8 first">
				<div id="content">
					<?php print message();?>
					<?php print $content; ?>
					<?php if(isset($pagination)) print $pagination;?>
				</div>
			</div>
			
			<div class="grid_4">
				<div id="sidebar">
					<?php print $sidebar; ?>
				</div>
			</div>
			
		<?php } else { // Else they want to do the content layout themselves... ?>
		
			<div class="grid_12">
				<div id="page">
					<?php print message();?>
					<?php print $content; ?>
					<?php if(isset($pagination)) print $pagination;?>
				</div>
			</div>
			
		<?php } ?>
		</div>
		
	</div>
	
	<footer>
		<div class="container">
			<div class="grid_6 first">
				Powered by <a href="http://micromvc.com">MicroMVC PHP Framework</a>
			</div>
		
			<div class="grid_6 stats">Page rendered in <?php print round((microtime(true) - START_TIME), 4); ?> seconds
			taking <?php print round((memory_get_usage() - START_MEMORY_USAGE) / 1024, 2); ?> KB
			(<?php print (memory_get_usage() - START_MEMORY_USAGE); ?> Bytes)
			</div>
		</div>
	</footer>
	
</div>
<?php if( ! empty($footer)) print $footer; //JS snippets and such ?>
</body>
</html>