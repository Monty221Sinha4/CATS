
<?php
$start = "";
// Our 2 global arrays containing our links to be crawled.
$already_crawled = array();
$crawling = array();
function get_details($url) {
	.
	$options = array('http'=>array('method'=>"GET", 'headers'=>"User-Agent: howBot/0.1\n"));
	
	$context = stream_context_create($options);
	// Create a new instance of PHP's DOMDocument class.
	$doc = new DOMDocument();


	@$doc->loadHTML(@file_get_contents($url, false, $context));
	// Create an array of all of the title tags.
	$title = $doc->getElementsByTagName("title");

	$title = $title->item(0)->nodeValue;
	
	$description = "";
	$keywords = "";

	$metas = $doc->getElementsByTagName("meta");
	for ($i = 0; $i < $metas->length; $i++) {
		$meta = $metas->item($i);
	
		if (strtolower($meta->getAttribute("name")) == "description")
			$description = $meta->getAttribute("content");
		if (strtolower($meta->getAttribute("name")) == "keywords")
			$keywords = $meta->getAttribute("content");
	}
	// Return our JSON string containing the title, description, keywords and URL.
	return '{ "Title": "'.str_replace("\n", "", $title).'", "Description": "'.str_replace("\n", "", $description).'", "Keywords": "'.str_replace("\n", "", $keywords).'", "URL": "'.$url.'"},';
}
function follow_links($url) {

	global $already_crawled;
	global $crawling;
	// The array that we pass to stream_context_create() to modify our User Agent.
	$options = array('http'=>array('method'=>"GET", 'headers'=>"User-Agent: howBot/0.1\n"));
	// Create the stream context.
	$context = stream_context_create($options);
	
	$doc = new DOMDocument();
	// Use file_get_contents() to download the page, pass the output of file_get_contents()
	// to PHP's DOMDocument class.
	@$doc->loadHTML(@file_get_contents($url, false, $context));
	// Create an array of all of the links we find on the page.
	$linklist = $doc->getElementsByTagName("a");
	// Loop through all of the links we find.
	foreach ($linklist as $link) {
		$l =  $link->getAttribute("href");
	
		if (substr($l, 0, 1) == "/" && substr($l, 0, 2) != "//") {
			$l = parse_url($url)["scheme"]."://".parse_url($url)["host"].$l;
		} else if (substr($l, 0, 2) == "//") {
			$l = parse_url($url)["scheme"].":".$l;
		} else if (substr($l, 0, 2) == "./") {
			$l = parse_url($url)["scheme"]."://".parse_url($url)["host"].dirname(parse_url($url)["path"]).substr($l, 1);
		} else if (substr($l, 0, 1) == "#") {
			$l = parse_url($url)["scheme"]."://".parse_url($url)["host"].parse_url($url)["path"].$l;
		} else if (substr($l, 0, 3) == "../") {
			$l = parse_url($url)["scheme"]."://".parse_url($url)["host"]."/".$l;
		} else if (substr($l, 0, 11) == "javascript:") {
			continue;
		} else if (substr($l, 0, 5) != "https" && substr($l, 0, 4) != "http") {
			$l = parse_url($url)["scheme"]."://".parse_url($url)["host"]."/".$l;
		}
		
		if (!in_array($l, $already_crawled)) {
				$already_crawled[] = $l;
				$crawling[] = $l;
				
				echo get_details($l)."\n";
		}
	}
	// Remove an item from the array after we have crawled it.
	// This prevents infinitely crawling the same page.
	array_shift($crawling);
	// Follow each link in the crawling array.
	foreach ($crawling as $site) {
		follow_links($site);
	}
}
// Begin the crawling process by crawling the starting link first.
follow_links($start);
© 2017 GitHub, Inc.
Terms
Privacy
Security
Status
Help
Contact GitHub
API
Training
Shop
Blog
About