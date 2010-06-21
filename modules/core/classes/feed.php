<?php defined('SYSTEM_PATH') or die('No direct access');
/**
 * Feed
 *
 * Creates RSS 2.0 and ATOM 1.0 XML feeds from standardized input. Can also 
 * parse RSS 2.0 and ATOM 1.0 strings into PHP arrays.
 *
 * @package		MicroMVC
 * @author		David Pennington
 * @copyright	(c) 2010 MicroMVC Framework
 * @license		http://micromvc.com/license
 ********************************** 80 Columns *********************************
 */
class Feed
{
	// RSS 2.0 Feed
	const RSS = 'RSS';
	
	// ATOM 1.0 Feed
	const ATOM = 'ATOM';
	
	public $type 			= NULL;
	public $link			= NULL;
	public $title			= NULL;
	public $description		= NULL;
	//public $language		= "en-us";
	public $published		= NULL;
	public $copyright		= NULL;
	public $generator		= 'MicroMVC Feed v1.0';
	public $image			= array();
	
	// Feed Items
	public $items			= array();
	
	
	/**
	 * Create a new feed object and setup some defaults
	 * 
	 * @param string $type the Feed::(type) of the feed
	 */
	public function __construct($type = self::ATOM)
	{
		// Default feed type is ATOM
		$this->type = $type;
		
		// Set feed date to now
		$this->published = time();
		
		// Set the link to this site
		$this->link = site_url(). '/';
	}
	
	
	/**
	 * Add an RSS item to the feed
	 * 
	 * @param object $item the Feed_Item object to add
	 */
	public function add($item)
	{
		$this->items[] = $item;
	}

	
	/**
	 * Render the complete RSS feed
	 * 
	 * @return string
	 */
	public function rss()
	{
		// Start the XML header
		$xml = '<?xml version="1.0" encoding="utf-8"?>' . "\n";
		
		// Add the RSS Header
		$xml .= '<rss version="2.0" xmlns:content="http://purl.org/rss/1.0/modules/content/"'
			. ' xmlns:wfw="http://wellformedweb.org/CommentAPI/">'. "\n";
		
		$xml .= "<channel>\n"
		. "\t<title>$this->title</title>\n"
		. "\t<link>$this->link</link>\n"
		. "\t<description>$this->description</description>\n"
		. "\t<pubDate>". date(DATE_RSS, $this->published). "</pubDate>\n";
		
		// Set the published date
		if($this->copyright)
		{
			$xml .= "\t<copyright>$this->copyright</copyright>\n";
		}
		
		// Set the feed generator
		if($this->generator)
		{
			$xml .= "\t<generator>$this->generator</generator>\n";
		}
		
		// If a feed image is set
		if($this->image)
		{
			$xml .= "\t<image>\n"
				. '<url>'. $this->image. "</url>\n"
				. '<title>'. $this->title. "</title>\n"
				. '<link>'. $this->link. "</link>\n"
				. "</image>\n";
		}
		
		// Process Feed Items
		foreach($this->items as $item)
		{
			
			// Create the feed item
			$xml .= "\t<item>\n"
			. "\t\t<guid>". h($item->id). "</guid>\n"
			. "\t\t<title>". h($item->title). "</title>\n"
			. "\t\t<link>". h($item->link). "</link>\n"
			. "\t\t<pubDate>". date(DATE_RSS, $item->published) . "</pubDate>\n"
			. "\t\t<description><![CDATA[". $item->description. "]]></description>\n";
			
			// If a source to credit is given
			if($item->source)
			{
				$xml .= "\t\t<source url=\"". $item->source['url']. '">'. $item->source['title']. "</source>\n";
			}
			
			// If an attachment is given
			if($item->enclosure)
			{
				$xml .= "\t\t<enclosure url=\"".$item->enclosure['url'].'" length="'.$item->enclosure['length']
				.'" type="'.$item->enclosure['mime'].'" />'. "\n";
			}
			
			// If an author is given
			if($item->author)
			{
				$xml .= "\t\t<author>";
				
				// If a URI is given
				if(isset($item->author['email']))
				{
					$xml .= '('. $item->author['email']. ') ';
				}
				
				$xml .= $item->author['name']. "</author>\n";
			}
			
			// Close the item
			$xml .= "\t</item>\n";
			
		}
		
		// Close and return the RSS channel
		return $xml. "</channel>\n</rss>";
	}
	
	
	/**
	 * Render the complete ATOM feed
	 * 
	 * @return string
	 */
	public function atom()
	{
		// Start the XML header
		$xml = '<?xml version="1.0" encoding="utf-8"?>' . "\n";
		
		// Add the ATOM Header
		$xml = '<feed xmlns="http://www.w3.org/2005/Atom">'. "\n";
		
		$xml .= '<id>' . $this->link . "</id>\n"
		. '<title type="text">' . $this->title . "</title>\n"
		. '<link href="' . $this->link . "\" />\n"
		. '<subtitle type="html">'. h($this->description). "</subtitle>\n"
		. '<updated>' . date(DATE_ATOM, $this->published) . "</updated>\n";
		
		// Set the published date
		if($this->copyright)
		{
			$xml .= '<rights type="html">' . h($this->copyright) . "</rights>\n";
		}
		
		// Set the feed generator
		if($this->generator)
		{
			$xml .= '<generator>' . $this->generator . "</generator>\n";
		}
		
		// If a feed image is set
		if($this->image)
		{
			$xml .= '<logo>'. $this->image. "</logo>\n";
		}
		
		// Process Feed Items
		foreach($this->items as $item)
		{
			// Create the feed item
			$xml .= "<entry>\n"
			. '<id>'. h($item->id). "</id>\n"
			. '<title type="html">'. h($item->title). "</title>\n"
			. '<link href="'. h($item->link). "\" />\n"
			. '<updated>' . date(DATE_ATOM, $item->published) . "</updated>\n"
			. '<content type="html">'. h($item->description). "</content>\n";
			
			// If a source-to-credit is given
			if($item->source)
			{
				$xml .= '<link rel="via" href="'. h($item->source['url']). ' title="'. h($item->source['title']). "\">\n";
			}
			
			// If an attachment is given
			if($item->enclosure)
			{
				$xml .= '<link rel="enclosure" type="'.$item->enclosure['mime'].'" length="'
				.$item->enclosure['length'].'" href="'. $item->enclosure['url']. "\"/>\n";
			}
			
			// If an author is given
			if($item->author)
			{
				$xml .= "<author>\n<name>". h($item->author['name']). "</name>\n";
				
				// If an email is given
				if(isset($item->author['email']))
				{
					$xml .= '<email>'. h($item->author['email']). "</email>\n";
				}
				
				// If a URI is given
				if(isset($item->author['uri']))
				{
					$xml .= '<uri>'. h($item->author['uri']). "</uri>\n";
				}
				
				$xml .= "</author>\n";
			}
			
			// Close the entry
			$xml .= "</entry>\n";
		}
		
		// Close the ATOM feed
		return $xml. "</feed>\n";
	}
	

