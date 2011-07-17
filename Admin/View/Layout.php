<!doctype html>
<html lang="en">
<head>
	<meta charset="utf-8"/>
	<title>Administration Area - <?php print URL::domain(); ?></title>
	<meta name="viewport" content="initial-scale=1.0, width=device-width, maximum-scale=1.0" />

	<link rel="stylesheet" media="all" href="<?php print site_url('/Admin/CSS/base.css'); ?>"/>
	<link rel="stylesheet" media="all" href="<?php print site_url('/Admin/CSS/admin.css'); ?>"/>
	<link rel="stylesheet" media="all" href="<?php print site_url('/Admin/CSS/style.css'); ?>"/>

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
<div id="sidebar">
	<h2>Admin Area</h2>
	<?php

	// Recursively build a menu tree
	$buildMenu = function($menus, $level = 1) use (&$buildMenu)
	{
		print "\n". str_repeat("\t", $level). "<ul>";
		foreach($menus as $title => $menu)
		{
			// Class is the name of the element
			$class = mb_strtolower(sanitize_filename($title));

			if($level === 1) $class .= ' root';
			if(isset($menu['child'])) $class .= ' parent';

			// Is this an array of sub-menus?
			$link = is_array($menu) ? $menu['link'] : $menu;

			print "\n". str_repeat("\t", $level + 1). "<li class=\"$class\">";
			print '<a href="' . site_url($link) . '"><span>' . $title . '</span></a>';
			if(is_array($menu))
			{
				$buildMenu($menu['child'], $level + 1);
				print str_repeat("\t", $level + 1);
			}
			print '</li>';
		}
		print "\n" . str_repeat("\t", $level) . "</ul>\n";

	};

	// Build it
	$buildMenu($menu);
	?>
</div>

<div id="main">

	<div id="header">
		<ul class="horizontal_menu">
			<li><a href="<?php print site_url(); ?>">Back to Site</a></li>
		</ul>
	</div>

	<div id="content">

		<?php print message();?>
		<?php print $content; ?>

		<?php if(isset($pagination)) print '<div class="box">'. $pagination. '</div>';?>

	</div>

	<div id="footer">

		<ul class="horizontal_menu">
			<li class="right">Page rendered in <?php print round((microtime(true) - START_TIME), 4); ?> seconds
			taking <?php print round((memory_get_usage() - START_MEMORY_USAGE) / 1024, 2); ?> KB
			</li>
			<li>Powered By <a href="http://micromvc.com">MicroMVC</a></li>
		</ul>

	</div>

</div>

</div>
<?php if( ! empty($footer)) print $footer; //JS snippets and such ?>
</body>
</html>
