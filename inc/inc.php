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


// render
function render_bean($admin=false, $rss=false, $requested_base_object=false, $next=false) {

	include "../settings.php";
	$db_connection = new mysqli("$db_host", "$db_user", "$db_pass", "$db_name");
	$set_utf8_stmt = $db_connection->prepare("SET CHARACTER SET 'utf8'");
	$set_utf8_stmt->execute();	

	// read objects from db 
	$all_t = $db_connection->query("SELECT s1.id, s1.time, s1.type, s1.parent, s1.content, s1.width, s1.sort_order, s1.deleted, s1.published FROM objects s1 LEFT JOIN objects s2 ON s1.id = s2.id AND s1.time < s2.time WHERE s2.id IS NULL AND s1.deleted = '0' ORDER BY s1.parent");
	while ($row = mysqli_fetch_assoc($all_t)) {
		$obj_id = $row['id'];
		$objects[$obj_id]["time"] = $row['time'];
		$objects[$obj_id]["parent"] = $row['parent'];
		$objects[$obj_id]["type"] = $row['type'];
		$objects[$obj_id]["content"] = $row['content'];
		if($row['content']>0) {
			$oid_cid_array[$obj_id] = $row['content'];
			}
		$objects[$obj_id]["width"] = $row['width'];
		$objects[$obj_id]["sort_order"] = $row['sort_order'];
		$objects[$obj_id]["published"] = $row['published'];				
		}

	// read all needed content from db
	if(count($oid_cid_array)>0) {
		$SQL_IN = '('.implode(",", $oid_cid_array).')';
		$content_t = $db_connection->query("SELECT id, content FROM content WHERE id IN $SQL_IN");
		$cid_oid_array = array_flip($oid_cid_array);
		while ($row = mysqli_fetch_assoc($content_t)) {
			$objects[$cid_oid_array[$row['id']]]['content'] = $row['content'];
			}
		}			
		
		
	// generate object_tree
	if(count($objects)) {
		$obj_tree = generate_tree(0,$objects);
		krsort($obj_tree);
		
		// if this is public site we remove all unpublished issues
		if($admin === false) {
			foreach($obj_tree as $obj_id=>$obj_data) {
				if($objects[$obj_id]['published'] == 0) {
					unset($obj_tree[$obj_id]);
					}
				}
			}	

		// make array with only base objects
		foreach($obj_tree as $base_id=>$base_object) {
			$base_object_array[] = $base_id;
			}
		
		// render directly if rss
		if($rss) {
			render_object_rss($obj_tree,$objects);						
			}		
		// display message if no base objects
		elseif(count($base_object_array)<1) {
			print '<div id="theend" style="font-size:30px;"><img style="width:216px;" src="../img/beaneditor.png" /><br />Nothing published here yet.<br />If you are the administrator of this Beaneditor site, <br />go to <a href="'.$home_url.'/admin/">'.$home_url.'/admin/</a> to start editing.</div>';
			}
		// continue otherwise
		else {	
			// show latest two base objects if front or if issue not found
			if($requested_base_object == 'latest' || !isset($obj_tree[$requested_base_object])) {
				$base_object_array = array_slice($base_object_array,0,2);
				}
			// requests to load next base object
			elseif($next) {
				$requested_base_object_position = array_search($requested_base_object, $base_object_array);
				$base_object_array = array_slice($base_object_array,$requested_base_object_position+1,1);
				if(count($base_object_array)<1) {
					print 'The end!';
					return;
					}
				}
			// permalink, only one
			else {
				$requested_base_object_position = array_search($requested_base_object, $base_object_array);
				$base_object_array = array_slice($base_object_array,$requested_base_object_position,1);
				}
				
	
			// keep only the needed base object/-s
			foreach($base_object_array as $base_object) {
				$needed_base_objects_tree[$base_object] = $obj_tree[$base_object];
				}
			$obj_tree = $needed_base_objects_tree;			
	
			
			// render
			render_object($obj_tree,$objects,$admin);						
			}
		}	
	}
	

// generate object_tree
function generate_tree($start_obj_id,$objects) {
	$obj_tree = array();
	foreach($objects as $obj_id=>$obj_data) {
		if($start_obj_id == $obj_data['parent']) {
			$obj_tree[$obj_id]['sort_order'] = $obj_data['sort_order'];
			$obj_tree[$obj_id]['children'] = generate_tree($obj_id,$objects);
			}
		}
	uasort($obj_tree, 'generate_tree_cmp');
	return $obj_tree;
	}
function generate_tree_cmp($a, $b) {
    if ($a == $b) { return 0; }
    return ($a < $b) ? -1 : 1;
	}
	
	


