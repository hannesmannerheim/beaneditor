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

// PARSER FOR hd.se	

// (1) domain match
$identify_by_domain = array(
	'hd.se'
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
function parse_hdse($url, $page_source) {
		
	$html = str_get_html($page_source);

	// get content
	$title = $html->find("title",0)->innertext;
	$h1 = $html->find("article",0)->find("h1",0)->innertext;	
	$body = $html->find("article",0)->find("div.article-bd",0)->innertext;	
	
	$body = str_get_html($body);
	$lead = '';
	foreach($body->find('p') as $p) {
		if($p->class == 'sub-heading') {
			$lead = '<div class="lead">'.$p->outertext.'</div>';
			$p->outertext = '';
			}
		elseif($p->class == 'author vcard') {
			
			if(count($p->find('strong',0)->find('a'))>0) {
				$p->find('strong',0)->find('a',0)->outertext = $p->find('strong',0)->find('a',0)->innertext;
				}
			
			$title = $p->find('strong',0)->innertext.' - '.$title;
			$p->outertext = '';
			}		
		}
	
	
	if(count($body->find('div.extras-wrap')) > 0) {
		$extras = $body->find('div.extras-wrap',0)->innertext;
		$body->find('div.extras-wrap',0)->outertext = '';

		$extras = str_get_html($extras);

		// remove hr:s
		if(count($extras->find('hr')) > 0) {
			foreach($extras->find('hr') as $hr) {
				$hr->outertext = '';
				}
			}
			
		// remove h2.struct headings
		if(count($extras->find('h2.struct')) > 0) {
			foreach($extras->find('h2.struct') as $h2struct) {
				$h2struct->outertext = '';
				}
			}						
		
		// change to hires img:s, remove attributes and insert line break
		if(count($extras->find('div.images')) > 0) {	
			foreach($extras->find('div.images',0)->find('a') as $a) {
				if(substr($a->href,0,1) == '/') {
					$a->href = 'http://hd.se'.$a->href;
					unset($a->find('img',0)->width);
					unset($a->find('img',0)->height);
					$a->find('img',0)->src = $a->href;
					$a->outertext = $a->outertext.'<br>';
					}
				}
			// remove nesting i p
			foreach($extras->find('div.images',0)->find('p') as $imgp) {
				$imgp->outertext = $imgp->innertext;
				}
			}

		}
	
		
	// wrap in article structure
	$content = '<div class="article"><h1>'.$h1.'</h1>'.$lead.$body.'<address><a href="'.$url.'">'.$title.'</a></address></div>';		

	return $content;

	}



?>