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

// PARSER FOR TPB

// (1) sites that we know for sure is blogspot
$identify_by_domain = array(
	'thepiratebay.se',
	'thepiratebay.org',
	'thepiratebay.sx'	
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
function parse_thepiratebay($url, $page_source) {
		
	$html = str_get_html($page_source);

	
	// if blog
	if(stristr($url,'/blog/')) {
	
		// get h1
		$h1_tag = $html->find(".post",0)->find("h3",0);
		$h1 = $h1_tag->innertext;
		
		// get title
		$title = $h1.' - TPB';		
		
		// get text body
		$html->find(".post",0)->find("h3",0)->outertext = '';
		$html->find(".post",0)->find("div.meta",0)->outertext = '';			
		$postbody_tag = $html->find(".post",0);
		$postbody = $postbody_tag->innertext;	
		}

	// if torrent
	elseif(stristr($url,'/torrent/') || stristr($url,'details.php?id=')) {
		
		// title
		$title_tag = $html->find("title",0);
		$title = $title_tag->innertext;

		// h1
		$h1_tag = $html->find("div#title",0);
		$h1 = $h1_tag->innertext;

		// torrent link
		$torrentlink = $html->find("div.download",0)->find("a",1)->outertext;

		// body
		$postbody = $html->find("div.nfo",0)->find("pre",0)->innertext;
		$postbody = '<p>'.str_replace('\r\n','</p><p>', $postbody).'</p>';
		$postbody = str_replace('\n\n','</p><p>', $postbody).'<p style="line-height:40px;font-size:24px;text-align:center;padding-top:20px;">'.$torrentlink.'</p>';
		
		}

		
	// wrap in article structure
	$content = '<div class="article"><h1>'.$h1.'</h1>'.$postbody.'<address><a href="'.$url.'">'.$title.'</a></address></div>';		

	return $content;

	}



?>