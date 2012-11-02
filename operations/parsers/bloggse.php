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

// PARSER FOR blogg.se

// (1) domain match
$identify_by_domain = array(
	'blogg.se'
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
function parse_bloggse($url, $page_source) {
		
	$html = str_get_html($page_source);

	// get title
	$title = $html->find("title",0)->innertext;
	$h1 = $html->find("h3",0)->innertext;	
	$postbody = $html->find(".entrybody",0)->innertext;	
	
	// full url to images
	$url_expl = explode('/',$url);
	$base_url = 'http://'.$url_expl[2].'/';
	$postbody = str_replace('../../',$base_url,$postbody);
	
	// stop link and image indent
	$postbody = str_replace('<p><a','<p>&nbsp;<a',$postbody);
	$postbody = str_replace('<p><img','<p>&nbsp;<img',$postbody);	
		
	// wrap in article structure
	$content = '<div class="article"><h1>'.$h1.'</h1>'.$postbody.'<address><a href="'.$url.'">'.$title.'</a></address></div>';		
	return $content;

	}



?>