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

// PARSER FOR STATUSNET

// (1) sites that we know for sure runs posterous
$identify_by_domain = array(
	'identi.ca',
	'quitter.se',
	'freesocial.org',
	'oracle.skilledtests.com',
	'indy.im',
	'status.fsf.org',
	'micro.fragdev.com',
	'status.pirati.ca',
	'dent.gomertronic.com',
	'statusnet.atari-frosch.de',
	'u.qdnx.org',	
	'rainbowdash.net',
	'loadaverage.org',					
	'federati.net',
	'somsants.net'							
	);

// (2) secondary regexp, for unmatched URL:s, we look for proof of posterous in html source
$identify_by_source = array(
	);



// ---------------------------------------------------------------
// ---------------------------------------------------------------

// PARSER
// 1. fetch page from URL
// 2. print parsed HTML
function parse_statusnet($url, $page_source) {
	include "../settings.php";	
	
	if(!stristr($url,'/notice/')) {
		print 'not a valid notice URL, should contain "/notice/"';
		}
	else {

		// get notice data from statusnet api
		
		$api_base = substr($url,0,strpos($url, '/notice'));
		$notice_id = (int)substr($url,(strpos($url,'/notice/')+8));
		$notice = json_decode(file_get_contents($api_base.'/api/statuses/show/'.$notice_id.'.json'));
		$notice_timestamp = strtotime($notice->created_at);
		$notice_date = date("H:i, ",$notice_timestamp).strftime("%e ", $notice_timestamp).substr($months[strftime("%B", $notice_timestamp)],0,3).strftime(" %Y", $notice_timestamp);

		// fix links
		$notice_body = $notice->statusnet_html;
		
		// notice url
		$notice_url = str_replace($notice->user->screen_name, '',$notice->user->statusnet_profile_url).'notice/'.$notice->id;
		
		// construct and return notice html	
		$the_notice = '<div class="micropost">';
		$the_notice .= '<div class="micropost_body">'.$notice_body.'</div>';
		$the_notice .= '<div class="micropost_date"><a href="'.$notice_url.'">'.$notice_date.'</a></div>';
		$the_notice .= '<a href="'.$notice->user->statusnet_profile_url.'"><img class="micropost_img" src="'.$notice->user->profile_image_url.'"></a>';
		$the_notice .= '<div class="micropost_author"><span class="fullname">'.$notice->user->name.'</span> <a href="'.$notice->user->statusnet_profile_url.'">@'.$notice->user->screen_name.'</a></div>';
		$the_notice .= '</div>';
		return $the_notice;
	
		}
	}


?>