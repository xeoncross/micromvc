<?php
/**
 * Admin (Database Management) Class
 *
 * This class allows easy scaffolding of database records to help with admin
 * tasks such as approving, disabling, reviewing, searching, and deleting.
 *
 * @package		MicroMVC
 * @author		David Pennington
 * @copyright	(c) 2011 MicroMVC Framework
 * @license		http://micromvc.com/license
 ********************************** 80 Columns *********************************
 */
abstract class Admin_Controller extends Controller
{
	public $template = 'layout';

	/**
	 * Administration Area
	 *
	 * @param array $config settings
	 */
	protected function admin($config = array(), $page)
	{
		// Setup defaults
		$config = $config + config(NULL, 'admin');
		$config['admin_url'] = site_url($this->route);

		$model = $config['model'];
		$pp = $config['per_page'];

		// URI Structure: /forum/admin/2/topic/desc/title/YQ~~
		$page = int($page, 1);
		$field = get('field', '', TRUE);
		$sort = get('sort') == 'asc' ? 'asc' : 'desc';
		$column = get('column', '', TRUE);
		$term = get('term', '', TRUE);
		$where = array();
		$order_by = array();

		// Valid?
		if($column AND in_array($column, $config['columns']))
		{
			// Get the correct database column identifier
			$i = ORM::$db->i;

			$where = array($i.$column.$i.' LIKE '.ORM::$db->quote("%$term%"));
		}

		// Valid?
		if($field AND in_array($field, $config['columns']))
		{
			$order_by = array($field => $sort);
		}

		// Load rows
		$result = $model::fetch($where,$pp,(($page*$pp)-$pp),$order_by);
		$count = $model::count($where);

		//If not found
		if( ! $result)
		{
			$this->content = new View('no_rows','admin');
			$this->content->set($config);
			return;
		}

		//Make the results into an object
		$result = (object) $result;

		//Allow the controller to process these rows
		$this->pre_admin_display($result);

		//Allow hooks to change the $row data before showing it
		event('administration_' . $model::$table, $result);

		//We must reset the array pointer
		reset($result);

		//For each post - place it in a template
		$data = array(
			'rows'		=> $result,
			'columns'	=> $config['columns'],
			'config'	=> $config,
			'page'		=> $page,
			'field'		=> $field,
			'sort'		=> $sort,
			'column'	=> $column,
			'term'		=> $term,
		);

		$query_string = '?'. http_build_query(array(
			'field' => $field,
			'sort' => $sort,
			'column' => $column,
			'term' => $term
		));

		// Pagination URI
		$url = $this->route. '/[[page]]'. $query_string;

		// Create the pagination
		$this->pagination = new Pagination($count, $url, $page, $pp);

		//Load the form view (and return it)
		$view = new view('admin','admin');
		$view->set($data);

		// Create new session token
		Session::token();

		$this->content = $view;
	}


	/**
	 * Process the given row ID's
	 *
	 * @param array $ids the ids to alter
	 * @param string $action the action name
	 */
	protected function process($config = array(), $return_to = NULL)
	{
		// Setup defaults
		$config = $config + config(NULL, 'admin');

		// If there is no page to return to - then go back to admin
		if( ! $return_to)
		{
			$return_to = $config['admin_url'];
		}

		// Validate
		if( ! Session::token(post('token')) OR ! post('ids') OR ! is_array(post('ids')) OR ! post('action') OR empty($config['actions'][post('action')]))
		{
			redirect(base64_url_decode($return_to));
			exit();
		}

		$process = $config['actions'][post('action')];
		$model = $config['model'];

		// Run each actions
		foreach(post('ids') as $id)
		{
			$object = new $model($id);

			// Remove?
			if(isset($process['delete']))
			{
				$object->delete();
				continue;
			}

			// We are approving/banning/activating/promoting/etc...
			if(isset($process['columns']))
			{
				foreach($process['columns'] as $column => $value)
				{
					$object->$column = $value;
				}
				$object->save();
			}
		}

		// And back we go!
		redirect(base64_url_decode($return_to));
		exit();
	}


	/**
	 * This method can be extended by the controller to allowing additional
	 * processing of the rows before display.
	 *
	 * @param object $results and object containing the result rows
	 */
	protected function pre_admin_display($results) {}


	/**
	 * Save user session before rendering the final layout template
	 */
	public function render()
	{
		Session::save();

		headers_sent() OR header('Content-Type: text/html; charset = utf-8');

		$layout = new View($this->template, 'admin');
		$layout->set((array) $this);
		$layout->menu = $this->load_menu();
		print $layout;

		$layout = NULL;

		if(config('debug_mode'))
		{
			print new View('debug', 'system');
		}
	}

	protected function load_menu()
	{
		// Fetch all the module directories
		$modules = dir::contents(SP, FALSE, 'dir');

		$menu = array();
		// Build the admin menu from all modules
		foreach($modules as $module)
		{
			// If this module has a config file
			if(is_file($module->getPathname(). '/config'. EXT))
			{
				// Load the config
				$config = config(NULL, $module->getBasename());

				// If this module has any admin menus to add
				if(isset($config['admin_menu']))
				{
					$menu = array_merge($menu, $config['admin_menu']);
				}
			}
		}

		return $menu;
	}

}
