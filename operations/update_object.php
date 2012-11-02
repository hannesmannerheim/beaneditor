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
$db_link=mysql_connect($db_host, $db_user, $db_pass) or die(mysql_error()); mysql_select_db($db_name, $db_link) or die(mysql_error()); mysql_query("SET CHARACTER SET 'utf8'");
$db_connection = new mysqli("$db_host", "$db_user", "$db_pass", "$db_name");
$set_utf8_stmt = $db_connection->prepare("SET CHARACTER SET 'utf8'");
$set_utf8_stmt->execute();

// read json to array
$obj_decoded = json_decode($_POST['data']);

// time now
$time_now = microtime(true);		

// read obj data from db and replace with new data
foreach ($obj_decoded as $this_obj_num=>$this_obj) {
	
	// read object data from db
	$new_obj_array[$this_obj_num]['id'] = (int)$this_obj->id;
	$this_obj_t = mysql_query("SELECT s1.id, s1.type, s1.content, s1.parent, s1.width, s1.sort_order, s1.published, s1.deleted FROM objects s1 LEFT JOIN objects s2 ON s1.id = s2.id AND s1.time < s2.time WHERE s2.id IS NULL AND s1.id = '".$new_obj_array[$this_obj_num]['id']."'");
	$new_obj_array[$this_obj_num]['type'] = mysql_result($this_obj_t,0,'type');
	$new_obj_array[$this_obj_num]['content'] = mysql_result($this_obj_t,0,'content');	
	$new_obj_array[$this_obj_num]['parent'] = mysql_result($this_obj_t,0,'parent');	
	$new_obj_array[$this_obj_num]['width'] = mysql_result($this_obj_t,0,'width');
	$new_obj_array[$this_obj_num]['sort_order'] = mysql_result($this_obj_t,0,'sort_order');
	$new_obj_array[$this_obj_num]['published'] = mysql_result($this_obj_t,0,'published');
	$new_obj_array[$this_obj_num]['deleted'] = mysql_result($this_obj_t,0,'deleted');

	// replace with new data
	foreach($this_obj as $field=>$new_value) {
		$new_obj_array[$this_obj_num][$field] = $new_value;
		}			

	}

// insert updated objects in db
$insert_updated_object_stmt = $db_connection->prepare("INSERT INTO objects (id, time, type, parent, content, width, sort_order, published, deleted) VALUES (?,?,?,?,?,?,?,?,?)");
foreach($new_obj_array as $obj) {
	$insert_updated_object_stmt->bind_param("idsiidiii", $obj['id'], $time_now, $obj['type'], $obj['parent'], $obj['content'], $obj['width'], $obj['sort_order'], $obj['published'], $obj['deleted']);
	$insert_updated_object_stmt->execute();	
	}
$insert_updated_object_stmt->close();		

// include date in response, to use i.e. when issue is published
$pubdate = $days[strftime("%A", $time_now)].strftime(" %e ", $time_now).$months[strftime("%B", $time_now)].strftime(" %Y", $time_now);

print 'ok|'.$pubdate;
	
?>