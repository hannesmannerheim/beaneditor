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

// fallback parser

function fallback_parser($url, $page_source) {

	// get charset
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_HEADER, 1);	
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.9.1.3) Gecko/20090824 Firefox/3.5.3 GTB5");
	$c = curl_exec($ch);
	$content_type = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
	curl_close($ch);
	if(stristr($content_type, 'charset')) {
		$charset = substr($content_type,strpos($content_type,'charset')+8);
		if(strstr($charset,';')) {
			$charset = substr($charset,0, strpos($charset,';'));			
			}
		}
	else {
		$charset = mb_detect_encoding($page_source);
		}

	// fetch through readability
	$postfields = 'token=&extensionType=addon&extensionVersion=2.4&extensionBrowser=firefox&fromEmbed=0&legacyBookmarklet=0&url='.urlencode($url).'&doc='.urlencode($page_source).'&charset='.$charset.'&read=1';
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, "http://www.readability.com/articles/queue");
	curl_setopt($ch, CURLOPT_POSTFIELDS,$postfields);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_HEADER, 1);	
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.9.1.3) Gecko/20090824 Firefox/3.5.3 GTB5");
	$c = curl_exec($ch);
	curl_close($ch);
	$readability_article_id = getcontent($c,'Location: http://www.readability.com/articles/',"\n");
	$html = str_get_html(file_get_contents('http://www.readability.com/articles/'.trim($readability_article_id['content']).'?readbar=1'));

	// get content
	$h1 = $html->find("h1.entry-title",0)->innertext;
	$body = $html->find("section.entry-content",0)->innertext;
	$source_html = str_get_html($page_source);
	$title = $source_html->find("title",0)->innertext;	
	
	// unnest img:s from p:s (to remove indent)
	$body = str_get_html($body);
	foreach($body->find('p') as $p) {
		if(count($p->find('img'))==1) {
			$orig_p = $p->outertext;
			$img = $p->find('img',0)->outertext;
			$p->find('img',0)->outertext = '';
			if(strlen($p->outertext<4)) {
				$p->outertext = $img;
				}
			else {
				$p->outertext = $orig_p;
				}
			}
		}
		
	// wrap in article structure
	$content = '<div class="article"><h1>'.$h1.'</h1>'.$body.'<address><a href="'.$url.'">'.$title.'</a></address></div>';		

	return $content;

	}


// get selected content from page 
function getcontent($string, $start, $end, $offset=0) {
	$startlen = strlen($start);
	$startpos = strpos($string, $start, $offset);
	if(!$startpos) {
		return FALSE;
		}
	else {
		$contentpos = $startpos + $startlen;
		$endpos = strpos($string, $end, $contentpos);
		$contentlen = $endpos - $contentpos;
		$res["content"] = substr($string, $contentpos, $contentlen);
		$res["offset"] = $endpos;
		return $res;
		}	
	}


?>