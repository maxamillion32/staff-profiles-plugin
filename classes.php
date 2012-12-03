<?php
/**
 * Classes to facilitate the retrieval of feeds from
 * the symplectic publications database
 * Author: Mark Wales (Small Hadron Collider)
 * Author: Peter Edwards <p.l.edwards@leeds.ac.uk>
 */
	
	abstract class TSD
	{
		private $id;
		private $array = array();
		private $cache;
		private $cacheDir;
		private $debug;
		private $logfile;
		
		private $sortBy;
		private $sortOrder;
		
		protected $url;
		protected $folder;
		protected $element;
		
		function __construct($id, $cache = 3600, $cacheDir = '', $debug = false)
		{
			$this->id = $id;
			$this->cache = $cache;
			$this->cacheDir = $cacheDir . 'cache/' . $this->folder . '/';
			$this->debug = $debug;
			$this->logfile = $cacheDir . 'cache/debug_log.txt';

			$this->loadXML();
			
			if ( !$this->array ){
				$this->array = array();
			}
		}
		
		public function loadXML()
		{
			$path = $this->cacheDir . $this->id . '.cache';
			
			if(file_exists($path))
			{
				/* check age of cache file against cache parameter */
				if(time() - filemtime($path) < $this->cache)
				{
					/* load items from cache */
					$this->loadXMLCache();
					$this->log("Loaded XML from cache for " . $this->id);
				}
				else
				{
					/* try downloading XML file */
					if ( !$this->downloadXML() )
					{
						/* if the download fails, load the cache file anyway */
						$this->loadXMLCache();
						$this->log("Loaded XML from cache for " . $this->id . " (XML download FAILED)");
					} else {
						if ($this->cache > 0)
						{
							$this->log("Downloaded XML for " . $this->id . " (stale cache file)");
						}
						else
						{
							$this->log("Downloaded XML for " . $this->id . " (caching disabled)");
						}
					}
				}
			}
			else
			{
				$this->downloadXML();
				$this->log("Downloaded XML for " . $this->id . " (no cache file)");
			}
		}

		private function loadXMLCache()
		{
			$path = $this->cacheDir . $this->id . '.cache';

			if ( false !== ($file = @fopen($path, 'r')) )
			{
				$input = fread($file, filesize($path));
				fclose($file);
				$this->array = unserialize($input);
			}
		}

		private function saveXMLCache()
		{
			/* Serialise and save the array into a cache */
			$output = serialize($this->array);
			if ( !is_dir($this->cacheDir) )
			{
				mkdir($this->cacheDir, 0777, true);
			}
			
			$path = $this->cacheDir . $this->id . '.cache';
			if ( false !== ($file = @fopen($path, 'w')) )
			{
				fwrite($file, $output);
				fclose($file);
			}
		}

		public function clearXMLCache()
		{
			if ( is_dir($this->cacheDir) ) {
				if ($dh = opendir($this->cacheDir)) {
					while (($file = readdir($dh)) !== false) {
						if ( is_file($this->cacheDir . $file) ) {
							@unlink($this->cacheDir . $file);
						}
					}
					closedir($dh);
				}
			}
		}
	
		private function downloadXML()
		{	
			$curl = curl_init();
			
			/* Set the root URL - subclasses add to this */
			curl_setopt($curl, CURLOPT_URL, 'http://tsdservices.leeds.ac.uk/'.$this->url.$this->id);

			/* Don't output headers */
			curl_setopt($curl, CURLOPT_HEADER, 0);

			/* Output as string rather than printing out */
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

			/* Limit connection to five seconds */
			curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 5);

			/* use proxy for local connections */
			/*if ( isset($_SERVER["SERVER_ADDR"]) && $_SERVER["SERVER_ADDR"] == "127.0.0.1" )
			{
				curl_setopt($curl, CURLOPT_PROXY, "http://www-cache.leeds.ac.uk:3128");
			}*/

			/* Get the xml */
			$xml = curl_exec($curl);

			/* If output is returned, parse XML*/
			if ($xml !== false)
			{
				/* Slighly crude parsing meachanism, but does the trick for the Sympletic format.
				 * Find the first key element (e.g. OrgStaff), remove everything before it, split on that element
				 */
				
				/* Find first occurence of the key element */
				$first = strpos($xml, '<'.$this->element);

				/* Get rid of the gunk before first key element */
				$xml = substr($xml, $first);

				/* Split the XML on the key element */
				$xmlArray = preg_split('/<\/*?'.$this->element.'[^>]*>/', $xml);

				/* Remove the last item in the array - all the gunk at the bottom */
				array_pop($xmlArray);

				/* Add each element as an item */
				foreach($xmlArray as $item)
				{
					/* Check for content */
					if ( trim($item) )
					{
						/* Item does the rest of the work, i.e. getting the inner elements sorted */
						$this->array[] = new Item($item);
					}
				}
				
				if (count($this->array)) {

					/* save to cache if we get results from symplectic */
					$this->saveXMLCache();

				} else {

					/* empty array returned by symplectic - check the cache */
					$path = $this->cacheDir . $this->id . '.cache';
					$tmp_array = array();
					
					if (file_exists($path))
					{
						/* cache file found - get contents */
						if ( false !== ($file = @fopen($path, 'r')) )
						{
							$input = fread($file, filesize($path));
							fclose($file);
							$tmp_array = unserialize($input);
						}

						if (!count($tmp_array)) {
							/* cache is empty as well - save a new copy to refresh cache */
							$this->saveXMLCache();
							$this->log("Cache file empty for " . $this->id . " (empty response from symplectic)");
						} else {
							/* cache is non-empty - return false to simulate failed download and force reading from cache instead */
							$this->log("Cache file non-empty for " . $this->id . " (empty response from symplectic) - using cache instead");
							return false;
						}
					} else {
						/* save empty array in cache */
						$this->saveXMLCache();
					}

				}
				return true;
			}
			else {
				return false;
			}
		}

		private function log($msg)
		{
			if ( $this->debug )
			{
				if ( false !== ($fh = @fopen($this->logfile, "a")) )
				{
					fwrite($fh, date("r") . ": " . $msg . "\n");
					fclose($fh);
				}
			}
		}
		
		public function getPossibleAttrValues($attr)
		{
			$arrayOfValues = array();
			
			foreach($this->array as $item)
			{
				$value = $item->getAttr($attr);
				
				if(!in_array($value, $arrayOfValues))
				{
					$arrayOfValues[] = $value;
				}
			}
			
			return $arrayOfValues;
		}
		
		public function getPossibleAttrNames()
		{
			$arrayOfNames = array();
			
			foreach($this->array as $item)
			{
				$arrayOfNames = array_merge($arrayOfNames, $item->getAttrNames());
			}
			
			return array_unique(array_values($arrayOfNames));
		}

		public function filterByAttr($attr, $value)
		{
			$arrayOfResults = array();
			
			foreach($this->array as $item)
			{
				if((strtolower($item->getAttr($attr)) === strtolower($value)) || (($item->getAttr($attr) === false || $item->getAttr($attr) == "" || $item->getAttr($attr) == "null") && $value == "null"))
				{
					$arrayOfResults[] = $item;
				}
			}
			
			return $arrayOfResults;
		}
		
		public function filterByAttrs($attrs)
		{
			if(is_array($attrs))
			{
				$arrayOfResults = array();
			
				foreach($this->array as $item)
				{
					$count = 0;
					
					foreach($attrs as $attr => $value)
					{
						if (is_array($value))
						{
							array_walk($value, create_function('&$val', '$val = trim(strtolower($val));')); 
							if (in_array(strtolower($item->getAttr($attr)), $value) || (($item->getAttr($attr) === false || $item->getAttr($attr) == "" || $item->getAttr($attr) == "null") && in_array("null", $value)))
							{
								$count++;
							}
						}
						elseif((strtolower($item->getAttr($attr)) === strtolower($value)) || (($item->getAttr($attr) === false || $item->getAttr($attr) == "" || $item->getAttr($attr) == "null") && $value == "null"))
						{
							$count++;
						}
					}
					
					if($count === count($attrs))
					{
						$arrayOfResults[] = $item;
					}
				}
				
				return $arrayOfResults;
			}
			else
			{
				die('<p>Error: Array required. Use filterByAttr for single values.</p>');
			}
		}
		
		public function sortByAttr($attr, $sortOrder = 'asc')
		{
			$this->sortBy = $attr;
			
			switch($sortOrder)
			{
				case 'desc':$this->sortOrder = -1; break;
				case 'asc':
				default:$this->sortOrder = 1;
			}
			
			uasort($this->array, array($this, '_sort'));
		}
		
		protected function _sort($a, $b)
		{
			$attr = $this->sortBy;
			$sortOrder = $this->sortOrder;
			
			if($a->getAttr($attr) == $b->getAttr($attr))
			{
				return 0;
			}

			if ($attr === "publicationdate") {
				$a_att = $this->get_timestamp($a->getAttr($attr));
				$b_att = $this->get_timestamp($b->getAttr($attr));
			} else {
				$a_att = $a->getAttr($attr);
				$b_att = $b->getAttr($attr);
			}

			return $sortOrder * (($a_att < $b_att) ? -1 : 1);
		}

		private function get_timestamp($dateStr)
		{
			/* attempt to make a timestamp from a date in d/m/y format */
			$date_parts = explode("/", $dateStr);
			if (count($date_parts) == 3) {
				$timestamp = @mktime(0, 0, 0, intval($date_parts[1]), intval($date_parts[0]), intval($date_parts[2]));
				if ($timestamp !== false && $timestamp !== -1) {
					return $timestamp;
				}
			}
			return $dateStr;
		}
		
		public function returnAmount($amount)
		{
			$arrayOfResults = array();
			
			for($i = 0; $i<$amount; $i++)
			{
				$arrayOfResults[] = array_pop($this->array);
			}
			
			return $arrayOfResults;
		}

	}
	
	class StaffLookup extends TSD
	{
		protected $url = 'MasterdataOrgStaff.asmx/GetOrganisationStaffData?sap_dept_no=';
		protected $folder = 'faculty';
		protected $element = 'OrgStaff';
	}
	
	class PublicationsLookup extends TSD
	{
		protected $url = 'SymplecticPublication.asmx/GetPubsForPerson?uniqueid=';
		protected $folder = 'staff';
		protected $element = 'Pubs';
	}
	
	class Item
	{
		private $attributes = array();
		
		function __construct($rawXML)
		{
			preg_match_all('/<(\w*?)>/i', $rawXML, $matches);
			
			$attributes = array();
			
			foreach($matches[1] as $a)
			{
				$temp = preg_split('/<(\/?)'.$a.'(.*?)>/i', $rawXML);
				$attributes[$a] = trim($temp[1]);
			}
			
			$this->attributes = $attributes;
		}
		
		public function display()
		{	
			foreach($this->attributes as $key => $value)
			{
				echo '<p><strong>'.$key.':</strong><br /> '.$value.'</p>';
			}
		}
		
		public function getAttr($attr)
		{
			if(isset($this->attributes[$attr]))
			{
				return $this->attributes[$attr];
			}
			else
			{
				return false;
			}
		}

		public function getAttrNames()
		{
			return array_keys($this->attributes);
		}
	}

?>