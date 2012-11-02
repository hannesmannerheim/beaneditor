<?php

// PARSER FOR feministisktperspektiv	

// (1) sites that we know for sure is feministisktperspektiv
$identify_by_domain = array(
	'feministisktperspektiv.se'
	);

// (2) for unmatched URL:s, we look for signs in html source
$identify_by_source = array(
	);


// ---------------------------------------------------------------
// ---------------------------------------------------------------

// PARSER
// function must be named "parse_" + (filename - ".php")
// 1. fetch page from URL
// 2. print parsed HTML
function parse_feministisktperspektiv($url, $page_source) {
		
	$html = str_get_html($page_source);

	// get title
	$title = $html->find("meta[property=og:title]",0)->content;
	$lead = $html->find(".article_container",0)->find(".lede",0)->innertext;
	$postbody = $html->find(".article_container",0)->find("#body",0)->innertext;	
		
	// wrap in article structure
	$content = '<div class="article"><h1>'.$title.'</h1><div class="lead">'.$lead.'</div>'.$postbody.'<address><a href="'.$url.'">feministisktperspektiv.se :: '.$title.'</a></address></div>';		

	return $content;

	}



?>