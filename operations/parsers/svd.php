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

// PARSER FOR svd.se	

// (1) domain match
$identify_by_domain = array(
	'svd.se'
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
function parse_svd($url, $page_source) {
		
	$html = str_get_html($page_source);

	// get title
	$title = $html->find("#article",0)->find("h1",0)->innertext;
	$lead = $html->find("#article",0)->find(".preamble",0)->innertext;	

	// remove unwanted stuff
	foreach($html->find("#article",0)->find(".articletext",0)->find("p") as $p) {
		// we don't want p:s with only <strong>, <a>, and <br>
		// like "HELA GRANSKNINGEN Läs tidigare delar i serien om blågula mutor"
		// or "LÄS OCKSÅ: Dyrt för företag att ta lätt på korruption"
		// that's just crap
		// although we want p:s with only strongs...
		$length_of_strong_a_and_br = 0;
		foreach($p->find('br') as $br) {
			$length_of_strong_a_and_br += strlen($br->outertext);
			}
		foreach($p->find('strong') as $strong) {
			$length_of_strong_a_and_br += strlen($strong->outertext);
			}
		foreach($p->find('a') as $a) {
			$length_of_strong_a_and_br += strlen($a->outertext);
			}			
		$other_length = strlen($p->innertext) - $length_of_strong_a_and_br;	
		// remove p of less than 10 chars and contains <a>
		if($other_length<10 && count($p->find('a'))>0) {
			$p->outertext = '';	
			}
		}
	//remove ads
	foreach($html->find("#article",0)->find(".articletext",0)->find("div.ad") as $ads) {
		$ads->outertext='';
		}			
	// remove h2 and .article-part in article footer
	foreach($html->find("#article",0)->find(".articletext",0)->find("h2") as $h2) {
		$h2->outertext='';
		}
	foreach($html->find("#article",0)->find(".articletext",0)->find("div.article-part") as $harticlepart) {
		$harticlepart->outertext='';
		}		
		

	// get body
	$postbody = $html->find("#article",0)->find(".articletext",0)->innertext;	
		
	// wrap in article structure
	$content = '<div class="article"><h1>'.$title.'</h1><div class="lead">'.$lead.'</div>'.$postbody.'<address><a href="'.$url.'">SvD | '.$title.'</a></address></div>';		

	return $content;

	}



?>