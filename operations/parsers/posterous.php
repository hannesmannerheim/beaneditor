<?php

// PARSER FOR POSTEROUS

// (1) sites that we know for sure runs posterous
$identify_by_domain = array(
	'.posterous.com'
	);

// (2) secondary regexp, for unmatched URL:s, we look for proof of posterous in html source
$identify_by_source = array(
	);



// ---------------------------------------------------------------
// ---------------------------------------------------------------

// PARSER
// 1. fetch page from URL
// 2. print parsed HTML
function parse_posterous($url, $page_source) {
	
	$html = str_get_html($page_source);

	// get heading
	$title_tag = $html->find("title",0);
	$title = $title_tag->innertext;
	
	// metadata for api request
	$body_tag = $html->find("div.posterous_site_data",0);
	$post_id = (int)$body_tag->attr['data-post-id'];
	$site_id = (int)$body_tag->attr['data-site-id'];
	
	// api proxy request
	$api_proxy_source = file_get_contents('http://hannes.kompisen.se/posterous_proxy/?site='.$site_id.'&post='.$post_id);
	$api_proxy_source_decoded = json_decode($api_proxy_source);

	// data
	$h1 = $api_proxy_source_decoded->title;
	$posturl = $api_proxy_source_decoded->full_url;	

	// get body and convert span-image-elements to img elements
	$post_body = $api_proxy_source_decoded->body_cleaned;
	$post_body_parsed = str_get_html($post_body);
	$i=0;
	foreach($post_body_parsed->find("span") as $span) {
		if($span->attr['data-type'] == 'image') {
			$post_body_parsed->find("span",$i)->outertext = '<img src="'.$span->attr['data-full-url'].'" />';;
			}
		$i++;
		}	
	$post_body = $post_body_parsed->save();
	
	// article structure
	$article = '<div class="article"><h1>'.$h1.'</h1><p>'.$post_body.'</p><address><a href="'.$posturl.'">'.$title.'</a></address></div>';		

	// no indent before image, if first in p
	$article = str_replace('<p><a','<p>&nbsp;<a',$article);
	$article = str_replace('<p><img','<p>&nbsp;<img',$article);
	
	return $article;
	
	}


?>