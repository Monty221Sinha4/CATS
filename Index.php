<?php
$start="http://localhost:81/Projects/SearchBox/Test.html";

$pdo= new PDO('mysql:host=127.0.0.1;dbname=thesearchBox','root','');

$crawl_ready=array();
$crawling = array();


function get_details($url){
 
	// The array that we pass to stream_context_create() to modify our User Agent.
	$options = array('http'=>array('method'=>"GET", 'headers'=>"User-Agent: howBot/0.1\n"));
	// Create the stream context.
	$context = stream_context_create($options);
	// Create a new instance of PHP's DOMDocument class.
	$doc = new DOMDocument();
	// Use file_get_contents() to download the page, pass the output of file_get_contents()
	// to PHP's DOMDocument class.
	@$doc->loadHTML(@file_get_contents($url, false, $context));
	// Create an array of all of the title tags.
	$title = $doc->getElementsByTagName("title");
	// There should only be one <title> on each page, so our array should have only 1 element.
	$title = $title->item(0)->nodeValue;
	// Give $description and $keywords no value initially. We do this to prevent errors.
	$description = "";
	$keywords = "";
	// Create an array of all of the pages <meta> tags. There will probably be lots of these.
	$metas = $doc->getElementsByTagName("meta");
	// Loop through all of the <meta> tags we find.
	for ($i = 0; $i < $metas->length; $i++) {
		$meta = $metas->item($i);
		
		if (strtolower($meta->getAttribute("name")) == "description")
			$description = $meta->getAttribute("content");
		if (strtolower($meta->getAttribute("name")) == "keywords")
			$keywords = $meta->getAttribute("content");
	}
	// Return our JSON string containing the title, description, keywords and URL.
	return '{ "Title": "'.str_replace("\n", "", $title).'", "Description": "'.str_replace("\n", "", $description).'", "Keywords": "'.str_replace("\n", "", $keywords).'", "URL": "'.$url.'"}';


}

/**
 * @param $url
 */
function  follow_Links($url){
    $doc= new DOMDocument();
    @$doc->LoadHTML(@file_get_contents($url,false,$context));

   $LinkList=$doc->getElementsByTagName("a");
   
   global $crawl_ready;
   global $craawling;
   global $pdo;
   $options=array("http"=>array('method'=>"GET",'header'=>"User-Agent:Bot/0.1\n"));
   $context=stream_context_create($options);

    foreach ($LinkList as $link){
        $l= $link->getAttribute("href");
        if (substr($l,0,1)=="/"&& substr($l,0,2)!="//"){
            $l=parse_url($url)["scheme"]."://".parse_url()["host"].$l;
        }else if(substr($l,0,1)=="//"){
			$l=parse_url($url)["scheme"]."://".parse_url($url)["host"].dirname(parse_url($url)["path"]).substr($l,1);
		}else if(substr($l,0,1)=="#"){
			$l=parse_url($url)["scheme"]."://".parse_url($url)["host"].dirname(parse_url($url)["path"]).$l;
		}else if(substr($l,0,3)=="../"){
				$l=parse_url($url)["scheme"]."://".parse_url($url)["host"]."/".$l;
		}else if(substr($l,0,11)=="javascript:"){
			continue;
		}else if(substr($l,0,5)!="https" && substr($l,0,4)!="http"){
			$l=parse_url($url)["scheme"].":".$l;
		}
		if(!in_array($l,$crawl_ready)){
			$crawl_ready[]=$l;
			$crawling[]=$l;
			
			$details= json_decode(get_details)($l);
			md5($details->URL);
		     $rows=$pdo->query("SELECT * FROM 'Index_Search'WHERE url_hash=''.md5($details->URL)");
			$rows=$rows->fetchColums();
			echo $rows."\n";
			print_r($details)."\n";
			
			//echo get_details($l)."\n";
			
				//echo $l."\n";
		}
		array_shift($crawling);
		foreach($crawling as $site){
			follow_Links($site);
		}
	


    }
}
print_r($crawl_ready);
follow_Links($start);

?>