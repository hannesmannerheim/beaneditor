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
	if(!stristr($page_source, '<article>')) {
		print 'We couldn\'t read this aftonbladet-article. Copy-paste the text using the HTML-button instead.';
		return false;
		}

	// parse html
	$html = str_get_html($page_source);

	// get title
	$title_tag = $html->find("title",0);
	$title = $title_tag->innertext;

	// get h1
	$h1_tag = $html->find("article",0)->find('h1',0);
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