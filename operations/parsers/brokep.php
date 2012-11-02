<?php

// PARSER FOR BLOG.BROKEP.COM

// (1) sites that we know for sure is blogspot
$identify_by_domain = array(
	'blog.brokep.com'
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
function parse_brokep($url, $page_source) {

	$start = strpos($page_source,"<link rel='shortlink' href='")+28;
	$end = strpos($page_source,"'",$start)-$start;
	$shortlink = substr($page_source,$start,$end);
		
	include 'wordpress.php';
	return parse_wordpress($shortlink,$page_source);
	
	}



?>