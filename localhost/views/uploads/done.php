<h2>Success</h2>
<p>Your file was uploaded! <a href="<?php print SITE_URL;?>uploads/upload">Upload again?</a></p>
<?php
print_pre($this->upload->file_data);
print_pre($_FILES);
?>