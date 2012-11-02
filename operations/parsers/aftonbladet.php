<?php

// PARSER FOR AFTONBLADET.SE


// sites that we know for sure runs this cms
$identify_by_domain = array(
	'aftonbladet.se'
	);

// secondary regexp, for unmatched URL:s, we look for proof of this cms in html source
$identify_by_source = array(
	);



// ---------------------------------------------------------------
// ---------------------------------------------------------------

// PARSER
// 1. fetch page from URL
// 2. print parsed HTML
function parse_aftonbladet($url, $page_source) {
	
	// can we read?
	if(!stristr($page_source, 'abMainArticle')) {
		print 'We couldn\'t read this aftonbladet-article. Copy-paste the text using the HTML-button instead.';
		return false;
		}

	// parse html
	$html = str_get_html($page_source);

	// get title
	$title_tag = $html->find("title",0);
	$title = $title_tag->innertext;

	// get h1
	$h1_tag = $html->find("div#abMainArticle",0)->find('h1',0);
	$h1 = $h1_tag->innertext;

	// get lead
	$lead_tag = $html->find("div.abLeadText",0);
	$lead = $lead_tag->innertext;
	
	// get body
	$body_tag = $html->find("div#abBodyText",0);
	$body = $body_tag->innertext;		
		
	// wrap in article structure
	return '<div class="article"><h1>'.$h1.'</h1><div class="lead">'.$lead.'</div>'.$body.'<address><a href="'.$url.'">'.$title.'</a></address></div>';

	}


?>