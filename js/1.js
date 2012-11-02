// -----------------------------------------------------
// -------- DISABLE LINKS WHEN EDITING OBJ -------------
// -----------------------------------------------------

$('a').click(function(e) {
    if($(this).closest('.base').hasClass('editing')) {
        e.preventDefault();
    } 
});


// -----------------------------------------------------
// ----------------- ADD BASE OBJECT -------------------
// -----------------------------------------------------

// add base object
function add_base() {
	$.ajax({
		url: '../operations/add_object.php?type=base',
		success: function(data) {
			var data_split = data.split('|');
			if(data_split[0] != 'ok') {
				alert('ERROR:' + data);	
				}
			else {
				var header_html = '<div id="base_header' + data_split[1] + '" class="base_header" style="width:' + data_split[3] + 'px">';
				header_html = header_html + '<div id="edit_btn' + data_split[1] + '" class="edit_btn" onclick="activate_tools(' + data_split[1] + ')">EDIT</div>';
				header_html = header_html + '<div id="publish_btn' + data_split[1] + '" class="publish_btn pub_unpub_visible" onclick="pub_unpub(' + data_split[1] + ',1)">PUBLISH</div>';
				header_html = header_html + '<div id="unpublish_btn' + data_split[1] + '" class="unpublish_btn" onclick="pub_unpub(' + data_split[1] + ',0)">UNPUBLISH</div>';
				header_html = header_html + '<a href="' + data_split[4] + '" class="pubdate">' + data_split[5] + '</a></div>';
				$('#obj0').prepend('<ul id="obj' + data_split[1] + '" class="object base" style="width:' + data_split[3] + 'px"><li id="obj' + data_split[2] + '" class="object" sort_order="0"><div class="object_content"></div><div class="object_handle"></div></li></ul>');
				$('#obj' + data_split[1]).before(header_html);
				var t = setTimeout('activate_tools(' + data_split[1] + ')',100);
				}
	
			}
		});	
	}
	
	
// -----------------------------------------------------
// ----------------- PUBLISH/UNPUBLISH -----------------
// -----------------------------------------------------

function pub_unpub(obj_id, val) {

	var obj_array = Array();
	obj_array[0]=new Object();	
	obj_array[0].id = parseInt(obj_id);
	obj_array[0].published = parseInt(val);	
	
	$.post("../operations/update_object.php", { data: $.toJSON(obj_array) }, function(data) { if(data.split('|')[0] != 'ok') { alert('ERROR:' + data);	} else {	
		
		// update date in DOM
		$('#base_header' + obj_id).find('a.pubdate').animate({ opacity: 0 }, 500, function() {
			$(this).html(data.split('|')[1]);
			$(this).animate({ opacity: 1 }, 500);					
			});

		
		// fix button
		if(val == '0') {
			$('#publish_btn' + obj_id).addClass('pub_unpub_visible');
			$('#unpublish_btn' + obj_id).removeClass('pub_unpub_visible');			
			}
		else {
			$('#publish_btn' + obj_id).removeClass('pub_unpub_visible');
			$('#unpublish_btn' + obj_id).addClass('pub_unpub_visible');						
			}
		
		}});
	}


// -----------------------------------------------------
// ----------------- ACTIVATE TOOLS --------------------
// -----------------------------------------------------

function activate_tools(base) {

	if($('#edit_btn' + base).hasClass('btn_active')) {
		deactivate_everything();
		}
	else {
		deactivate_everything();		
		$('#obj' + base).addClass('editing');
		$('.edit_btn').removeClass('btn_active');
		$('#edit_btn' + base).addClass('btn_active');
		$('.base').css('opacity','1');
		$('.base_header').not('#base_header' + base).css('opacity','0.5');
		$('.base').not('#obj' + base).css('opacity','0.5');
		$('body').css('background-color','gray');			
		$('.tools').css('display','inline-block');
		$('.tools').not('input').each(function() {
			var onclick_split = $(this).attr('onclick').split('(');
			$(this).attr('onclick',onclick_split[0] + '(' + base + ')');
			});
		$('#change_base_width_input').val($('#obj' + base).css('width'));
		$('input#change_base_width_input').keypress(function(e) { if(e.which == 13) {
			change_base_width(base);
        	}});
		}
	}

function deactivate_everything() {
	disable_all_tools();
	$('.object').removeClass('editing');
	$('.edit_btn').removeClass('btn_active');
	$('.tools').css('display','none');
	$('.base_header').css('opacity','1');
	$('.base').css('opacity','1');	
	$('body').css('background-color','#e6e6e6');		
	}

