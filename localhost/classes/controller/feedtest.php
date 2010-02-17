<?php defined('SYSTEM_PATH') or die('No direct access');
/**
 * FeedTest
 *
 * Shows an example of using the feed class.
 *
 * Note, this controller is only for demonstration purposes! Remove this file
 * before you put your site online!
 * 
 * @package		MicroMVC
 * @author		David Pennington
 * @copyright	(c) 2010 MicroMVC Framework
 * @license		http://micromvc.com/license
 ********************************** 80 Columns *********************************
 */
class Controller_FeedText extends Controller
{
	public function feed($type = 'rss')
	{
		
		$feed = new Feed();
		$feed->title		= "RSS Feed Title";
		$feed->link			= "http://website.com";
		$feed->description	= "Recent articles on your website.";
		$feed->published	= time();
		$feed->copyright	= 'Copyright my site';
		
		$description = '<b>This is <i>so cool!</i></b>. <p>yesterday <a href="#">google</a> did it!</p>';
		
	 	for($x=0;$x<10;$x++)
	 	{
			$item = new Feed_Item();
			$item->id			= 'http://'. DOMAIN. '/'. rand(100, 10000);
			$item->title		= random_charaters(19);
			$item->link			= 'http://helloworld.com';
			$item->published	= time();
			$item->description	= $description;
			$item->author		= array('name' => random_charaters(5), 'email' => 'user@site.com');
			
			$feed->add($item);
		}
		
		$this->layout = FALSE;
		$this->content_type = 'xml';
		print $feed->$type();
		
	}
	
	
	
	
	public function index()
	{
		
		// Create a new feed object
		$feed = new Feed();
		
		// Fetch a feed 
		$xml = file_get_contents('C:\wamp\www\feeds\reader\wp.atom');
		
		// Parse the ATOM feed
		$xml = $feed->parse($xml);
		
		// If this feed was valid, well-formed, and had entries
		if($xml AND !empty($xml['entry']))
		{
			$xml = $xml['entry'];
		}
		
		//print dump($xml);
		//die();
		
		// Create a new RSS feed from the feed items
		foreach($xml as $entry)
		{
			$item = new Feed_Item();
			$item->id			= $entry['id'];
			$item->title		= $entry['title'];
			$item->link			= $entry['link'];
			$item->published	= strtotime($entry['date']);
			$item->description	= $entry['description'];
		
			// Add the entry
			$feed->add($item);
		}
		
		
		$this->layout = FALSE;
		$this->content_type = 'xml';
		$this->views['content'] = $feed->rss();
		
	}
}