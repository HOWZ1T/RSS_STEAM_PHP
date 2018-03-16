<?php
	class SteamPost
	{
		private $title;
		private $description;
		private $link;
		private $published_date;
		private $author;
		
		public function __construct($title, $description, $link, $published_date, $author)
		{
			$this->title = $title;
			$this->description = $description;
			$this->link = $link;
			$this->published_date = $published_date;
			$this->author = $author;
		}
		
		public function get($key)
		{
			switch(strtolower($key))
			{
				case 'title':
				case 'heading':
				case 'headline':
				case 'header':
				case 'head':
					return $this->title;
					
				case 'desc':
				case 'description':
				case 'body':
				case 'content':
					return $this->description;
					
				case 'link':
				case 'url':
				case 'href':
					return $this->link;
					
				case 'date':
				case 'published':
				case 'published date':
				case 'published_date':
				case 'date published':
					return $this->published_date;
					
				case 'author':
					return $this->author;
					
				default:
					error_log('POST->GET: unrecognized key!', 0);
					return null;
			}
		}
	}

	class SteamFeed
	{
		private $title;
		private $language;
		private $generator;
		private $image_url;
		private $link;
		private $posts;
		
		public function __construct($title, $language, $generator, $image_url, $link, $posts)
		{
			$this->title = $title;
			$this->language = $language;
			$this->generator = $generator;
			$this->image_url = $image_url;
			$this->link = $link;
			$this->posts = $posts;
		}
		
		public function get($key, $index=-1)
		{
			switch(strtolower($key))
			{
				case 'title':
				case 'heading':
				case 'headline':
				case 'header':
				case 'head':
				case 'feedtitle':
				case 'feed title':
					return $this->title;
					
				case 'language':
				case 'lang':
					return $this->language;
					
				case 'generator':
				case 'origin':
					return $this->generator;
					
				case 'image_url':
				case 'image':
				case 'thumbnail':
				case 'thumb':
				case 'artwork':
					return $this->image_url;
					
				case 'link':
				case 'url':
				case 'href':
					return $this->link;
					
				case 'post':
					if($index == -1)
					{
						error_log('FEED->GET: No post index provided!');
						return  null;
					}
					else
					{
						$len = count($this->posts);
						if($index < 0 || $index >= $len)
						{
							error_log('FEED->GET: Post index out of bounds!');
							return null;
						}
						else
						{
							return $this->posts[$index];
						}
					}
					
					error_log('FEED->GET: Unexpected error when attempting to get post!');
					return null;
					
				default:
					error_log('FEED->GET: unrecognized key!', 0);
					return null;
			}
		}
	}

	class RSS_MANAGER
	{
		private $feed_config;
		public function __construct($depth=0) //depth of this script in directory
		{
			$this->feed_config = $this->getFeedINI($depth);
		}
		
		public function loadSteamFeed($name) //loads the feed data into the feed objects
		{
			if(!isset($this->feed_config['steam'][$name]))
			{
				error_log('RSS_MANAGER->LOAD_STEAM_FEED: Given feed ('.$name.') does not exist!');
				return null;
			}
			
			$feed_xml_link = $this->feed_config['steam'][$name];
			$raw_xml = file_get_contents($feed_xml_link);
			$xml = new SimpleXmlElement($raw_xml);
			
			//parse xml
			$channel = $xml->channel;
		
			$rssTitle = (string)$channel->title;
			$lang = (string)$channel->language;
			$generator = (string)$channel->generator;
			$image_url = (string)$channel->image->url;
			$feedTitle = (string)$channel->image->title;
			$feedLink = (string)$channel->image->link;

			$obj_posts = array();
			$posts = $channel->item;
			for($i = 0; $i < count($posts); $i++)
			{
				$post = $posts[$i];
				$title = $post->title;
				$desc = $post->description;
				$link = $post->guid;
				$pub_date = $post->pubDate;
				$author = $post->author;
				
				$parsed_post = new SteamPost((string)$title, (string)$desc, (string)$link, (string)$pub_date, (string)$author);
				array_push($obj_posts, $parsed_post);
			}
			
			//create feed object
			$feed = new SteamFeed($feedTitle, $lang, $generator, $image_url, $feedLink, $obj_posts);
			return $feed;
		}
		
		private function getFeedINI($depth, $useSections=true)
		{
			$res = "";
			for($i = 0; $i < $depth; $i++)
			{
				$res .= '../';
			}
			$path = $res."data/rss_feeds.ini";
			$links = parse_ini_file($path, $useSections);
			return $links;
		}
	}

	/*TEST SCRIPT IN COMMENT
	$RSS = new RSS_MANAGER();
	$stationeers_feed = $RSS->loadSteamFeed('stationeers');
	print_r($stationeers_feed);*/
?>
