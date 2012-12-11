// -----------------------------------------------------
// -------------- SHOW AND ACTIVATE NEXT-BUTTON --------
// -----------------------------------------------------


window.onpopstate = function(event) {  if(event && event.state) { location.reload();  }}

// make a pushstate for front pages, so onpopstate fires when user goes back there
if(window.location.href.substring(window.location.href.length-6) == 'admin/' || window.location.href.substring(window.location.href.length-6) == 'front/') {
	window.history.pushState('tjo', '', window.location.href);			
	}

// only on front and admin-pages, i.e. not in permalinks
if(window.location.href.indexOf('front') > 0 || window.location.href.indexOf('admin') > 0) {
	// count base objects, if more than one, make last one into next-button
	if($('.base').length > 1) {
		next_button_from_object($('.base').last().attr('id').substring(3));
		}			
	}

// next button
function next_button_from_object(obj_id) {
	
	var this_obj = '#obj' + obj_id;
	
	// add cover div
	$(this_obj).before('<div class="nav_cover" id="nav_cover' + obj_id + '"><div class="nav_cover_inner"><img src="../img/arrowdown.png" /></div></div>');

	// make cover as wide as base object
	$('#nav_cover' + obj_id).width($(this_obj).width()+50);
	$('#nav_cover' + obj_id).find('.nav_cover_inner').width($(this_obj).width()+50);			

	// minify base object
	$(this_obj).css('height','300px');
	$(this_obj).css('margin-bottom','0px');			
	

	// add hover effect	
	$('#nav_cover' + obj_id).find('.nav_cover_inner').hover(function() {
		$(this).find('img').css('opacity','1');
		$(this).css('background-color','transparent');										
		}, 
	function() {
		$(this).find('img').css('opacity','0.6');
		$(this).css('background-color','#E6E6E6');										
		})								
	
	// on click wi get the revious object and expand the minfied object
	$('#nav_cover' + obj_id).click(function(e) {
	
		// figure out and prepare urls
		var front_or_admin;
		var perma_front_or_admin;
		if(window.location.href.indexOf('admin') > 0) { // for admin page
			var base_url = window.location.href.substring(0,window.location.href.lastIndexOf('/admin'));			
			front_or_admin = 'admin';
			perma_front_or_admin = 'admin/' + obj_id;
			}
		else { // for front page
			var base_url = window.location.href.substring(0, window.location.href.length - 1);
			base_url = base_url.substring(0,base_url.lastIndexOf('/'));
			front_or_admin = 'front';
			perma_front_or_admin = obj_id + '/';
			}
	
		// set url to permalink
		window.history.pushState({ obj: obj_id }, '', base_url + '/' + perma_front_or_admin);

		// remove cover, expand object
		$('#nav_cover' + obj_id).remove();
		$('.base').last().css('height','auto');
		$('.base').last().css('margin-bottom','70px');						
		
		// scroll to objects top
		var scroll_less_if_admin = 0;
		if(window.location.href.indexOf('admin') > 0) { scroll_less_if_admin = 70; }				
		$('html,body').animate({scrollTop: $('.base').last().offset().top - 40 - scroll_less_if_admin},'fast');			
	
		// get previous base object with ajax
		$.ajax({
			url: '../' + front_or_admin + '/next.php?last=' + $('.base').last().attr('id').substring(3),
			success: function(data) {									
				
				// add new previous object to dom
				if(data != 'The end!') {
					$('#obj0').append(data);
					next_button_from_object($('.base').last().attr('id').substring(3));				
					}
				
				// show end div if no previous object
				else {
					$('#obj0').append('<div id="theend">X</div>');
					$('#theend').width($(this_obj).width());					
					}					
				}
			});		
		});
		
	}
