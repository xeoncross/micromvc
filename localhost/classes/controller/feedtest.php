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
class Controller_FeedTest extends Controller
{
	/**
	 * Creates an RSS or ATOM feed
	 * @param string $type set to rss or atom
	 */
	public function create($type = 'rss')
	{
		
		$type = $type === 'rss' ? 'rss' : 'atom';
		
		$feed = new Feed();
		$feed->title		= $type." Feed Title";
		$feed->link			= site_url();
		$feed->description	= "Recent articles on your website.";
		$feed->published	= time();
		$feed->copyright	= 'Copyright '. DOMAIN;
		
		$description = '<b>This is <i>so cool!</i></b>. <p>Yesterday <a href="#">google</a> did it!</p>';
		
	 	for($x=0;$x<10;$x++)
	 	{
			$item = new Feed_Item();
			$item->id			= 'http://'. DOMAIN. '/'. rand(100, 10000);
			$item->title		= String::random_charaters(19);
			$item->link			= site_url();
			$item->published	= time();
			$item->description	= $description;
			$item->author		= array('name' => String::random_charaters(5), 'email' => 'user@'. DOMAIN);
			
			$feed->add($item);
		}
		
		$this->layout = FALSE;
		$this->content_type = 'xml';
		$this->views['content'] = $feed->$type();
		
	}
	
	
	
	/**
	 * Parses the Google RSS feed and displays it as RSS or ATOM
	 * @param string $type set to rss or atom
	 */
	public function reprocess($type = 'rss')
	{
		$type = $type === 'rss' ? 'rss' : 'atom';
		
		// Create a new feed object
		$feed = new Feed();
		
		// Fetch a feed (google's)
		$xml = file_get_contents('http://feeds.feedburner.com/blogspot/MKuf');
		
		// Parse the ATOM feed
		$xml = $feed->parse($xml);
		
		// If this feed was valid, well-formed, and had entries
		if($xml AND ! empty($xml['entry']))
		{
			$xml = $xml['entry'];
		}
		
		// Create a new feed from the feed items
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
		$this->views['content'] = $feed->$type();
		
	}
}