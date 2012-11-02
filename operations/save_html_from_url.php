<?php

// stuff
error_reporting(E_ALL); ini_set('display_errors', '1');
header("Content-type: text/plain; charset=utf-8");
include('../inc/inc.php');	
include('../lib/simple_html_dom.php');	

// post-vars
$obj_id = (int)$_GET['obj_id'];
$url = $_GET['url']; 

// get source
$url_source = get_source_width_curl($url);

// if this is an image
if(substr($url_source["type"],0,5) == "image") {
	$hash = md5($url_source["content"]);
	$type_expl = explode("/", $url_source["type"]);
	$path = '../media/'.$hash.'.'.$type_expl[1];			
	if(!file_exists($path)) {
		file_put_contents($path, $url_source["content"]);					
		}
	$fullpath = substr(__FILE__,0, (-1*strlen('operations/save_html_from_url.php'))).'media/'.$hash.'.'.$type_expl[1];
	$imagesize = @getimagesize($fullpath);
	if(!$imagesize) {
		print 'Image could not be processed.';
		}
	else {
		save_html($obj_id, '<img src="'.$path.'" />');	
		}
	}

// if html,xml or json
elseif(stristr($url_source["type"],"text/xml")
	|| stristr($url_source["type"],"application/xml")
	|| stristr($url_source["type"],"application/xhtml+xml")
	|| stristr($url_source["type"],"text/html")
	|| stristr($url_source["type"],"text/plain")			
	|| stristr($url_source["type"],"application/json")) {

	// read all parsers
	$parsers_by_domain = array();
	foreach (glob("../operations/parsers/*.php") as $parser_name) {
	    include $parser_name;
	    $parser_name = substr($parser_name,22,-4);
		$parsers_by_domain[$parser_name] = $identify_by_domain;
		$parsers_by_source[$parser_name] = $identify_by_source;	
		}
	
	// match url
	foreach($parsers_by_domain as $parser_name=>$domains) {
		foreach($domains as $domain) {
			$url_parsed = parse_url($url);
			if(stristr($url_parsed['host'], $domain)) {
				$function_name = 'parse_'.$parser_name;
				break 2;
				}
			}	
		}
	
	// parse if domainmatch
	if(isset($function_name)) {	
		$html = $function_name($url, $url_source["content"]);
		if($html) {
			$html = download_inline_images($html);
			save_html($obj_id, $html);
			}	
		}
	
	// if no match we check the content instead
	else {		
		foreach($parsers_by_source as $parser_name=>$identifiers) {
			foreach($identifiers as $identifier) {
				if(strstr($url_source["content"], $identifier)) {
					$function_name = 'parse_'.$parser_name;
					break 2;
					}
				}	
			}
		if(isset($function_name)) {	
			$html = $function_name($url, $url_source["content"]);
			if($html) {
				$html = download_inline_images($html);
				save_html($obj_id, $html);
				}
			}
		else {
			
			// here we should have a generic parser
			print 'me not know this content :(';
			
			}
	
		}

	}
else {
	print 'unknown content type: '.$url_source["type"];	
	}




	// download inline images
function download_inline_images($html) {
	$html_parsed = str_get_html($html);
	function download_images($element) {
		if($element->tag == 'img') {
			if($element->src) {
				$image = get_source_width_curl($element->src);
				$hash = md5($image["content"]);
				$type_expl = explode("/", $image["type"]);
				$ext = $type_expl[1];
				$path = '../media/'.$hash.'.'.$ext;			
				if(!file_exists($path)) {
					file_put_contents($path, $image["content"]);					
					}
				$fullpath = substr(__FILE__,0, (-1*strlen('operations/save_html_from_url.php'))).'media/'.$hash.'.'.$ext;
				$imagesize = @getimagesize($fullpath);
				if($imagesize[0] < 40 || $imagesize[1] < 40) {
					$element->outertext = '';					
					}
				else {
					$element->src = $path;
					$element->style = 'max-width:'.$imagesize[0].'px;';
					}
				}
			else {
				$element->outertext = '';
				}
			}
		} 
	$html_parsed->set_callback('download_images');	
	$html = $html_parsed->save();
	return $html;	
	}


// function to save the parsed html
function save_html($obj_id, $html) {
	
	// db stuff
	include "../settings.php";
	$db_link=mysql_connect($db_host, $db_user, $db_pass) or die(mysql_error()); mysql_select_db($db_name, $db_link) or die(mysql_error()); mysql_query("SET CHARACTER SET 'utf8'");
	$db_connection = new mysqli("$db_host", "$db_user", "$db_pass", "$db_name");
	$set_utf8_stmt = $db_connection->prepare("SET CHARACTER SET 'utf8'");
	$set_utf8_stmt->execute();	

	// read object data from db
	$obj_t = mysql_query("SELECT s1.id, s1.type, s1.parent, s1.width, s1.sort_order FROM objects s1 LEFT JOIN objects s2 ON s1.id = s2.id AND s1.time < s2.time WHERE s2.id IS NULL AND s1.id = '$obj_id'");
	$obj_type = mysql_result($obj_t,0,'type');
	$obj_parent = mysql_result($obj_t,0,'parent');	
	$obj_width = mysql_result($obj_t,0,'width');
	$obj_sort_order = mysql_result($obj_t,0,'sort_order');
	
	// time now
	$time_now = microtime(true);		
	
	// insert content into content table and get id
	$insert_content_stmt = $db_connection->prepare("INSERT INTO content (content) VALUES (?)");
	$insert_content_stmt->bind_param("s", $html);
	$insert_content_stmt->execute();
	$content_id = $insert_content_stmt->insert_id;	
	$insert_content_stmt->close();
	
	// insert new updated object row
	mysql_query("INSERT INTO objects (id, time, type, parent, content, width, sort_order) VALUES ('$obj_id','$time_now','$obj_type','$obj_parent','$content_id','$obj_width','$obj_sort_order')");	
	
	print 'ok|'.$html;	

	}
	
?>