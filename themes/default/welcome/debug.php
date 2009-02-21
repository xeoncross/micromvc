<p>Page rendered in <?php print round((microtime(true) - START_TIME), 5); ?> seconds
taking <?php print round((memory_get_usage() - START_MEMORY_USAGE) / 1024, 2); ?> KB 
(<?php print (memory_get_usage() - START_MEMORY_USAGE); ?> Bytes).</p>

<b>Files Included:</b>
<ul>
<li><?php print implode("</li>\n<li>", get_included_files()); ?>
</li>
</ul>

<b>Constants Defined:</b>
<ul>
<?php 
$constants = get_defined_constants(true);
foreach($constants['user'] as $name => $value) {
	print '<li>'. $name. ' = '. $value. '</li>';
}
?>
</ul>