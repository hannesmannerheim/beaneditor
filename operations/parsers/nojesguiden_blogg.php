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

// PARSER FOR EXPRESSEN

// (1) domain match
$identify_by_domain = array(
	'nojesguiden.se'
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
function parse_nojesguiden_blogg($url, $page_source) {
		
	$html = str_get_html($page_source);

	// get title
	$title = $html->find("header.article-header",0)->find('h1',0)->innertext;
	$postbody = $html->find("div.article-content",0)->find("div.field-type-text-with-summary",0)->find('div',0)->innertext;
	$author = $html->find("header.article-header",0)->find('span',0)->find('a',0)->innertext;
		
	// line break between images
	$postbody = str_replace('<p><img','<br><p><img', $postbody);	
	
	// wrap in article structure
	$content = '<div class="article"><h1>'.$title.'</h1>'.$postbody.'<address><a href="'.$url.'">Nöjesguiden | '.$author.' | '.$title.'</a></address></div>';		

	return $content;

	}



?>