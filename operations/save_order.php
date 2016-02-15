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

// read data
$order_expl = explode(',',substr($_GET["order"],1));
$obj_sort_order = 0;
foreach($order_expl as $obj_id) {

	// secure against sql-insert
	$obj_id = (int)$obj_id;
	
	// time now
	$time_now = microtime(true);	

	// read object data from db
	$obj_t = $db_connection->query("SELECT s1.id, s1.type, s1.parent, s1.content, s1.width FROM objects s1 LEFT JOIN objects s2 ON s1.id = s2.id AND s1.time < s2.time WHERE s2.id IS NULL AND s1.id = '$obj_id'");
	$obj_t_result = $obj_t->fetch_assoc();
	$obj_type = $obj_t_result['type'];
	$obj_parent = $obj_t_result['parent'];	
	$obj_content = $obj_t_result['content'];
	$obj_width = $obj_t_result['width'];
	
	// insert new updated row
	$db_connection->query("INSERT INTO objects (id, time, type, parent, content, width, sort_order) VALUES ('$obj_id','$time_now','$obj_type','$obj_parent','$obj_content','$obj_width','$obj_sort_order')");
	$obj_sort_order++;
	}

print 'ok';
	
?>