// render objects
function render_object($object_tree,$objects,$admin=false) {
	include "../settings.php";	
	
	foreach($object_tree as $obj_id=>$obj_data) {
		
		if($objects[$obj_id]['type'] == 'ul') {
			$width_html = ' width_percent="'.$objects[$obj_id]['width'].'" style="width:'.$objects[$obj_id]['width'].'%;"';				
			}				
			
		// edit & publish/unpublish buttons
		$header_html = '';
		if($objects[$obj_id]['parent'] == 0 && $admin === true) {

			$header_html .= '<div id="edit_btn'.$obj_id.'" class="edit_btn" onclick="activate_tools('.$obj_id.')">EDIT</div>';
	
			$p_visible = ''; $up_visible = '';
			if($objects[$obj_id]['published'] == 0) {
				$p_visible = 'pub_unpub_visible';
				}
			else {
				$up_visible = 'pub_unpub_visible';				
				}				
			$header_html .= '<div id="publish_btn'.$obj_id.'" class="publish_btn '.$p_visible.'" onclick="pub_unpub('.$obj_id.',1)">PUBLISH</div>';
			$header_html .= '<div id="unpublish_btn'.$obj_id.'" class="unpublish_btn '.$up_visible.'" onclick="pub_unpub('.$obj_id.',0)">UNPUBLISH</div>';
			}
			
		// publish date and permalink
		if($objects[$obj_id]['parent'] == 0) {
			$width_html = ' style="width:'.($objects[$obj_id]['width']*100).'px;"';		
			$base_class = ' base';
		
			$pubdate = $days[strftime("%A", $objects[$obj_id]["time"])].strftime(" %e ", $objects[$obj_id]["time"]).$months[strftime("%B", $objects[$obj_id]["time"])].strftime(" %Y", $objects[$obj_id]["time"]);

			// permalinks for admin
			if(strpos($_SERVER["REQUEST_URI"],'admin')) {
				$front_url = $home_url.'/admin/'.$obj_id;								
				}
			// public permalinks
			else {
				$front_url = $home_url.'/'.$obj_id.'/';
				}
			$header_html = '<div id="base_header'.$obj_id.'" class="base_header"'.$width_html.'>'.$header_html.'<a href="'.$front_url.'" class="pubdate">'.$pubdate.'</a></div>';
			}			
					
		print $header_html.'<'.$objects[$obj_id]['type'].' id="obj'.$obj_id.'" class="object'.$base_class.'" sort_order="'.$objects[$obj_id]['sort_order'].'"'.$width_html.'>';
		$numchildren = count($obj_data['children']);
		if($numchildren>0) {
			render_object($obj_data['children'],$objects,$admin);	
			}
		else {
			print '<div class="object_content">';
			if($objects[$obj_id]['content'] != '0') {
				print $objects[$obj_id]['content'];
				}
			print '</div>';
			}
		if($objects[$obj_id]['type'] == 'li') {
			print '<div class="object_handle"></div>';			
			}
		print '</'.$objects[$obj_id]['type'].'>'.$base_buttons_html	;
		unset($width_html, $base_class, $base_buttons_html);		
		}
	}

// render RSS
function render_object_rss($object_tree,$objects) {
	include "../settings.php";	
	$i=0;
	$maxnum=50;
	foreach($object_tree as $obj_id=>$obj_data) {
			
		// start of item
		if($objects[$obj_id]['parent'] == 0) {
			$titledate = $days[strftime("%A", $objects[$obj_id]["time"])].strftime(" %e ", $objects[$obj_id]["time"]).$months[strftime("%B", $objects[$obj_id]["time"])].strftime(" %Y", $objects[$obj_id]["time"]);
			$item_url = "http://".$_SERVER['SERVER_NAME'].substr($_SERVER["REQUEST_URI"],0,(strpos($_SERVER["REQUEST_URI"],'rss'))).$obj_id.'/';											
			$pubdate = date('r', $objects[$obj_id]["time"]);			
			print '<item><title>'.$name.': '.$titledate.'</title><description><![CDATA[';
			print 'New issue from '.$name.': <a href="'.$item_url.'">Click here to read it</a>';
			print ']]></description><link>'.$item_url.'</link><guid>'.$item_url.'</guid><pubDate>'.$pubdate.'</pubDate></item>'."\n	";
			$i++;
			}
	
		if($i>=$maxnum) break;
		}
	}

// get stuff with curl 
function get_source_width_curl($url) {
	$header = array();
	$header[] = 'Accept: text/xml,application/xml,application/xhtml+xml,text/html;q=0.9,text/plain;q=0.8,image/png,image/gif,image/jpeg,*/*;q=0.5';
	$header[] = 'Cache-Control: max-age=0';
	$header[] = 'Connection: keep-alive';
	$header[] = 'Keep-Alive: 300';
	$header[] = 'Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7';
	$header[] = 'Accept-Language: en-us,en;q=0.5';
	$header[] = 'Pragma: ';
	
	// some illegal character fix
	$url = str_replace('“','%e2%80%9c',$url);
	$url = str_replace('”','%e2%80%9d',$url);
	$url = str_replace('–','%e2%80%93',$url);
	
	$ch = curl_init(); 
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1); 	
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 6.0; en-US; rv:1.9.0.11) Gecko/2009060215 Firefox/3.0.11 (.NET CLR 3.5.30729)');
	curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
	curl_setopt($ch, CURLOPT_AUTOREFERER, true); 
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	curl_setopt($ch, CURLOPT_ENCODING, '');
	curl_setopt($ch, CURLOPT_TIMEOUT, 20);
	$result["content"] = curl_exec($ch);
	if(!$result) {
		echo "cURL error number:" .curl_errno($ch)."\n";	
		echo "cURL error:" . curl_error($ch)."\n";
		}
	$result["type"] = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);	
	curl_close ($ch);
	
	return $result;
	}

// last change in db
function last_change_timestamp() {
	include "../settings.php";
	$db_connection = new mysqli("$db_host", "$db_user", "$db_pass", "$db_name");
	$set_utf8_stmt = $db_connection->prepare("SET CHARACTER SET 'utf8'");
	$set_utf8_stmt->execute();	
	$t = $db_connection->query("SELECT time FROM objects ORDER BY time DESC LIMIT 1");
	return $t->fetch_assoc()['time'];
	}
	
	


?>