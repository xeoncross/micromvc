<?php
// Sample homepage
class Example_Controller_Index extends Controller
{
	public function action()
	{
		// Load the theme sidebar since we don't need the full page
		$this->sidebar = new View('sidebar');
		
		// Load the welcome view
		$this->content = new View('index', 'example');
	}
}