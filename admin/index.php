<?php

include "../settings.php";
include "../inc/inc.php";

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN"
"http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>  
		<title><?php print $name; ?></title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<link rel="stylesheet" type="text/css" href="../css/jquery-ui-1.8.16.custom.css" />		
		<link rel="stylesheet" type="text/css" href="../css/1.css" />
		<link rel="stylesheet" type="text/css" href="../css/admin-1.css" />		
		<link rel="shortcut icon" href="../favicon.ico" />
	</head>
	<body>
		<div id="header">
			<input type="text" class="tools" id="change_base_width_input" />
			<img src="../img/btns/add_obj.png" class="tools" id="add_rows_and_cut_columns_button" onclick="add_rows_and_cut_columns()" />
			<img src="../img/btns/change_width.png" class="tools" id="change_column_widths_button" onclick="change_column_widths()" />
			<img src="../img/btns/move_h.png" class="tools" id="sort_objects_horizontally_button" onclick="sort_objects_horizontally()" />
			<img src="../img/btns/move_v.png" class="tools" id="sort_objects_vertically_button" onclick="sort_objects_vertically()" />
			<img src="../img/btns/delete.png" class="tools" id="remove_object_button" onclick="remove_object()" />
			<img src="../img/btns/html.png" class="tools" id="add_edit_html_button" onclick="add_edit_html()" />
			<img src="../img/btns/url.png" class="tools" id="add_content_from_url_button" onclick="add_content_from_url()" />																		
			<img src="../img/beaneditor.png" id="beaneditor" />
			<img src="../img/new.png" id="add_base_btn" onclick="add_base()" />
		</div>
		<ul id="objects_container"><li id="obj0"><?php render_bean(true) ?></li></ul>	
	    <script type="text/javascript" src="../js/jquery-1.6.3.min.js"></script>
	    <script type="text/javascript" src="../js/jquery-ui-1.8.16.custom.min.js"></script>    
	    <script type="text/javascript" src="../js/jquery.json-2.3.min.js"></script>    	    
	    <script type="text/javascript" src="../js/1.js"></script>
	</body>
</html>