// -----------------------------------------------------
// ----------------- DISABLE TOOLS ---------------------
// -----------------------------------------------------

// disable all tools
function disable_all_tools() {
	$('.tools').removeClass('tool_btn_active');
	$('.object_content').removeClass('object_content_active');
	$('.object_handle').removeClass('object_handle_active');
	$('.object_content').unbind();
	$('.object_handle').unbind();					
	$('.splithandle').remove();
	$('div.object_content').children('input.addfromurl').remove();		
	$('ul.object').resizable("destroy");
	$('.object').sortable("destroy");
	$('#change_base_width_input').unbind();
	$('textarea.edit_html').remove();
	$('.save_html_button').remove();
	$('.object_content').css('display','block');	
	}

// -----------------------------------------------------
// ----------------- CHANGE BASE WIDTH -----------------
// -----------------------------------------------------

function change_base_width(base) {

	var new_width = parseInt($('#change_base_width_input').val())/100;

	var obj_array = Array();
	obj_array[0]=new Object();	
	obj_array[0].id = parseInt(base);
	obj_array[0].width = parseInt($('#change_base_width_input').val())/100;	
	
	$.post("../operations/update_object.php", { data: $.toJSON(obj_array) }, function(data) { if(data.split('|')[0] != 'ok') { alert('ERROR:' + data);	} else {	
		
		$('#obj' + base).css('width', (new_width*100) + 'px');
		
		}});
	}


// -----------------------------------------------------
// ----------------- ADD CONTENT FROM URL --------------
// -----------------------------------------------------

function add_content_from_url(base) {
	if($('#add_content_from_url_button').hasClass('tool_btn_active')) {
		disable_all_tools();
		}
	else {		 
		disable_all_tools();
		$('#add_content_from_url_button').addClass('tool_btn_active');		

		var num_empty = 0;
		$('#obj' + base + ' div.object_content').each(function () {
			if($(this).html() == '') {
				num_empty++;
				$(this).append($('<input class="addfromurl" type="text" value="Paste URL here..." />').width($(this).width()-8));
				}
					
			// trigger event when user paste url in input
			$(this).children('input').focus(function () {
				if($(this).val() == 'Paste URL here...') {
					$(this).val('');
					}
				});
			$(this).children('input').blur(function () {
				if($(this).val() == '') {
					$(this).val('Paste URL here...');
					}
				});				
			$(this).children('input').bind('paste', function() {
				get_url_content($(this));
				});
			$(this).children('input').keypress(function(e) {
				if(e.which == 13) {
					get_url_content($(this));
        			}
    			});
							

			});
			
		// if only one input, we focus on that
		if(num_empty == 1) {
			$('#obj' +  base + ' input.addfromurl').focus();
			}

		}	

	}

