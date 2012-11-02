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

// PARSER FOR BLAY.SE	

// (1) sites that we know for sure is blogspot
$identify_by_domain = array(
	'blay.se'
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
function parse_blay($url, $page_source) {
		
	$html = str_get_html($page_source);

	// get title
	$title_tag = $html->find("title",0);
	$title = $title_tag->innertext;

	// get h1
	$h1_tag = $html->find("div.post",0)->find('h1',0);
	$h1 = $h1_tag->innertext;
	
	// get text body
	$html->find("div.post",0)->find("div.footnotes",0)->style = 'font-size:14px;border-top:1px dashed gray;margin-top:30px;';
	$html->find("div.post",0)->find("div.footnotes",0)->find("ol",0)->style = 'padding-left:20px;';	
	$html->find("div.post",0)->find("div.meta",0)->outertext = '';
	$html->find("div.post",0)->find("h1",0)->outertext = '';	
	$html->find("div.post",0)->find("div#disqus_thread",0)->outertext = '';		
	$postbody_tag = $html->find("div.post",0);
	$postbody = $postbody_tag->innertext;	
		
	// wrap in article structure
	$content = '<div class="article"><h1>'.$h1.'</h1>'.$postbody.'<address><a href="'.$url.'">blay.se :: '.$title.'</a></address></div>';		

	return $content;

	}



?>