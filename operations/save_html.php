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

// stuff
error_reporting(E_ALL); ini_set('display_errors', '1');
header("Content-type: text/plain; charset=utf-8");
include "../settings.php";
$db_connection = new mysqli("$db_host", "$db_user", "$db_pass", "$db_name");
$set_utf8_stmt = $db_connection->prepare("SET CHARACTER SET 'utf8'");
$set_utf8_stmt->execute();

// post-vars
$obj_id = (int)$_POST['obj_id'];
$html = $_POST['html']; 

// read object data from db
$obj_t = $db_connection->query("SELECT s1.id, s1.type, s1.parent, s1.width, s1.sort_order FROM objects s1 LEFT JOIN objects s2 ON s1.id = s2.id AND s1.time < s2.time WHERE s2.id IS NULL AND s1.id = '$obj_id'");
$obj_t_result = $obj_t->fetch_assoc();
$obj_type = $obj_t_result['type'];
$obj_parent = $obj_t_result['parent'];	
$obj_width = $obj_t_result['width'];
$obj_sort_order = $obj_t_result['sort_order'];

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
$db_connection->query("INSERT INTO objects (id, time, type, parent, content, width, sort_order) VALUES ('$obj_id','$time_now','$obj_type','$obj_parent','$content_id','$obj_width','$obj_sort_order')");	

print 'ok';
	
?>