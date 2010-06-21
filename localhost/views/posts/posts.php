<div id="content" class="grid_8 suffix_1">

	<h4><?php print $count; ?> Posts found in the database</h4>
	
	<?php 
	if(!empty($rows)) {
	foreach($rows as $row) { 
	?>
	<h2><?php print $row->title; ?></h2>
	<small>Posted by <?php print $row->author; ?></small>
	<p><?php print $row->text; ?></p>
	
	<?php } } ?>
	
	<h2><?php print count($this->db->queries); ?> Database Queries Run</h2>
	<?php $this->db->print_queries(); ?>
</div>

<div id="sidebar" class="grid_3">
	
	
	<h3>About</h3>
	<p>On controller load, we connect to the database and 
	check it for the posts table. If not found we create it.
	Then we insert several posts and display them.</p>
</div>