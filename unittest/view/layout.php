<!DOCTYPE html>
<head>
	<meta charset="UTF-8">
	<title>MicroMVC Unit Test</title> 
	<style type="text/css">
		body{font: 1em/1.3em Arial, san-serif; background: #f3f3f3;}
		#container { max-width: 800px; margin: 0 auto; padding: 1em; background: #fff;}
		table{border-collapse: collapse;border-spacing: 0;width: 100%;}
		th, td {text-align: right;padding: .5em;}
		td:first-child, th:first-child {text-align: left;}
		tr{border-bottom: 1px solid #fff;}
		th, tfoot td {background: #BEE1F8; }
		tr.ok td {background: #CAF9D3;}
		tr.fail td{background: #F8BEBE;}
		tfoot td:first-child { text-align: right; }
		h2{margin: 0; padding: 0; font-weight: normal;}
	</style>
</head>
<body>
<div id="container">

	<h1>Results For <?php print substr(get_class($this), 11); ?></h1>
	
	<?php //print dump($tests); ?>
	
	<table>
	<thead>
		<tr><th>Test Name</th><th>Time</th><th>Memory</th><th>Result</th></tr>
	</thead>
	<tbody>
	<?php
	$counter = 0;
	foreach($tests as $method => $result)
	{
		$time = round($result[1]*1000, 2);
		$memory = round($result[2]/1024, 3);
		
		if($result[0])
		{
			$result = 'ok';
		}
		else
		{
			$counter++;
			$result = 'fail';
		}
		
		
		print "<tr class=\"$result\"><td>$method</td><td>$time ms</td><td>$memory kb</td><td>$result</td></tr>";
	}
	
	?>
	</tbody>
	
	<tfoot><tr><td colspan="4"><b><?php print $counter.' of '.count($tests); ?> tests failed</b></td></tr></tfoot>
	
	</table>
</div>
</body>
</html>