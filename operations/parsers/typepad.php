<?php

/* ---------------------------------------------------------------------------*
 *                                                                            *
 *                     This file is part of Beaneditor                        * 
 *                                                                            *
 *                                   _/                                       *
 *                                  /o|                                       *
 *                                 |o||                                       *
 *                                 |o||                                       *
 *                                  v\|                                       *
 *                                                                            *
 *                                                                            *
 *  Beaneditor is free software:  you can redistribute it and / or modify it  *
 *  under the terms of the GNU Affero General Public License as published by  *
 *  the Free Software Foundation, either version three of the License or (at  *
 *  your option) any later version.                                           *
 *                                                                            *
 *  Beaneditor is distributed in hope that it will be useful but WITHOUT ANY  *
 *  WARRANTY; without even the implied warranty of MERCHANTABILTY or FITNESS  *
 *  FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for  *
 *  more details.                                                             *
 *                                                                            *
 *  You should have received a copy of the GNU Affero General Public License  *
 *  along with Beaneditor. If not, see <http://www.gnu.org/licenses/>.        *
 *                                                                            * 
 *----------------------------------------------------------------------------*/   

// PARSER FOR TYPEPAD

// sites that we know for sure runs this cms
$identify_by_domain = array(
	'.typepad.com'
	);

// secondary regexp, for unmatched URL:s, we look for proof of this cms in html source
$identify_by_source = array();



// ---------------------------------------------------------------
// ---------------------------------------------------------------

// PARSER
// 1. fetch page from URL
// 2. print parsed HTML
function parse_typepad($url, $page_source) {
	
	$html = str_get_html($page_source);

	// get heading
	$title_tag = $html->find("title",0);
	$title = $title_tag->innertext;
	$heading = $html->find("h3.entry-header",0);
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
	$content = $html->find("div.entry-body",0);
	$content = str_get_html($content->innertext);	
	
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

		// remove all attributes, except href and src
		foreach($element->attr as $name=>$attr) {
			if($name != 'src' && $name != 'href') {
				$element->removeAttribute($name);
				}
			}

		// we keep a, span, img, object, embed, h1, h2, h3, h4, h5, h6, i, em, b, strong, blockquote
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
			$element->outertext = null;
			}
			
		} 
	$content->set_callback('remove_empty_elements');	
	$content = $content->save();

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