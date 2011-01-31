<?php

// Send the correct header
headers_sent() OR header('HTTP/1.0 404 Page Not Found');

// Load the theme 404 page
$this->content = new View('404');
