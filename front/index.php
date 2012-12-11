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

include "../settings.php";
include "../inc/inc.php";

// rss feed
$arr = explode('/', $_SERVER["REQUEST_URI"]);
$c = count($arr);
unset($arr[$c-1], $arr[$c-2]);
$feed_url = "http://".$_SERVER['SERVER_NAME'].implode('/', $arr).'/rss/'; 

// permalinks
if(count($_GET) == 1) {
	foreach($_GET as $perma_id=>$this_var_does_noting_lol) {
		$requested_issue = (int)$perma_id;
		}
	}
else {
	$requested_issue = 'latest';
	}


?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN"
"http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>  
		<title><?php print $name; ?></title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<meta name="description" content="" />
		<link rel="stylesheet" type="text/css" href="../css/2.css" />
		<link rel="shortcut icon" href="favicon.ico" />
		<link rel="alternate" type="application/rss+xml" title="<?php print $name; ?> RSS feed" href="<?php print $feed_url ?>" />		
	</head>
	<body>
		<ul id="objects_container"><li id="obj0"><?php 
		
		
		// get latest issues
		render_bean(false, false, $requested_issue) 
		
		
		
		?></li></ul>	
	    <script type="text/javascript" src="../js/jquery-1.6.3.min.js"></script>
	    <script type="text/javascript" src="../js/2.js"></script>		
	</body>
</html>