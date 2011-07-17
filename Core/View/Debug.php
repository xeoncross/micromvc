<div style="margin: 60px 0; padding:2em; background:#ECF5FA; color:#000; clear:both;">

<b>Memory Usage</b>
<pre>
<?php print number_format(memory_get_usage() - START_MEMORY_USAGE); ?> bytes
<?php print number_format(memory_get_usage()); ?> bytes (process)
<?php print number_format(memory_get_peak_usage(TRUE)); ?> bytes (process peak)
</pre>

<b>Execution Time</b>
<pre><?php print round((microtime(true) - START_TIME), 5); ?> seconds</pre>

<b>URL</b>
<?php print dump(\Core\URL::path()); ?>

<?php if(class_exists('\Core\DB', FALSE))
{
	foreach(\Core\DB::$queries as $type => $queries)
	{
		print '<b>'.$type.' ('. count($queries). ' queries)</b>';
		foreach($queries as $data)
		{
			print '<pre>'. highlight(wordwrap($data[2])."\n/* ".round(($data[0]*1000), 2).'ms - '. round($data[1]/1024,2).'kb'. ' */'). '</pre>';
		}
	}

	if(\Core\Error::$found)
	{
		print '<b>Last Query Run</b>';
		print '<pre>'. highlight(\Core\DB::$last_query). '</pre>';
	}

}


function highlight($string)
{
	/*return str_replace(array("&lt;?php", "?&gt;"),'',substr(substr(highlight_string('<?php '.$string.' ?>', TRUE),36),0,-20));*/
	return str_replace(array("&lt;?php", "?&gt;"),'',substr(highlight_string('<?php '.$string.' ?>', TRUE),36));
}
?>

<?php if(!empty($_SESSION)) { ?>
<b>Session Data</b>
<?php print dump($_SESSION); ?>
<?php } ?>

<?php $included_files = get_included_files(); ?>
<b><?php print count($included_files); ?> PHP Files Included:</b>
<pre>
<?php print implode("\n", $included_files); ?>
</pre>

<b>Server Info</b>
<?php print dump($_SERVER); ?>

</div>
