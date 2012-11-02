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

// PARSER FOR TUMBLR

// (1) domain match
$identify_by_domain = array(
	'tumblr.com'
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
function parse_tumblr($url, $page_source) {
		
	// get feed source
	$single_post_feed_source = get_source_width_curl($url.'/rss');
	$single_post_feed_source = $single_post_feed_source["content"];
	
	// parse feed
	if(!stristr($single_post_feed_source, '</rss>')) {
		print 'Did not recieve a valid rss feed, check your post url by adding /rss after it';
		return false;
		}
		
	$xml_parsed = simplexml_load_string($single_post_feed_source, null, LIBXML_NOCDATA);
	$h1 = $xml_parsed->channel->item->title;
	$title = $xml_parsed->channel->title;	
	$content_decoded = $xml_parsed->channel->item->description;
		
	// wrap in article structure
	$content = '<div class="article"><h1>'.$h1.'</h1>'.$content_decoded.'<address><a href="'.$url.'">'.$title.'</a></address></div>';		

	return $content;

	}



?>