<?php

//You can show all errors together...
print $this->validation->display_errors();
?>



<form method="post">
<?php
$fields = array(
	'name' => 'Name',
	'age' => 'Age',
	'min' => 'Min of 5 Chars',
	'max' => 'Max of 5 Chars',
	'exact' => 'Exactly 5 Chars',
	'email' => 'Email',
	'empty' => 'Optional Field (can be empty)'
);

foreach($fields as $name => $title) {
?>

	<?php
	//Or you can show errors separately (Look for an error for this field)
	$error = $this->validation->error($name);
	?>
	<b><?php print $title; ?></b><br />
	<input type="text" name="<?php print $name; ?>" value="<?php print $_POST[$name]; ?>" />
	<?php if($error) { print '<i style="color: red">'. $error. '</i>'; } ?>
	<br />

<?php } ?>

<br />

<b>Type your comment:</b>
<textarea name="text" style="display: block; width: 100%;"><?php print $_POST['text']; ?></textarea>

<input type="submit" value="submit" />
</form>