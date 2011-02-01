<?php
// MicroMVC unit test controller
class Unittest_Controller_Index extends Controller
{
	public function action()
	{
		// Run each test
		foreach(get_class_methods($this) as $method)
		{
			// Skip the core methods
			if(in_array($method,array('show_404','render','action','__construct')))
			{
				continue;
			}
			
			$time = microtime(TRUE);
			$memory = memory_get_usage();
			
			// Run the test
			$result = $this->$method();
			
			// Record the result
			$this->tests[$method] = array($result, microtime(TRUE)-$time, memory_get_usage()-$memory);
		}
	}
	
	// Override the default layout to use our custom unittest template
	public function render()
	{
		headers_sent()||header('Content-Type: text/html; charset=utf-8');$l=new View('layout','unittest');$l->set((array)$this);print$l;$l=0;if(config('debug_mode'))print new View('debug','system');
	}
	
	
	/*
	 * Begin Unit tests
	 */
	
	
	public function registry()
	{
		// Test empty value
		if(registry('key') !== NULL)
			return;
		
		// Set a value
		if(registry('key', 'value') !== 'value')
			return;
		
		// Get a value
		if(registry('key') !== 'value')
			return;

		// Remove a value
		if(registry('key', NULL) !== NULL)
			return;
		
		// Get the new null value
		if(registry('key') !== NULL)
			return;
		
		return TRUE;
	}
	
	public function config()
	{
		return config('key', 'unittest') === 'value';
	}
	
	public function lang()
	{
		return lang('line', 'unittest') === 'ok';
	}
	
	public function url()
	{
		// Get current URL
		$url=server('PATH_INFO')?server('PATH_INFO'):server('REQUEST_URI');
		
		// Compare it to the result of URL
		return strpos('/'.url(), $url) === 0;
	}
	
	public function autoload()
	{
		// Check for non-loaded class
		if(class_exists('UnitTest_Example', FALSE))
			return;
		
		new UnitTest_Example;
		
		if( ! class_exists('UnitTest_Example', FALSE))
			return;
			
		return TRUE;
	}
	
	
	public function dump()
	{
		return dump('<?php=<h1>string</h1>')==="<pre>&lt;?php=&lt;h1&gt;string&lt;/h1&gt;</pre>\n";
	}
	
	public function post()
	{
		$_POST['key'] = 'value';
		return post('key') === 'value';
	}
	
	public function get()
	{
		$_GET['key'] = 'value';
		return get('key') === 'value';
	}
	
	public function server()
	{
		$_SERVER['key'] = 'value';
		return server('key') === 'value';
	}
	
	/**
	 * We must have a cookie set before we can test it.
	 * Therefore, issue a page reload after setting a sample cookie.
	 */
	public function cookie()
	{
		if( ! cookie::get('key'))
		{
			redirect('unittest/index');
			cookie::set('key', 'value');
			exit();
		}
		
		return cookie::get('key') === 'value';
	}
	
	public function token()
	{
		return token() !== token() AND mb_strlen(token()) === 32;
	}
	
	public function log_message()
	{
		// Path to log file
		$log = SP.config('log_path').date('/Y-m-d').'.log';
		
		// If the file already exists - increase size
		if(is_file($log))
		{
			$size = filesize($log);
			log_message('UnitTest');
			clearstatcache();
			return filesize($log)>$size;
		}
		
		// Else create log file
		log_message('UnitTest');
		
		return is_file($log);
	}
	
	
	public function int()
	{
		// Int
		if(int(5) !== 5)
			return;
		
		// Array
		if(int(array(), 5) !== 5)
			return;
		
		// Text String
		if(int('key', 5) !== 5)
			return;
		
		// Int String
		if(int('5', 4) !== 5)
			return;
			
		// Float
		if(int(5.5, 4) !== 5)
			return;
			
		// Object
		if(int(new StdClass, 5) !== 5)
			return;
		
		return TRUE;
	}
	
	public function str()
	{
		// Int
		if(str(5) !== '5')
			return;
		
		// Array
		if(str(array(), 'value') !== 'value')
			return;
		
		// Text String
		if(str('value', 5) !== 'value')
			return;
		
		// Empty String
		if(str('', 'value') !== '')
			return;
		
		// Float
		if(str(5.5, 'value') !== '5.5')
			return;
			
		// Object
		if(str(new StdClass, 'value') !== 'value')
			return;
		
		return TRUE;
	}
	
	/*
	public function _testing_db()
	{
		$config = config('database');
		
		$db = new DB($config['default']);
		
		$db->pdo = new UnitTest_Mock_PDO();
		
		return (bool) $db->fetch('SELECT * FROM "table" WHERE "id" = ?', array(5));
		
	}
	*/
	
	public function unittest()
	{
		return TRUE;
	}
	
}
