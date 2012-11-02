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
	'expressen.se'
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
function parse_expressen($url, $page_source) {
		
	$html = str_get_html($page_source);

	// get title
	$title = $html->find("h1",0)->innertext;
	$lead = $html->find(".b-text_article-preamble",0)->innertext;	
	$postbody = $html->find(".b-article__content",0)->find(".b-text_article",1)->find("div",0)->innertext;	
		
	// wrap in article structure
	$content = '<div class="article"><h1>'.$title.'</h1><div class="lead">'.$lead.'</div>'.$postbody.'<address><a href="'.$url.'">Expressen | '.$title.'</a></address></div>';		

	return $content;

	}



?>