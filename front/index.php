<?php

include "../settings.php";
include "../inc/inc.php";

// rss feed
$arr = explode('/', $_SERVER["REQUEST_URI"]);
$c = count($arr);
unset($arr[$c-1], $arr[$c-2]);
$feed_url = "http://".$_SERVER['SERVER_NAME'].implode('/', $arr).'/rss/'; 


?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN"
"http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>  
		<title><?php print $name; ?></title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<meta name="description" content="" />
		<link rel="stylesheet" type="text/css" href="../css/1.css" />
		<link rel="shortcut icon" href="favicon.ico" />
		<link rel="alternate" type="application/rss+xml" title="<?php print $name; ?> RSS feed" href="<?php print $feed_url ?>" />		
	</head>
	<body>
		<ul id="objects_container"><li id="obj0"><?php 
		
		
		// we haz bean
		render_bean() 
		
		
		
		?></li></ul>	
	</body>
</html>