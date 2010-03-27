<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">

<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>MicroMVC PHP Framework</title>

<link rel="stylesheet" type="text/css" media="all" href="<?php print VIEW_URL; ?>css/reset.css" />
<link rel="stylesheet" type="text/css" media="all" href="<?php print VIEW_URL; ?>css/text.css" />
<link rel="stylesheet" type="text/css" media="all" href="<?php print VIEW_URL; ?>css/grid.css" />
<link rel="stylesheet" type="text/css" media="all" href="<?php print VIEW_URL; ?>css/style.css" />


<?php
//Print all CSS files
if( ! empty($css_files))
{
	foreach($css_files as $file)
	{
		print css($file);
	}
}

//Print all JS files
if( ! empty($js_files))
{
	foreach($js_files as $file)
	{
		print js($file);
	}
}

//Print head data
if( ! empty($head_data))
{
	print $head_data;
}
?>

</head>
<body>


<div id="container">

	<div id="top">
		<div class="container">
			<div class="grid_4">
				<h1><?php print DOMAIN; ?></h1>
			</div>
	
			<div class="grid_7">
				<ul id="sub_nav">
					<?php
					// Get the URI of this page
					$uri = routes::fetch();
	
					// Header links
					$links = array(
						'Welcome' => 'welcome',
						'Form Test' => 'formtest',
						'Upload Test' => 'uploadtest'
					);
	
					foreach($links as $name => $link)
					{
						// If this this link is the current one in the URI
						print '<li'. ($uri === $link ? ' class="selected"' : ''). '>'
							. '<a href="'. site_url($link). '">'. $name. '</a></li>';
					}
					?>
				</ul>
			</div>
		</div>
	</div>
	
	<div id="middle">
		<div class="container">
		
			<?php if( ! empty($headline)) { print $headline; } ?>
			
			
			<?php if( ! empty($sidebar)) { ?>
			
				<div class="grid_8">
					<div id="content">
						<?php print $content; ?>
					</div>
				</div>
				
				<div class="grid_4">
					<div id="sidebar">
						<?php print $sidebar; ?>
					</div>
				</div>
			
			<?php } else { // Else they want to do the content layout themselves... ?>
			
				<?php print $content; ?>
				
			<?php } ?>
		</div>
	</div>
	
	<div id="bottom">
		<div class="container">
			<div class="grid_4">
				Powered by <a href="http://micromvc.com">MicroMVC PHP Framework</a>
			</div>
	
			<div class="grid_7 prefix_1">
				<p>Page rendered in <?php print round((microtime(true) - START_TIME), 5); ?> seconds
				taking <?php print round((memory_get_usage() - START_MEMORY_USAGE) / 1024, 2); ?> KB
				(<?php print (memory_get_usage() - START_MEMORY_USAGE); ?> Bytes).</p>
			</div>
		</div>
	</div>
	
</div>


</body>
</html>