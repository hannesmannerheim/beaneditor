<?php

include "../settings.php";
$db_link=mysql_connect($db_host, $db_user, $db_pass) or die(mysql_error()); mysql_select_db($db_name, $db_link) or die(mysql_error()); mysql_query("SET CHARACTER SET 'utf8'");

// render
function render_bean($admin=false, $rss=false) {

	if(count($_GET) == 1) {
		// we have a permalink		
		foreach($_GET as $perma_id=>$this_var_does_noting_lol) {
			$perma_id = (int)$perma_id;
			}
		}

	// read objects from db 
	$all_t = mysql_query("SELECT s1.id, s1.time, s1.type, s1.parent, s1.content, s1.width, s1.sort_order, s1.deleted, s1.published FROM objects s1 LEFT JOIN objects s2 ON s1.id = s2.id AND s1.time < s2.time WHERE s2.id IS NULL AND s1.deleted = '0' ORDER BY s1.parent");
	$n = mysql_numrows($all_t);
	$i=0;
	while($i<$n) {						
		$obj_id = mysql_result($all_t,$i,'id');
		$objects[$obj_id]["time"] = mysql_result($all_t,$i,'time');
		$objects[$obj_id]["parent"] = mysql_result($all_t,$i,'parent');
		$objects[$obj_id]["type"] = mysql_result($all_t,$i,'type');
		$objects[$obj_id]["content"] = mysql_result($all_t,$i,'content');
		if(mysql_result($all_t,$i,'content')>0) {
			$oid_cid_array[$obj_id] = mysql_result($all_t,$i,'content');
			}
		$objects[$obj_id]["width"] = mysql_result($all_t,$i,'width');
		$objects[$obj_id]["sort_order"] = mysql_result($all_t,$i,'sort_order');
		$objects[$obj_id]["published"] = mysql_result($all_t,$i,'published');				
		$i++;
		}

	// read all needed content from db
	if(count($oid_cid_array)>0) {
		$SQL_IN = '('.implode(",", $oid_cid_array).')';
		$content_t = mysql_query("SELECT id, content FROM content WHERE id IN $SQL_IN");
		$i=0;
		$content_t_row_num = mysql_numrows($content_t);
		$cid_oid_array = array_flip($oid_cid_array);		
		while($i<$content_t_row_num) {
			$objects[$cid_oid_array[mysql_result($content_t,$i,'id')]]["content"] = mysql_result($content_t,$i,'content');
			$i++;
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

		// if we have permalink we remove all bases except perma_id
		if($perma_id>0) {
			foreach($obj_tree as $obj_id=>$obj_data) {
				if($obj_id != $perma_id) {
					unset($obj_tree[$obj_id]);
					}
				}
			}					
		
		// proceed rendering
		if($rss) {
			render_object_rss($obj_tree,$objects);						
			}
		else {
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
	$numsiblings = count($object_tree);
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
				$front_url = substr($_SERVER["REQUEST_URI"],0,(strpos($_SERVER["REQUEST_URI"],'admin')+5)).'/?'.$obj_id;								
				}
			// permalinks for front
			elseif(strpos($_SERVER["REQUEST_URI"],'front')) {
				$front_url = substr($_SERVER["REQUEST_URI"],0,(strpos($_SERVER["REQUEST_URI"],'front'))).$obj_id.'/';								
				}
			// permalinks for permalinks 
			else {
				$front_url = $_SERVER["REQUEST_URI"];				
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
	$numsiblings = count($object_tree);
	foreach($object_tree as $obj_id=>$obj_data) {
			
		// start of item
		if($objects[$obj_id]['parent'] == 0) {
			$titledate = $days[strftime("%A", $objects[$obj_id]["time"])].strftime(" %e ", $objects[$obj_id]["time"]).$months[strftime("%B", $objects[$obj_id]["time"])].strftime(" %Y", $objects[$obj_id]["time"]);
			print '<item><title>'.$name.': '.$titledate.'</title><description><![CDATA[';
			}			
					
		// print all objects with content in rows
		$numchildren = count($obj_data['children']);
		if($numchildren>0) {
			render_object_rss($obj_data['children'],$objects);	
			}
		else {
			print '<div style="width:500px;">';
			if($objects[$obj_id]['content'] != '0') {
				
				// fix relative links
				$img_base_url = "http://".$_SERVER['SERVER_NAME'].substr($_SERVER["REQUEST_URI"],0,(strpos($_SERVER["REQUEST_URI"],'rss')));											
				$objects[$obj_id]['content'] = str_replace('="../','="'.$img_base_url,$objects[$obj_id]['content']);
				
				// print
				print $objects[$obj_id]['content'];
				}
			print '</div>';
			}
		
		// end of item
		if($objects[$obj_id]['parent'] == 0) {
			$pubdate = date('r', $objects[$obj_id]["time"]);
			$item_url = "http://".$_SERVER['SERVER_NAME'].substr($_SERVER["REQUEST_URI"],0,(strpos($_SERVER["REQUEST_URI"],'rss'))).$obj_id.'/';											
			print ']]></description><link>'.$item_url.'</link><guid>'.$item_url.'</guid><pubDate>'.$pubdate.'</pubDate></item>'."\n	";
			}	

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
	$t = mysql_query("SELECT time FROM objects ORDER BY time DESC LIMIT 1");
	return mysql_result($t,0);
	}


?>