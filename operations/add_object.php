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

// get latest id
$latest_id_t = mysql_query("SELECT id FROM objects ORDER BY id DESC LIMIT 1");
if(mysql_numrows($latest_id_t) == 0) { $latest_id = 0; }
else { $latest_id = mysql_result($latest_id_t, 0); }
	
// prepare vars
$new_obj_id1 = $latest_id+1;
$new_obj_id2 = $latest_id+2;
$new_obj_id3 = $latest_id+3;
$new_obj_id4 = $latest_id+4;
$time_now = microtime(true);
if(isset($_GET['width'])) {
	$obj_width = (float)$_GET['width'];		
	} 

// insert new base object
if($_GET['type'] == 'base') {

	$pxwidth = $container_width/100;
	$front_url = substr($_SERVER["REQUEST_URI"],0,strpos($_SERVER["REQUEST_URI"],'operations')).'admin/?'.$new_obj_id1;	
	$pubdate = $days[strftime("%A", $time_now)].strftime(" %e ", $time_now).$months[strftime("%B", $time_now)].strftime(" %Y", $time_now);	

	mysql_query("INSERT INTO objects (id, time, type, width) VALUES ('$new_obj_id1','$time_now','ul',$pxwidth)");
	mysql_query("INSERT INTO objects (id, time, type, parent) VALUES ('$new_obj_id2','$time_now','li','$new_obj_id1')");	
	print 'ok|'.$new_obj_id1.'|'.$new_obj_id2.'|'.$container_width.'|'.$front_url.'|'.$pubdate;
	}
	
// insert new row
elseif($_GET['type'] == 'row') {

	$after = (int)$_GET["after"];

	// get the order number of the 'after'-sibling 
	$after_siblings_order_number_t = mysql_query("SELECT s1.id, s1.time, s1.sort_order, s1.parent FROM objects s1 LEFT JOIN objects s2 ON s1.id = s2.id AND s1.time < s2.time WHERE s2.id IS NULL AND s1.deleted = '0' AND s1.id = '$after'");		
	$after_siblings_order_number = mysql_result($after_siblings_order_number_t,0,'sort_order');
	$after_siblings_parent = mysql_result($after_siblings_order_number_t,0,'parent');		
	$new_childs_order_number = $after_siblings_order_number + 1;

	// move siblings with higher order numbers one place forward
	$siblings_to_move_t = mysql_query("SELECT s1.id, s1.time, s1.content, s1.sort_order FROM objects s1 LEFT JOIN objects s2 ON s1.id = s2.id AND s1.time < s2.time WHERE s2.id IS NULL AND s1.deleted = '0' AND s1.parent = '$after_siblings_parent' AND s1.sort_order > '$after_siblings_order_number'");
	$num_siblings_to_move = mysql_numrows($siblings_to_move_t);
	$i=0;
	while($i<$num_siblings_to_move) {
		$sibling_to_move_id = mysql_result($siblings_to_move_t,$i,'id');
		$sibling_to_move_content = mysql_result($siblings_to_move_t,$i,'content');
		$sibling_to_move_new_sort_order = mysql_result($siblings_to_move_t,$i,'sort_order') + 1;		
		mysql_query("INSERT INTO objects (id, time, type, parent, content, sort_order) VALUES ('$sibling_to_move_id','$time_now','li','$after_siblings_parent','$sibling_to_move_content','$sibling_to_move_new_sort_order')");				
		$i++;
		}

	// insert new child
	mysql_query("INSERT INTO objects (id, time, type, parent, sort_order) VALUES ('$new_obj_id1','$time_now','li','$after_siblings_parent','$new_childs_order_number')");

	print 'ok|'.$new_obj_id1;
	}

