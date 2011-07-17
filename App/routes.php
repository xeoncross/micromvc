<?php
/**
 * URL Routing
 *
 * URLs are very important to the future usability of your site. Take
 * time to think about your structure in a way that is meaningful. Place
 * your most common page routes at the top for better performace.
 *
 * - Routes are matched from left-to-right.
 * - Regex can also be used to define routes if enclosed in "/.../"
 * - Each regex catch pattern (...) will be viewed as a parameter.
 * - The remaning (unmached) URL path will be passed as parameters.
 *
 ** Simple Example **
 * URL Path:	/forum/topic/view/45/Hello-World
 * Route:		"forum/topic/view" => 'Forum\Controller\Forum\View'
 * Result:		Forum\Controller\Forum\View->action('45', 'Hello-World');
 *
 ** Regex Example **
 * URL Path:	/John_Doe4/recent/comments/3
 * Route:		"/^(\w+)/recent/comments/' => 'Comments\Controller\Recent'
 * Result:		Comments\Controller\Recent->action($username = 'John_Doe4', $page = 3)
 */
$config = array(
	''					=> 'App\Controller\Index',
	'404'				=> 'App\Controller\Page404',

	// Example Module
	'example/school'	=> 'Example\Controller\School',
	'example/form'		=> 'Example\Controller\Form',
	'example/upload'	=> 'Example\Controller\Upload',

	// Forum Module
	'/^forum$/'				=> 'Forum\Controller\Forum\Index',
	'forum/create'			=> 'Forum\Controller\Forum\Create',
	'forum/update'			=> 'Forum\Controller\Forum\Update',
	'forum/admin'			=> 'Forum\Controller\Forum\Admin',
	'forum/process'			=> 'Forum\Controller\Forum\Process',
	'forum/topic/create'	=> 'Forum\Controller\Topic\Create',
	'forum/topic/update'	=> 'Forum\Controller\Topic\Update',
	'forum/topic/process'	=> 'Forum\Controller\Topic\Process',
	'forum/topic/admin'		=> 'Forum\Controller\Topic\Admin',
	'forum/topic'			=> 'Forum\Controller\Topic\View',
	'forum/reply/create'	=> 'Forum\Controller\Reply\Create',
	'forum/reply/update'	=> 'Forum\Controller\Reply\Update',
	'forum/reply/process'	=> 'Forum\Controller\Reply\Process',
	'forum/reply/admin'		=> 'Forum\Controller\Reply\Admin',
	'forum'					=> 'Forum\Controller\Forum\View',		// Forum/4/General-Topics/

	// User, ACL, Role, & Resource
	'user/login'				=> 'User\Controller\User\Login',
	'user/deny'					=> 'User\Controller\User\Deny',
	'user/admin'				=> 'User\Controller\User\Admin',
	'user/process'				=> 'User\Controller\User\Process',
	'user/acl/admin'			=> 'User\Controller\ACL\Admin',
	'user/role/admin'			=> 'User\Controller\Role\Admin',
	'user/role/process'			=> 'User\Controller\Role\Process',
	'user/role/create'			=> 'User\Controller\Role\Create',
	'user/role/update'			=> 'User\Controller\Role\Update',
	'user/resource/admin'		=> 'User\Controller\Resource\Admin',
	'user/resource/create'		=> 'User\Controller\Resource\Create',
	'user/resource/update'		=> 'User\Controller\Resource\Update',
	'user/resource/process'		=> 'User\Controller\Resource\Process',

	// Unit Tests
	'unittest'	=> 'Unittest\Controller\Index',

	// Events
	'/^events$/' => 'Event\Controller\Index',
	'events/ajax' => 'Event\Controller\AJAX',
	'events/details' => 'Event\Controller\Detail',
	'events/recent' => 'Event\Controller\Recent',
	'events/create' => 'Event\Controller\Create',
	'events/update' => 'Event\Controller\Update',
	'events/admin' => 'Event\Controller\Admin',
);
