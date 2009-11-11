<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">

<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>MicroMVC PHP Framework</title>

<link rel="stylesheet" href="<?php print THEME_URL; ?>style.css" type="text/css" media="screen" />
<?php if(!empty($header_files)) { print $header_files; } ?>

</head>
<body>

<div id="container">
	<div id="menu">
		<ul>
			<?php
			//Get the URI of this page
			$uri = $this->routes->fetch();

			//Create a list of the menu links
			$links = array(
				'Welcome' => 'welcome/index',
				'Hooks' => 'hook_test',
				'URI' => 'hook_test/say',
				'Twitter' => 'twitter',
				'SQLite' => 'posts',
			);

			//For each link
			foreach($links as $name => $link) {
				//If this this link is the current one in the URI
				if(stripos($uri, $link) !== FALSE) {
					print '<li class="selected">';
				} else {
					print '<li>';
				}
				print '<a href="'. SITE_URL. $link. '">'. $name. '</a></li>';
			}
			?>
		</ul>
	</div>

	<div id="main">
		<div class="wrapper">
			<?php print $content; ?>
		</div>
	</div>

	<div id="footer">
		<div class="wrapper">
			<p>Page rendered in <?php print round((microtime(true) - START_TIME), 5); ?> seconds
			taking <?php print round((memory_get_usage() - START_MEMORY_USAGE) / 1024, 2); ?> KB
			(<?php print (memory_get_usage() - START_MEMORY_USAGE); ?> Bytes).</p>
		</div>
	</div>
</div>

</body>
</html>