// cut column
elseif($_GET['type'] == 'cut') {

	$object_to_cut = (int)$_GET['object_to_cut'];
	$first_col_width = (float)$_GET['first_col_width'];	
	
	
	// either we (1) just insert new column (<ul><li></li></ul>) after object_to_cut, and change object_to_cut to half it's width
	// OR (2) we want to insert two new columns object_to_cut and move its contents to one of them
	// the question we ask is: "Do object_to_cut have any siblings?" If, yes -> 2, if no -> 1 (although if object_to_cut's parent is base, we go with 2)
	
	// does object_to_cut have siblings?
	$object_to_cut_t = mysql_query("SELECT s1.id, s1.type, s1.parent, s1.content, s1.sort_order FROM objects s1 LEFT JOIN objects s2 ON s1.id = s2.id AND s1.time < s2.time WHERE s2.id IS NULL AND s1.id = '$object_to_cut'");
	$object_to_cut_parent = mysql_result($object_to_cut_t,0,'parent');
	$object_to_cut_content = mysql_result($object_to_cut_t,0,'content');
	$object_to_cut_sort_order = mysql_result($object_to_cut_t,0,'sort_order');			
	$object_to_cut_siblings_t = mysql_query("SELECT s1.id, s1.time, s1.parent, s1.deleted FROM objects s1 LEFT JOIN objects s2 ON s1.id = s2.id AND s1.time < s2.time WHERE s2.id IS NULL AND s1.deleted = '0' AND s1.parent = '$object_to_cut_parent'");
	$num_object_to_cut_siblings = mysql_numrows($object_to_cut_siblings_t);			

	// get grandparent
	$parent_t = mysql_query("SELECT s1.id, s1.time, s1.parent, s1.content, s1.width, s1.sort_order, s1.deleted FROM objects s1 LEFT JOIN objects s2 ON s1.id = s2.id AND s1.time < s2.time WHERE s2.id IS NULL AND s1.id = '$object_to_cut_parent'");
	$parent = mysql_result($parent_t,0,'id');
	$grandparent = mysql_result($parent_t,0,'parent');
	$parent_content = mysql_result($parent_t,0,'content');
	$parent_width = mysql_result($parent_t,0,'width');
	$parent_sort_order = mysql_result($parent_t,0,'sort_order');	;
	
	// (1) add column after object_to_split
	if($grandparent != 0 && $num_object_to_cut_siblings==1) {

		// get the order number of object_to_splits parent and calculate new widths
		$new_column_order_number = $parent_sort_order + 1;
		$new_width1 = (floor($parent_width*($first_col_width/100)*100))/100;
		$new_width2 = $parent_width - $new_width1;		

		// move columns with higher order numbers one place forward
		$columns_to_move_t = mysql_query("SELECT s1.id, s1.time, s1.content, s1.width, s1.sort_order FROM objects s1 LEFT JOIN objects s2 ON s1.id = s2.id AND s1.time < s2.time WHERE s2.id IS NULL AND s1.deleted = '0' AND s1.parent = '$grandparent' AND s1.sort_order > '$parent_sort_order'");
		$num_columns_to_move = mysql_numrows($columns_to_move_t);
		$i=0;
		while($i<$num_columns_to_move) {
			$column_to_move_id = mysql_result($columns_to_move_t,$i,'id');
			$column_to_move_content = mysql_result($columns_to_move_t,$i,'content');
			$column_to_move_width = mysql_result($columns_to_move_t,$i,'width');			
			$column_to_move_new_sort_order = mysql_result($columns_to_move_t,$i,'sort_order') + 1;		
			mysql_query("INSERT INTO objects (id, time, type, parent, content, width, sort_order) VALUES ('$column_to_move_id','$time_now','ul','$grandparent','$column_to_move_content','$column_to_move_width','$column_to_move_new_sort_order')");				
			$i++;
			}
		
		// change width of object_to_split's parent
		mysql_query("INSERT INTO objects (id, time, type, parent, content, width, sort_order) VALUES ('$parent','$time_now','ul','$grandparent','$parent_content','$new_width1','$parent_sort_order')");						

		// insert new column
		mysql_query("INSERT INTO objects (id, time, type, parent, width, sort_order) VALUES ('$new_obj_id1','$time_now','ul','$grandparent','$new_width2','$new_column_order_number')");
		mysql_query("INSERT INTO objects (id, time, type, parent) VALUES ('$new_obj_id2','$time_now','li','$new_obj_id1')");			
		print 'ok|'.$new_obj_id1.'|'.$new_width1.'>'.$new_width2.'|'.$new_obj_id2;		
		}

	// (2) insert two new columns in new object, move object_to_split into one of the new columns
	else {

		$second_col_width = 100 - $first_col_width;
		
		// new li in place of object_to_split
		mysql_query("INSERT INTO objects (id, time, type, parent, sort_order) VALUES ('$new_obj_id1','$time_now','li','$object_to_cut_parent','$object_to_cut_sort_order')");

		// new columns in new li
		mysql_query("INSERT INTO objects (id, time, type, parent, width, sort_order) VALUES ('$new_obj_id2','$time_now','ul','$new_obj_id1','$first_col_width','0')");
		mysql_query("INSERT INTO objects (id, time, type, parent, width, sort_order) VALUES ('$new_obj_id3','$time_now','ul','$new_obj_id1','$second_col_width','1')");

		// old object_to_split moved into new column 1
		mysql_query("INSERT INTO objects (id, time, type, parent, content) VALUES ('$object_to_cut','$time_now','li','$new_obj_id2','$object_to_cut_content')");

		// new li in new column 2
		mysql_query("INSERT INTO objects (id, time, type, parent) VALUES ('$new_obj_id4','$time_now','li','$new_obj_id3')");			
		print 'ok|'.$new_obj_id1.'|'.$new_obj_id2.'|'.$new_obj_id3.'|'.$new_obj_id4;
		}	
	
	}
		
?>