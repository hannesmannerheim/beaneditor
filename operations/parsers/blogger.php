<?php

// PARSER FOR BLOGSPOT

// (1) sites that we know for sure is blogspot
$identify_by_domain = array(
	'.blogspot.com'
	);

// (2) for unmatched URL:s, we look for signs of blogger in html source
$identify_by_source = array(
	"<meta content='blogger' name='generator'/>"
	);


// ---------------------------------------------------------------
// ---------------------------------------------------------------

// PARSER
// function must be named "parse_" + (filename - ".php")
// 1. fetch page from URL
// 2. print parsed HTML
function parse_blogger($url, $page_source) {
		
	$html = str_get_html($page_source);

	// get heading
	$title_tag = $html->find("title",0);
	$title = $title_tag->innertext;
	
	// variant 1
	if($html->find("h3.post-title",0)) {
		$heading = $html->find("h3.post-title",0);
		$h1 = $heading->innertext;		
	
		// if there is a link in heading, take that innertext instead
		if($heading->find('a',0)) {
			$link = $heading->find('a',0);
			$h1 = $link->innertext;				
			$posturl = $link->href;
			}
		else {
			$posturl = $url;
			}
	
		// get content
		$content = $html->find("div.post-body",0);
		$content = str_get_html($content->innertext);			
		}
	
	// variant 2
	elseif($html->find("div.hentry",0)->find("h2",0)) {
		$heading = $html->find("div.hentry",0)->find("h2",0);
		$h1 = $heading->innertext;		
	
		// if there is a link in heading, take that innertext instead
		if($heading->find('a',0)) {
			$link = $heading->find('a',0);
			$h1 = $link->innertext;				
			$posturl = $link->href;
			}
		else {
			$posturl = $url;
			}
	
		// get content
		$content = $html->find("div.hentry",0);
		$content = $content->find("div.entry",0);
		$content = str_get_html($content->innertext);					
		}
	
	// remove facebook iframes
	$i=0;
	foreach($content->find("iframe") as $iframe) {
		if(strstr($iframe->src,'facebook.com')) {
			$content->find("iframe",$i)->outertext = '';
			}
		$i++;
		}

	// remove flattr-links
	$i=0;
	foreach($content->find("a") as $a) {
		if(strstr($a->href,'flattr.com')) {
			$content->find("a",$i)->outertext = '';
			}
		$i++;
		}
	
	//replace double linebreak with p
	$content = str_get_html(str_replace('<br />','</p><p>',$content));
	$content = str_get_html(str_replace('<br/>','</p><p>',$content));
	$content = str_get_html(str_replace('<br>','</p><p>',$content));
	
	// loop through elements and do stuff
	function remove_empty_elements($element) {
	
		// remove hidden elements
		if(stristr($element->style,'display:none') || stristr($element->style,'display: none')) {
			$element->outertext = '';	
			}

		// remove twitter share
		if(stristr($element->href,'twitter.com/share')) {
			$element->outertext = '';	
			}			

		// remove tags
		if($element->class == 'tags') {
			$element->outertext = '';	
			}

		// remove scripts
		if($element->tag == 'script') {
			$element->outertext = '';	
			}
		
		// remove all attributes, except href and src
		foreach($element->attr as $name=>$attr) {
			if($name != 'src' && $name != 'href' && $name != 'class') {
				$element->removeAttribute($name);
				}
			$element->class = '';
			}

		// we keep a, img, object, embed, h1, h2, h3, h4, h5, h6, i, em, b, strong, blockquote
		// all other converted to p
		if($element->tag != 'img'
		&& $element->tag != 'span'		
		&& $element->tag != 'a'		
		&& $element->tag != 'li'	
		&& $element->tag != 'ul'	
		&& $element->tag != 'ol'			
		&& $element->tag != 'object'
		&& $element->tag != 'embed'
		&& $element->tag != 'h1'
		&& $element->tag != 'h2'
		&& $element->tag != 'h3'
		&& $element->tag != 'h4'
		&& $element->tag != 'h5'
		&& $element->tag != 'h6'
		&& $element->tag != 'i'
		&& $element->tag != 'em'
		&& $element->tag != 'b'								
		&& $element->tag != 'strong'
		&& $element->tag != 'blockquote') {
			$element->tag = 'p';
			}
			
		// remove p:s without innertext
		if($element->tag == 'p' && $element->innertext == null) {
			$element->outertext = '';
			}
			
		} 
	$content->set_callback('remove_empty_elements');	
	$content = $content->save();

	// remove empty class attribute
	while(strstr($content,' class=""')) {
		$content = str_replace(' class=""','',$content);
		}		

	// remove <p />
	while(strstr($content,'<p />')) {
		$content = str_replace('<p />','',$content);
		}	
	// remove </div> (could be divs with no starting <div> that is not converted to p above)
	while(strstr($content,'</div>')) {
		$content = str_replace('</div>','',$content);
		}
		
	// wrap in article structure
	$content = '<div class="article"><h1>'.$h1.'</h1><p>'.$content.'</p><address><a href="'.$posturl.'">'.$title.'</a></address></div>';		

	// remove whitespace before p-tag
	while(strstr($content,' <p')) {
		$content = str_replace(' <p','<p',$content);
		}		
	while(strstr($content,' </p')) {
		$content = str_replace(' </p','</p',$content);
		}	
	// remove whitespace after p-tag
	while(strstr($content,'p> ')) {
		$content = str_replace('p> ','p>',$content);
		}		
	// remove nested p
	while(strstr($content,'<p><p>')) {
		$content = str_replace('<p><p>','<p>',$content);
		}
	while(strstr($content,'</p></p>')) {
		$content = str_replace('</p></p>','</p>',$content);
		}		
	// remove empty p
	while(strstr($content,'<p></p>')) {
		$content = str_replace('<p></p>','',$content);
		}

	// no indent before image, if first in p
	$content = str_replace('<p><a','<p>&nbsp;<a',$content);
	$content = str_replace('<p><img','<p>&nbsp;<img',$content);
	
	return $content;

	}



?>