// get the parsed content from pasted url
function get_url_content(inpt) { setTimeout(function() {
	
	var url = $(inpt).val();
	
	var obj_id = $(inpt).closest('.object').attr('id').substring(3);
	if(/^([a-z]([a-z]|\d|\+|-|\.)*):(\/\/(((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:)*@)?((\[(|(v[\da-f]{1,}\.(([a-z]|\d|-|\.|_|~)|[!\$&'\(\)\*\+,;=]|:)+))\])|((\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5]))|(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=])*)(:\d*)?)(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*|(\/((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)?)|((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)|((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)){0})(\?((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|[\uE000-\uF8FF]|\/|\?)*)?(\#((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|\/|\?)*)?$/i.test(url)) { 
	
		// display loading 
		$(inpt).val('Loading');
		var loadinterval = setInterval("$('#" + $(inpt).closest('.object').attr('id') + "').find('input').val($('#" + $(inpt).closest('.object').attr('id') + "').find('input').val() + '.')",200);

		
		$.ajax({
			url: '../operations/save_html_from_url.php?obj_id=' + obj_id + '&url=' + encodeURIComponent(url),
			success: function(data) {
				clearInterval(loadinterval);
				var data_split = data.split('|');
				if(data_split[0] != 'ok') {
					alert('ERROR:' + data);	
					}
				else {
					$(inpt).closest('.object_content').html(data.substring(3));
					
					// disable links					
					$(inpt).closest('.object_content').find('a').click(function(e) { if($(this).closest('.base').hasClass('editing')) { e.preventDefault(); } });
					}
				}
			});	
		
		
		}
	else {
		alert("Not a valid URL!");
		}
	
	},0);}



// -----------------------------------------------------
// ----------------- REMOVE OBJECT ---------------------
// -----------------------------------------------------

function remove_object(base) {
	if($('#remove_object_button').hasClass('tool_btn_active')) {
		disable_all_tools();
		}
	else {		 
		disable_all_tools();

		$('#remove_object_button').addClass('tool_btn_active');
		
		$('#obj' + base + ' div.object_content').mouseover(function() {
			window.obj_bg_color = $(this).css('background-color');
			$(this).css('background-color','#ffdddd');
			});
		$('#obj' + base + ' div.object_content').mouseout(function() {
			$(this).css('background-color',window.obj_bg_color);
			});	

		$('#obj' + base + ' div.object_content').click(function () {
			
			$(this).css('background-color',window.obj_bg_color);

			var this_obj_id = parseInt($(this).closest('.object').attr('id').substring(3));			

			if($(this).html() == '') {
				// proceed to delete immediatly
				remove_this_object(this_obj_id, base);
				}
			else {
				// if there is content, ask if user really wants to delete
				if(confirm("This object has content, delete it anyway?")) {					
					remove_this_object(this_obj_id, base);
					}
				}


			});
		
		}
	}


// remove function, also cleans up unnecessary parents
function remove_this_object(obj_id, base) {

	obj_array=new Array();
	
	// widths to add
	var parent_width = parseFloat($('#obj' + obj_id).parent().attr('width_percent'));
	var add_to_prev = (Math.floor((parent_width/2)*100))/100;
	var add_to_next = (Math.floor((parent_width - add_to_prev)*100))/100;
	var prev_width = (Math.floor((parseFloat($('#obj' + obj_id).parent().prev('.object').attr('width_percent')))*100))/100;
	var next_width = (Math.floor((parseFloat($('#obj' + obj_id).parent().next('.object').attr('width_percent')))*100))/100;	
	
	// REMOVE OBJECT
	obj_array[0]=new Object();	
	obj_array[0].id = obj_id;
	obj_array[0].deleted = 1;	
	
	// Clean up if object has only one sibling and that sibling has children (and parent is not base)
	if($('#obj' + obj_id).siblings('.object').length == 1 
	   && $('#obj' + obj_id).siblings('.object').children('.object').length > 0
	   && !$('#obj' + obj_id).parent().hasClass('base')) {
		
		// REMOVE SIBLING
		obj_array[1]=new Object();	
		obj_array[1].id = parseInt($('#obj' + obj_id).siblings('.object').attr('id').substring(3));
		obj_array[1].deleted = 1;						
		
		// REMOVE PARENT
		obj_array[2]=new Object();	
		obj_array[2].id = parseInt($('#obj' + obj_id).parent().attr('id').substring(3));
		obj_array[2].deleted = 1;						

		// give parent's width to sibling's children
		// make object's grandparent new parent
		// give them object's parent's sort_order
		var tot_width = 0;
		var obj_num = 3;
		var obj_sort_order = parseInt($('#obj' + obj_id).parent().attr('sort_order'));			
		$('#obj' + obj_id).siblings('.object').children('.object').each(function() {				
			obj_array[obj_num]=new Object();	
			// if this is last child we just give it what width remains, so it all adds up to exactly parent_width
			if($(this).next('.object').length == 0) {
				obj_array[obj_num].width = (parseInt((parent_width-tot_width)*100))/100;
				}
			// otherwise calculate new width
			else {
				obj_array[obj_num].width = (Math.floor((parent_width*(parseFloat($(this).attr('width_percent'))/100))*100))/100;
				tot_width = tot_width + obj_array[obj_num].width;
				}
			
			// SET NEW PARENT
			obj_array[obj_num].id = parseInt($(this).attr('id').substring(3));
			obj_array[obj_num].parent = parseInt($('#obj' + obj_id).parent().parent().attr('id').substring(3));						
			obj_array[obj_num].sort_order = obj_sort_order;
			
			obj_num++;
			obj_sort_order++;
			});
		
		// give parent's siblings on the right side new sort_order, adding to sibling's children's new sort_order
		var start_obj = $('#obj' + obj_id).parent();
		while(start_obj.next('.object').length > 0) {
			start_obj = start_obj.next('.object');
			
			// SET NEW SORT ORDER
			obj_array[obj_num]=new Object();
			obj_array[obj_num].id = parseInt(start_obj.attr('id').substring(3));
			obj_array[obj_num].sort_order = obj_sort_order;
			
			obj_sort_order++;
			obj_num++;
			}

		}

	// Clean up if object is only child
	else if($('#obj' + obj_id).siblings('.object').length == 0) {

		// REMOVE PARENT
		obj_array[1]=new Object();	
		obj_array[1].id = parseInt($('#obj' + obj_id).parent().attr('id').substring(3));
		obj_array[1].deleted = 1;	

		
		// if parent has more than one sibling
		if($('#obj' + obj_id).parent().siblings('.object').length > 1) {														

			// if parent has siblings on both sides, add half parent's width to each of its adjacent siblings
			if($('#obj' + obj_id).parent().prev('.object').length == 1 && $('#obj' + obj_id).parent().next('.object').length == 1) {

				// ADD WIDTH TO PARENT'S ADJACENT SIBLINGS				
				obj_array[2]=new Object();	
				obj_array[2].id = parseInt($('#obj' + obj_id).parent().prev('.object').attr('id').substring(3));
				obj_array[2].width = prev_width + add_to_prev;	
				obj_array[3]=new Object();	
				obj_array[3].id = parseInt($('#obj' + obj_id).parent().next('.object').attr('id').substring(3));
				obj_array[3].width = next_width + add_to_next;	
				}

			// if parent has sibling only on left side, add whole of parent's width to that sibling 
			else if($('#obj' + obj_id).parent().prev('.object').length == 1) {

				// ADD WIDTH TO PARENT'S PREV SIBLING
				obj_array[2]=new Object();	
				obj_array[2].id = parseInt($('#obj' + obj_id).parent().prev('.object').attr('id').substring(3));
				obj_array[2].width = prev_width + add_to_prev + add_to_next;

				}		
			// if parent has sibling only on right side, add whole of parent's width to that sibling 
			else if($('#obj' + obj_id).parent().next('.object').length == 1) {

				// ADD WIDTH TO PARENT\'S NEXT SIBLING
				obj_array[2]=new Object();	
				obj_array[2].id = parseInt($('#obj' + obj_id).parent().next('.object').attr('id').substring(3));
				obj_array[2].width = next_width + add_to_prev + add_to_next;

				}						

			}
		
		// if parent only has one sibling and is not base
		else if(!$('#obj' + obj_id).parent().hasClass('base')) {
			
			console.log('tjo');
			
			// REMOVE GRANDPARENT
			obj_array[2]=new Object();	
			obj_array[2].id = parseInt($('#obj' + obj_id).parent().parent().attr('id').substring(3));
			obj_array[2].deleted = 1;	
			
			// set cousins parent to great grandparent
			// set cousins' sort_order to grandparent's sort_order ++
			var new_sort_order = parseInt($('#obj' + obj_id).parent().parent().attr('sort_order'));
			var obj_num = 3;
			$('#obj' + obj_id).parent().siblings('.object').children('.object').each(function() {

				// SET NEW PARENT
				var great_grandparent = parseInt($('#obj' + obj_id).parent().parent().parent().attr('id').substring(3));
				obj_array[obj_num]=new Object();	
				obj_array[obj_num].id = parseInt($(this).attr('id').substring(3));
				obj_array[obj_num].parent = great_grandparent
				obj_array[obj_num].sort_order = new_sort_order;				
				obj_array[obj_num].width = 100;
				
				new_sort_order++;
				obj_num++;
				});

			// if more than one cousin, and if grandparent has siblings after, ++ on those grandparentsiblings' sort order 
			if ($('#obj' + obj_id).parent().siblings('.object').children('.object').length > 1) {
				
				var start_obj = $('#obj' + obj_id).parent().parent();
				while(start_obj.next('.object').length > 0) {
					start_obj = start_obj.next('.object');
					
					// SET NEW SORT ORDER
					obj_array[obj_num]=new Object();
					obj_array[obj_num].id = parseInt(start_obj.attr('id').substring(3));
					obj_array[obj_num].sort_order = new_sort_order;

					new_sort_order++;
					obj_num++;
					}
										
				}				
			
			}

		}
	
	// save in db and render in DOM
	save_update_remove_and_render_objects(obj_array, base);
	}


// saves changes in db, updates, removes and re-rerenders objects in DOM
function save_update_remove_and_render_objects(obj_array, base) {
	
	console.log(obj_array);
	
	$.post("../operations/update_object.php", { data: $.toJSON(obj_array) }, function(data) { if(data.split('|')[0] != 'ok') { alert('ERROR:' + data);	} else {

		// read all objects that we want to move to new parent, keep them in array
		jQuery.each(obj_array, function (key,obj) {
			if(this.parent > 1) {
				$('#obj' + this.id).css('width','0%');
				obj_array[key].html = $('#obj' + this.id).clone().wrap('<div>').parent().html();
				}
			});

		// now we can safely do the things we want to do in DOM
		jQuery.each(obj_array, function (key,obj) {
			
			// remove
			if(this.deleted==1) {
				
				// deactivate toolbuttons and remove base header if base
				if($('#obj' + this.id).hasClass('base')) {
					deactivate_everything();
					$('#base_header' + this.id).remove();
					}
				
				// remove object
				$('#obj' + this.id).remove();
				}

			// move object to new parent (needs sort order and width)
			else if(this.parent > 0) {				
				// try to find previous sibling (might not exist if first)
				if($('#obj' + this.parent).children('.object[sort_order=' + (this.sort_order-1) + ']').first().length > 0) {
					$('#obj' + this.parent).children('.object[sort_order=' + (this.sort_order-1) + ']').first().after($(this.html).attr('sort_order',this.sort_order).attr('width_percent',this.width).css('width',this.width + '%'));				
					}
				// see if there is a sibling with the same sort_order (in that case, insert before)
				else if($('#obj' + this.parent).children('.object[sort_order=' + this.sort_order + ']').first().length > 0) {
					$('#obj' + this.parent).children('.object[sort_order=' + this.sort_order + ']').first().before($(this.html).attr('sort_order',this.sort_order).attr('width_percent',this.width).css('width',this.width + '%'));				
					}
				// if there is a next sibling to insert before
				else if($('#obj' + this.parent).children('.object[sort_order=' + (this.sort_order+1) + ']').first().length > 0) {
					$('#obj' + this.parent).children('.object[sort_order=' + (this.sort_order+1) + ']').first().before($(this.html).attr('sort_order',this.sort_order).attr('width_percent',this.width).css('width',this.width + '%'));				
					}
				// if no siblings we just insert it in parent
				else {
					$('#obj' + this.parent).append($(this.html).attr('sort_order',this.sort_order).attr('width_percent',this.width).css('width',this.width + '%'));				
					}
				
				// FULHACK, reactivate remove tool so that objects that has been given new parents gets the click event binded
				remove_object(base);
				remove_object(base);
				
				}

			// no new parent, only new sort order
			else if(this.sort_order > 0) {
				$('#obj' + this.id).attr('sort_order',this.sort_order);
				}
			
			// no new parent, only new width
			else if(this.width > 0) {
				$('#obj' + this.id).css('width',this.width + '%');
				$('#obj' + this.id).attr('width_percent',this.width);				
				}
			});		
	

		}});		

	}	


// -----------------------------------------------------
// ----------------- ADD/EDIT HTML ---------------------
// -----------------------------------------------------

function add_edit_html(base) {
	if($('#add_edit_html_button').hasClass('tool_btn_active')) {
		disable_all_tools();
		}
	else {		 
		disable_all_tools();

		$('#add_edit_html_button').addClass('tool_btn_active');
		
		$('#obj' + base + ' div.object_content').mouseover(function() {
			window.obj_bg_color = $(this).css('background-color');
			$(this).css('background-color','#ffdddd');
			});
		$('#obj' + base + ' div.object_content').mouseout(function() {
			$(this).css('background-color',window.obj_bg_color);
			});			

		$('#obj' + base + ' div.object_content').click(function () {
			
			$(this).css('background-color',window.obj_bg_color);

			var this_obj_id = $(this).closest('.object').attr('id').substring(3);			
			$(this).css('display','none');
			$('#obj' + this_obj_id).prepend('<textarea class="edit_html">' + $(this).html() + '</textarea>');				
			$('#obj' + this_obj_id).prepend('<div class="save_html_button" onclick="save_html(' + this_obj_id + ')">Save</div>');
			autoExpandTextarea($('#obj' + this_obj_id).find('textarea'));				

			});
		
		}
	}

// auto expand textarea
function autoExpandTextarea($textarea) {
	var height = $textarea.prop("scrollHeight") + 22;
	$textarea.height(height);
	$textarea.live("keyup", function() {
		var height = $textarea.prop("scrollHeight") + 22;
		$textarea.height(height);
		});	
	}

// save html
function save_html(obj_id) {

	// insert to content table, set content_id in object table
	var html = $('#obj' + obj_id).find('textarea.edit_html').val();
	$.post("../operations/save_html.php", { obj_id: obj_id, html: html },
		function(data) {
			var data_split = data.split('|');
			if(data_split[0] != 'ok') {
				alert('ERROR:' + data);	
				}
			else {
				// update div.object_content, remove textarea and save btn show div.content_content
				$('#obj' + obj_id).find('.object_content').html($('#obj' + obj_id).find('textarea.edit_html').val());								
				$('#obj' + obj_id).find('textarea.edit_html').remove();
				$('#obj' + obj_id).find('.save_html_button').remove();
				$('#obj' + obj_id).find('.object_content').css('display','block');								

				// disable links					
				$('#obj' + obj_id).find('a').click(function(e) { if($(this).closest('.base').hasClass('editing')) { e.preventDefault(); } });				
				}
			});	
	}



// -----------------------------------------------------
// ----------------- SORT OBJECTS ----------------------
// -----------------------------------------------------

function sort_objects_vertically(base) {
	sort_objects(base, 'vertically');
	}
function sort_objects_horizontally(base) {
	sort_objects(base, 'horizontally');
	}	
function sort_objects(base, orientation) {
	if(orientation == 'vertically') {
		var sort_in_parent = 'ul';
		var children_to_sort = 'li';
		}
	else {
		var sort_in_parent = 'li';
		var children_to_sort = 'ul';		
		}
	if($('#sort_objects_' + orientation + '_button').hasClass('tool_btn_active')) {
		disable_all_tools();
		}
	else {		
		disable_all_tools();

		$('#sort_objects_' + orientation + '_button').addClass('tool_btn_active');
		
		// make base sortable if vertically
		if(orientation == 'vertically') {
			var base_sort = '#obj' + base + ', ';
			}
		else {
			var base_sort = '';			
			}
		
		// make sortable
		$(base_sort + '#obj' + base + ' .object_content').mouseover(function() {	$(this).css('cursor','move'); });
		$(base_sort + '#obj' + base + ' .object_content').mouseout(function() { 	$(this).css('cursor','auto'); });			
		$(base_sort + '#obj' + base + ' ' + sort_in_parent + '.object').sortable({
			cursor: 'move',
			start: function (event, ui) {

				// object order before sort
				window.order_before = '';
				ui.helper.parent().children(children_to_sort + '.object').not('.ui-sortable-placeholder').each( function () {
					window.order_before = window.order_before + ',' + $(this).attr('id').substring(3);
					});
				
				// show which siblings can be sorted with pink background
				window.object_content_background_color = ui.helper.parent().find('div.object_content').css('background-color');
				ui.helper.parent().find('div.object_content').css('background-color','pink');
				},
			beforeStop: function (event, ui) {

				// object order after sort!
				window.order_after = '';
				ui.helper.parent().children(children_to_sort + '.object').not('.ui-sortable-placeholder').each( function () {
					window.order_after = window.order_after + ',' + $(this).attr('id').substring(3);
					});

				if(window.order_after == window.order_before) {
					// do nothing
					}
				else {
					// new order, save					
					save_object_order(window.order_after);					
					
					// update attribute so we know what their sort_order is in db
					var attr_sort_order = 0;
					ui.helper.parent().children(children_to_sort + '.object').not('.ui-sortable-placeholder').each( function () {
						$(this).attr('sort_order',attr_sort_order);
						attr_sort_order++;
						});					
					}

				// revert background to original color
				ui.helper.parent().find('div.object_content').css('background-color', window.object_content_background_color);
				},
			items: '> ' + children_to_sort,
			opacity: 0.5,
			});
			
		// destroy sortables with only one child
		$(base_sort + '#obj' + base + ' ' + sort_in_parent + '.object').each(function() {
			if($(this).children('.object').length == 1) {
				$(this).sortable("destroy");
				}
			});
		}
	}
	
	
// save object order
function save_object_order(order) {
	$.ajax({
		url: '../operations/save_order.php?order=' + order,
		success: function(data) {
			var data_split = data.split('|');
			if(data_split[0] != 'ok') {
				alert('ERROR:' + data);	
				}
			else {
				// maybe do something on success?
				}
			}
		});		
	}


// -----------------------------------------------------
// ----------------- CHANGE COLUMN WIDTHS --------------
// -----------------------------------------------------

function change_column_widths(base) {
	if($('#change_column_widths_button').hasClass('tool_btn_active')) {
		disable_all_tools();
		}
	else {		
		disable_all_tools();

		$('#change_column_widths_button').addClass('tool_btn_active');

		$('#obj' + base + ' ul.object').each(function () {
			if($(this).next('ul.object').length>0) {
				var max_width = $(this).width() + $(this).next('ul.object').width() - 20;
				$(this).resizable({
					handles: 'e',					
					maxWidth: max_width,
					start: function(event, ui) {
						ui.helper.start_w = ui.helper.width();
						ui.helper.start_w_percent = ui.helper.attr('width_percent');
						ui.helper.next_start_w = ui.helper.next('ul.object').width();
						ui.helper.next_start_w_percent = ui.helper.next('ul.object').attr('width_percent');						
						},
					resize: function(event, ui) {
						ui.helper.next('ul.object').width(ui.helper.next_start_w + (ui.helper.start_w - ui.helper.width()));
						
						// unset height
						ui.helper.css('height','100%');
						
						// height of handle
						adjust_resize_handles_height();
							
						},
					stop: function(event, ui) {	
						
						// new maxwidths of previous and next sibling, and also all obejcts children and its next sibling's
						ui.helper.prev('ul.object').resizable( "option", "maxWidth", (ui.helper.width() + ui.helper.prev('ul.object').width() - 20));
						ui.helper.find('ul.object').each(function () {
							$(this).resizable( "option", "maxWidth", ($(this).width() + $(this).next().width() - 20));							
							});
						ui.helper.next('ul.object').resizable( "option", "maxWidth", (ui.helper.next('ul.object').next('ul.object').width() + ui.helper.next('ul.object').width() - 20));
						ui.helper.next('ul.object').find('ul.object').each(function () {
							$(this).resizable( "option", "maxWidth", ($(this).width() + $(this).next().width() - 20));
							});
		
						// set widths to percents again
						ui.helper.new_w_percent = Math.floor(10000*ui.helper.width()/ui.helper.parent().parent().width())/100;
						ui.helper.width(ui.helper.new_w_percent + '%');
						ui.helper.attr('width_percent',ui.helper.new_w_percent);						

						ui.helper.next_new_w_percent = ui.helper.next_start_w_percent - (ui.helper.new_w_percent - ui.helper.start_w_percent);
						ui.helper.next('ul.object').width(ui.helper.next_new_w_percent + '%');
						ui.helper.next('ul.object').attr('width_percent',ui.helper.next_new_w_percent);						
						
						// save new widths in db
						ui.helper.obj_id = ui.helper.attr('id').substring(3);
						ui.helper.nexy_obj_id = ui.helper.next('ul.object').attr('id').substring(3);						
						save_widths(ui.helper.obj_id + '>' + ui.helper.new_w_percent + ',' + ui.helper.nexy_obj_id + '>' + ui.helper.next_new_w_percent);				
						
						// unset height
						ui.helper.css('height','100%');						
						}				
					});			
				}
			});
		adjust_resize_handles_height();		
		}
	}
// adjust handle heights to fill height of siblings	
function adjust_resize_handles_height() {
	$('.ui-resizable-e').each(function () {			
		if($(this).parent().height()<$(this).parent().next().height()) {
			$(this).height($(this).parent().next().height()-($(this).outerHeight() - $(this).height()));
			}
		else {
			$(this).height($(this).parent().height()-($(this).outerHeight() - $(this).height()));
			}
		});	
	}
// save widths
function save_widths(widths) {
	$.ajax({
		url: '../operations/save_widths.php?widths=' + widths,
		success: function(data) {
			var data_split = data.split('|');
			if(data_split[0] != 'ok') {
				alert('ERROR:' + data);	
				}
			else {
				// maybe do something when save widths is success?
				}
			}
		});	
	}

		
// -----------------------------------------------------
// ----------------- ADD ROWS/CUT COLUMNS --------------
// -----------------------------------------------------

function add_rows_and_cut_columns(base) {
	if($('#add_rows_and_cut_columns_button').hasClass('tool_btn_active')) {
		disable_all_tools();

		}
	else {
		disable_all_tools();
		
		$('#add_rows_and_cut_columns_button').addClass('tool_btn_active');
		
		// show split handle
		$('#obj' + base + ' .object_content').prepend('<div class="splithandle"></div>');
		$('#obj' + base + ' .splithandle').each(function () {
			$(this).height($(this).parent().height());
			});
		$('#obj' + base + ' .object_content').mousemove(function(e){
			$(this).find('.splithandle').css('margin-left',e.pageX-$(this).offset().left - ($(this).outerWidth()-$(this).width())/2 + 'px');
			});
		$('#obj' + base + ' .object_content').mouseover(function () {	
			$(this).find('.splithandle').css('display','block');
			});			
		$('#obj' + base + ' .object_content').mouseout(function () {
			$(this).find('.splithandle').css('display','none');
			});						
				
		
		// activate add rows
		$('#obj' + base + ' .object_handle').click(function(e) {
			add_row($(this).closest('li.object').attr('id').substring(3), base);
			});	
		$('#obj' + base + ' .object_handle').addClass('object_handle_active');

		// activate add columns
		$('#obj' + base + ' .object_content').click(function(e) {

			var first_col_width = Math.floor(10000*(e.pageX-$(this).offset().left)/$(this).outerWidth())/100;

			cut_column($(this).closest('li.object').attr('id').substring(3), first_col_width, base);
			});	
		$('#obj' + base + ' .object_content').addClass('object_content_active');	
		}
	}	


// add row
function add_row(after, base) {

	// add new object to db and get id
	$.ajax({
		url: '../operations/add_object.php?type=row&after=' + after,
		success: function(data) {
			var data_split = data.split('|');
			if(data_split[0] != 'ok') {
				alert('ERROR:' + data);	
				}
			else {
				
				// new object's sort order
				var new_obj_sort_order = parseInt($('#obj' + after).attr('sort_order')) + 1;
				
				$('#obj' + after).after('<li id="obj' + data_split[1] + '" class="object" sort_order="' + new_obj_sort_order + '"><div class="object_content"></div><div class="object_handle"></div></li>');								
				
				// reset buttons
				add_rows_and_cut_columns(base);
				add_rows_and_cut_columns(base);				
				}	
			}
		});	
	}	

// cut column
function cut_column(object_to_cut, first_col_width, base) {

	// add new object to db and get id
	$.ajax({
		url: '../operations/add_object.php?type=cut&first_col_width=' + first_col_width + '&object_to_cut=' + object_to_cut,
		success: function(data) {
			var data_split = data.split('|');
			if(data_split[0] != 'ok') {
				alert('ERROR:' + data);	
				}
			else {
				
				if(data_split.length == 4) {

					var data_split_widths = data_split[2].split('>');					

					// new siblings sort order
					var new_object_sort_order = parseInt($('#obj' + object_to_cut).parent().attr('sort_order'))+1;

					// parent's siblings to the right need new sort_order
					$('#obj' + object_to_cut).parent().parent().children().each(function () {
						if(parseInt($(this).attr('sort_order')) >= new_object_sort_order) {
							var this_sort_order_plus_one = parseInt($(this).attr('sort_order')) + 1;
							$(this).attr('sort_order', this_sort_order_plus_one);
							}
						});

					// render new sibling
					$('#obj' + object_to_cut).parent().css('width',data_split_widths[0] + '%');
					$('#obj' + object_to_cut).parent().attr('width_percent',data_split_widths[0]);
					$('#obj' + object_to_cut).parent().after('<ul id="obj' + data_split[1] + '" class="object" sort_order="' + new_object_sort_order + '" width_percent="' + data_split_widths[1] + '" style="width:' + data_split_widths[1] + '%"><li id="obj' + data_split[3] + '" class="object" sort_order="0"><div class="object_content"></div><div class="object_handle"></div></li></ul>');
					
					
					}
				else if (data_split.length == 5)	{
						
						var second_col_width = 100 - first_col_width;
						var old_object_to_cut_content = $('#obj' + object_to_cut).html();
						$('#obj' + object_to_cut).attr("id","obj" + data_split[1]);							
						$("#obj" + data_split[1]).html('<ul id="obj' + data_split[2] + '" class="object" sort_order="0" width_percent="' + first_col_width + '" style="width:' + first_col_width + '%"><li id="obj' + object_to_cut + '" class="object" sort_order="0">' + old_object_to_cut_content + '</li></ul>');
						$("#obj" + data_split[1]).append('<ul id="obj' + data_split[3] + '" class="object" sort_order="1" width_percent="' + second_col_width + '" style="width:' + second_col_width + '%"><li id="obj' + data_split[4] + '" class="object" sort_order="0"><div class="object_content"></div><div class="object_handle"></div></li></ul><div class="object_handle"></div>');

					}		
				

				// deactivate and activate to get the new objects editable
				add_rows_and_cut_columns(base);
				add_rows_and_cut_columns(base);

				
				}	
			}
		});	
	}
	