	/**
	 * Parse a string of RSS/ATOM XML into an array of standardized entries.
	 * 
	 * @param string $xml the string of XML
	 * @return array|boolean
	 */
	public function parse($xml, $standardize = TRUE)
	{
		// Convert to XML object
		$xml = new SimpleXMLElement($xml);
		
		// If this is an ATOM feed
		if($xml->entry)
		{
			$type = 'atom';
			$xml = $xml->entry;
		}
		// Else if this is a RSS feed
		elseif($xml->channel AND $xml->channel->item)
		{
			$type = 'rss';
			$xml = $xml->channel->item;
		}
		else // Invalid FEED!
		{
			return;
		}
		
		// Convert to an array
		$xml = $this->xml_to_array($xml);
		
		// If we should leave the XML "as-is"
		if( ! $standardize)
		{
			return $xml;
		}
		
		// Standardize the ATOM feed entries
		if($type == 'atom')
		{
			return $this->standardize_atom($xml['entry']);
		}
		
		// Standardize the RSS feed items
		return $this->standardize_rss($xml['item']);
	}
	
	
	/**
	 * Parse an array of ATOM feed entries into to the standardized format*
	 * 
	 * @param array $xml the array of parsed XML
	 * @return array
	 */
	public function standardize_atom($xml)
	{
		foreach($xml as $id => &$data)
		{
			// If missing the *required* ATOM elements
			if(empty($data['id']) OR empty($data['title']) OR empty($data['updated']))
			{
				unset($xml[$id]);
				continue;
			}
			
			// Standarize date
			$data['date'] = $data['updated'];
			
			// Get the main link to the HTML version of the feed item
			if( ! empty($data['link']))
			{
				// Create a copy of this data in case the user wants it
				$data['links'] = $data['link'];
				
				// Search for the webpage link
				foreach($data['link'] as $link)
				{
					if( ! empty($link['href']))
					{
						if(empty($link['rel']) OR $link['rel'] === 'alternate')
						{
							$data['link'] = $link['href'];
							break;
						}
					}
				}
				
				// Link not found!
				if(is_array($data['link']))
				{
					$data['link'] = NULL;
				}
			}
			else // We must have a link!
			{
				$data['link'] = NULL;
			}
			
			// Save the Feed entry text
			if( ! empty($data['content']))
			{
				$data['description'] = $data['content'];
			}
			elseif( ! empty($data['summary']))
			{
				$data['description'] = $data['summary'];
			}
			else
			{
				$data['description'] = '';
			}
			
		}
		
		return $xml;
	}


	/**
	 * Parse an array of RSS feed items into to the standardized format*
	 * 
	 * @param array $xml the array of parsed XML
	 * @return array
	 */
	public function standardize_rss($xml)
	{
		foreach($xml as $id => &$data)
		{
			// If missing the *required* RSS elements
			if(empty($data['title']) AND empty($data['description']))
			{
				print dump('bad data', $data);
				unset($xml[$id]);
				continue;
			}
			
			// Standarize the date
			$data['date'] = (empty($data['pubDate']) ? NULL : $data['pubDate']);
			
			// Must have a link
			$data['link'] = (empty($data['link']) ? NULL : $data['link']);
			
			// Set the entry ID
			$data['id'] = (empty($data['guid']) ? $data['link'] : $data['guid']);
			
			// Create a description if not set
			if(empty($data['description']))
			{
				$data['description'] = NULL;
			}
			
		}
		return $xml;
	}
	
	
	/**
	 * Convert an XML RSS/ATOM feed into an array
	 * 
	 * @param object $xml the SimpleXMLElement object
	 * @return array
	 */
	public function xml_to_array($xml)
	{
		$result = array();
		foreach($xml as $id => $element)
		{
			// If this element has MANY children
			if(count($element->children()))
			{
				$result[$id][] = $this->xml_to_array($element->children());
			}
			
			// If an element has attributes which contain the values
			elseif($element->attributes() AND ! (string) $element)
			{
				$result[$id][] = current((array) $element->attributes());
			}
			else
			{
				$result[$id] = (string) $element;
			}
		}
		return $result;
	}
	
}

class Feed_Item
{
	// Requried item tags
	public $id			= NULL;
	public $title		= NULL;
	public $link		= NULL;
	public $description = NULL;
	public $published	= NULL;
	
	// Optional tags
	public $author		= array();
	public $source		= array();
	public $enclosure	= array();

}