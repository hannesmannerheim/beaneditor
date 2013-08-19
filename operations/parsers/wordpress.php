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

// PARSER FOR WORDPRESS


// sites that we know for sure runs this cms
$identify_by_domain = array(
	'.wordpress.com',
	'.blogetery.com',
	'.blogpeoria.com',
	'.freeblogit.com',
	'arbetaren.se',
	'arsinoe.se'
	);

// secondary regexp, for unmatched URL:s, we look for proof of this cms in html source
$identify_by_source = array(
	'<meta name="generator" content="WordPress',
	'/wp-content/themes/'	
	);



// ---------------------------------------------------------------
// ---------------------------------------------------------------

// PARSER
// 1. fetch page from URL
// 2. print parsed HTML
function parse_wordpress($url, $page_source) {
	
	// feed url
	$url_parsed = parse_url($url);
	if(isset($url_parsed['query'])) { $url_parsed['query'] .= '&feed=rss&withoutcomments=1'; }
	else { $url_parsed['query'] = 'feed=rss2&withoutcomments=1'; }	
	$single_post_feed_url = glue_url($url_parsed);

	// get feed source
	$single_post_feed_source = get_source_width_curl($single_post_feed_url);
	$single_post_feed_source = $single_post_feed_source["content"];
	
	// fallback parser if not full content in single post feed
	if(!stristr($single_post_feed_source, "<content:encoded>")) {
		return fallback_parser($url, $page_source);
		}

	// parse if feed has full content
	else {

		// content
		$xml_parsed = simplexml_load_string($single_post_feed_source, null, LIBXML_NOCDATA);
		
		$link = $xml_parsed->channel->item->link;
		$h1 = $xml_parsed->channel->item->title;
		$title = $xml_parsed->channel->title;	
		$content_decoded = $xml_parsed->channel->item->children('content', true)->encoded;

		// parse content
		$content = str_get_html((string)$content_decoded[0]);
	
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
			if(strstr($a->href,'flattrss_redirect')) {
				$content->find("a",$i)->outertext = '';
				}
			$i++;
			}
	
		// remove wordpress.com feeds
		$i=0;
		foreach($content->find("a") as $a) {
			if(strstr($a->href,'feeds.wordpress.com')) {
				$content->find("a",$i)->outertext = '';
				}
			$i++;
			}
			
		// loop through elements and do stuff
		function do_stuff($element) {
	
			// remove all attributes, except href and src
			foreach($element->attr as $name=>$attr) {
				if($name != 'src' && $name != 'href') {
					$element->removeAttribute($name);
					}
				}
	
			// we keep a, img, object, embed, h1, h2, h3, h4, h5, h6, i, em, b, strong, blockquote
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
				$element->outertext = '';
				}
			// remove a:s without innertext
			if($element->tag == 'a' && $element->innertext == null) {
				$element->outertext = '';
				}			
	
			// remove wordpress.com stats
			if(strstr($element->src,'stats.wordpress.com')) {
				$element->outertext = '';			
				}
				
			} 
		$content->set_callback('do_stuff');	
		$content = $content->save();		
		
		// no indent before image, if first in p
		$content = str_replace('<p><a','<p>&nbsp;<a',$content);
		$content = str_replace('<p><img','<p>&nbsp;<img',$content);
		
		// wrap in article structure
		return '<div class="article"><h1>'.$h1.'</h1>'.$content.'<address><a href="'.$link.'">'.$title.'</a></address></div>';
		}

	}




// reverse parse_url()
function glue_url($parsed) {
    if (!is_array($parsed)) {
        return false;
    }

    $uri = isset($parsed['scheme']) ? $parsed['scheme'].':'.((strtolower($parsed['scheme']) == 'mailto') ? '' : '//') : '';
    $uri .= isset($parsed['user']) ? $parsed['user'].(isset($parsed['pass']) ? ':'.$parsed['pass'] : '').'@' : '';
    $uri .= isset($parsed['host']) ? $parsed['host'] : '';
    $uri .= isset($parsed['port']) ? ':'.$parsed['port'] : '';

    if (isset($parsed['path'])) {
        $uri .= (substr($parsed['path'], 0, 1) == '/') ?
            $parsed['path'] : ((!empty($uri) ? '/' : '' ) . $parsed['path']);
    }

    $uri .= isset($parsed['query']) ? '?'.$parsed['query'] : '';
    $uri .= isset($parsed['fragment']) ? '#'.$parsed['fragment'] : '';

    return $uri;
} 

?>