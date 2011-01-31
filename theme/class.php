<?php

Class Theme_Class {

	public static function render($view)
	{
		// UTF-8 HTML
		headers_sent() OR header('Content-Type: text/html; charset=utf-8');
		
		// Load system layout
		$layout = new View('layout');
		$layout->set($view);
		print $layout;
		
		// Show debug info?
		if(config('debug_mode'))
		{
			print new View('debug', 'system');
		}
	}
}