<div id="twitter">

<h1>Twitter Public Timeline</h1>

<?php
foreach($object as $tweet) {
	
	//Get the users name
	$name = $tweet->user->screen_name ? $tweet->user->screen_name : $tweet->user->name;
	
	//Get the tweet text
	$text = $tweet->text;
	
	print '<div class="tweet">'. $text;
	print ' - <a href="http://twitter.com/'. $name. '">'. $name. '</a>';
	print '</div>'. "\n\n";
}
?>


<?php
//To see all the data...
//print_pre($object);
?>
</div>