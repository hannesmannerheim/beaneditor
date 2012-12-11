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

// PARSER FOR POSTEROUS

// (1) sites that we know for sure runs posterous
$identify_by_domain = array(
	'twitter.com'
	);

// (2) secondary regexp, for unmatched URL:s, we look for proof of posterous in html source
$identify_by_source = array(
	);



// ---------------------------------------------------------------
// ---------------------------------------------------------------

// PARSER
// 1. fetch page from URL
// 2. print parsed HTML
function parse_twitter($url, $page_source) {
	include "../settings.php";	
	
	if(!stristr($url,'/status/')) {
		print 'not a valid status URL, should contain "/status/"';
		}
	else {

		// get tweet data from twitter api
		$tweet_id = (int)substr($url,(strpos($url,'/status/')+8));
		$tweet = json_decode(file_get_contents('https://api.twitter.com/1/statuses/show.json?id='.$tweet_id));
		$tweet_timestamp = strtotime($tweet->created_at);
		$tweet_date = date("H:i, ",$tweet_timestamp).strftime("%e ", $tweet_timestamp).substr($months[strftime("%B", $tweet_timestamp)],0,3).strftime(" %Y", $tweet_timestamp);

		// fix links
		$tweet_body = twitterify($tweet->text);
		
		// translate t.co addresses
		$html = str_get_html($tweet_body);
		$i=0;
		foreach($html->find("a") as $a) {
			if(strstr($a->href,'://t.co/')) {
				$tco_src = get_source_width_curl($a->href);
				$url_start = strpos($tco_src['content'],'content="0;URL=')+15;
				$url_length = strpos($tco_src['content'],'"', $url_start)-$url_start;
				$real_url = substr($tco_src['content'],$url_start,$url_length);
				$html->find("a",$i)->href = $real_url;
				$html->find("a",$i)->innertext = $real_url;				
				}
			$i++;
			}
		$tweet_body = $html->save();		
		
		// contruct and return tweet html	
		$the_tweet = '<div class="micropost">';
		$the_tweet .= '<div class="micropost_body">'.$tweet_body.'</div>';
		$the_tweet .= '<div class="micropost_date"><a href="https://twitter.com/'.$tweet->user->screen_name.'/status/'.$tweet_id.'">'.$tweet_date.'</a></div>';
		$the_tweet .= '<a href="http://twitter.com/'.$tweet->user->screen_name.'"><img class="micropost_img" src="'.$tweet->user->profile_image_url.'"></a>';
		$the_tweet .= '<div class="micropost_author"><span class="fullname">'.$tweet->user->name.'</span> <a href="http://twitter.com/'.$tweet->user->screen_name.'">@'.$tweet->user->screen_name.'</a></div>';
		$the_tweet .= '</div>';
		return $the_tweet;
	
		}
	}


function twitterify($ret) {
  $ret = preg_replace("#(^|[\n ])([\w]+?://[\w]+[^ \"\n\r\t< ]*)#", "\\1<a href=\"\\2\" target=\"_blank\">\\2</a>", $ret);
  $ret = preg_replace("#(^|[\n ])((www|ftp)\.[^ \"\t\n\r< ]*)#", "\\1<a href=\"http://\\2\" target=\"_blank\">\\2</a>", $ret);
  $ret = preg_replace("/@(\w+)/", "<a href=\"http://twitter.com/\\1\" target=\"_blank\">@\\1</a>", $ret);
  $ret = preg_replace("/#(\w+)/", "<a href=\"http://search.twitter.com/search?q=\\1\" target=\"_blank\">#\\1</a>", $ret);
return $ret;
}


?>