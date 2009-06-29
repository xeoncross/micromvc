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