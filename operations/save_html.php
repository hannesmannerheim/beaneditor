<?php

// stuff
error_reporting(E_ALL); ini_set('display_errors', '1');
header("Content-type: text/plain; charset=utf-8");
include "../settings.php";
$db_link=mysql_connect($db_host, $db_user, $db_pass) or die(mysql_error()); mysql_select_db($db_name, $db_link) or die(mysql_error()); mysql_query("SET CHARACTER SET 'utf8'");
$db_connection = new mysqli("$db_host", "$db_user", "$db_pass", "$db_name");
$set_utf8_stmt = $db_connection->prepare("SET CHARACTER SET 'utf8'");
$set_utf8_stmt->execute();

// post-vars
$obj_id = (int)$_POST['obj_id'];
$html = $_POST['html']; 

// read object data from db
$obj_t = mysql_query("SELECT s1.id, s1.type, s1.parent, s1.width, s1.sort_order FROM objects s1 LEFT JOIN objects s2 ON s1.id = s2.id AND s1.time < s2.time WHERE s2.id IS NULL AND s1.id = '$obj_id'");
$obj_type = mysql_result($obj_t,0,'type');
$obj_parent = mysql_result($obj_t,0,'parent');	
$obj_width = mysql_result($obj_t,0,'width');
$obj_sort_order = mysql_result($obj_t,0,'sort_order');

// time now
$time_now = microtime(true);		

// if empty, set content_id to 0
if(strlen($html) == 0) {
	$content_id = 0;
	}
else {
	// insert content into content table and get id
	$insert_content_stmt = $db_connection->prepare("INSERT INTO content (content) VALUES (?)");
	$insert_content_stmt->bind_param("s", $html);
	$insert_content_stmt->execute();
	$content_id = $insert_content_stmt->insert_id;	
	$insert_content_stmt->close();
	}

// insert new updated object row
mysql_query("INSERT INTO objects (id, time, type, parent, content, width, sort_order) VALUES ('$obj_id','$time_now','$obj_type','$obj_parent','$content_id','$obj_width','$obj_sort_order')");	

print 'ok';
	
?>