<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">

<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>MicroMVC PHP Framework</title>

<link rel="stylesheet" href="<?php print THEME_PATH; ?>style.css" type="text/css" media="screen" />
<?php if(!empty($header_files)) { print $header_files; } ?>

</head>
<body>

<div id="main">
	<ul id="menu">
		<li><a href="<?php print SITE_PATH; ?>welcome/">Welcome</a></li>
		<li><a href="<?php print SITE_PATH; ?>welcome/hooks/">Hooks</a></li>
		<li><a href="<?php print SITE_PATH; ?>welcome/say/">URI</a></li>
		<li><a href="<?php print SITE_PATH; ?>welcome/twitter/">Twitter</a></li>
		<li><a href="<?php print SITE_PATH; ?>posts/">SQLite</a></li>
	</ul>

	<?php print $content; ?>
</div>

</body>
</html>