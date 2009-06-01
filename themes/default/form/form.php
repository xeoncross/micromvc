

<?php print $this->validation->errors(); ?>

<?php //print_pre($_POST); ?>



<form method="post">
<?php 
foreach(array('name', 'age', 'min', 'max', 'exact', 'email', 'empty') as $name) {
?>
	<b><?php print $name; ?></b><br />
	<input type="text" name="<?php print $name; ?>" value="<?php print $_POST[$name]; ?>" /> <br />

<?php } ?>


Type your comment:
<textarea name="text" style="display: block; width: 100%;"><?php print $_POST['text']; ?></textarea>

<input type="submit" value="submit" />
</form>