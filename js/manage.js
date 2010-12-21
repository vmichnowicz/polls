jQuery(document).ready(function($) {
	
	// Function that gets run when the sort order changes
	function sort() {
		var poll_id = $('#poll_id').val();
		var options = $('input[name^="options"]');
		var obj = new Object();
		
		// Loop through all our poll optoins
		$(options).each(function(i) {
			var id = $(options).eq(i).attr('name');
			id = id.replace('options[', '');
			id = id.replace('][title]', '');
			
			// Add sort order to our object "obj"
			obj[id] = i;
			
		});
		
		// POST our sort order
		$.post(BASE_URL + 'admin/polls/ajax_update_order/' + poll_id, obj, function(data) {
			// It worked!!! (probably)
		});
	}
	
	// Sort options on page load along the "y" axis
	$('#options').sortable({
		axis: 'y',
		update: function() {
			sort();
		}
	});
	
	// Add a poll option
	function add_option() {
		var options = $('#section_options');
		var poll_id = $('#poll_id').val();
		var type = $('#new_option_type').val();
		var title = $('#new_option_title').val();
		
		// Make sure user entered a title
		if (title) {
			$.post(BASE_URL + 'admin/polls/ajax_add_option', {
					'poll_id': $('#poll_id').val(),
					'new_option_type': $('#new_option_type').val(),
					'new_option_title': $('#new_option_title').val()
				}, function(data) {
					
					// Load in new poll options and re-run sorting function on new data
					$(options).load(BASE_URL + 'admin/polls/manage/' + poll_id + ' #section_options', function() {
						$('#options').sortable({
							axis: 'y',
							update: function() {
								sort();
							}
						});
					});
				
			});
		}
		else {
			alert('Please enter a poll option title.');
		}
	}
	
	// If "Add Option" button is clicked
	$('#add_new_option').live('click', function() {
		add_option();
	});
	
	// If user presses the "enter" key (use "live" because of AJAX DOM replacement)
	$('#new_option_title').live('keyup', function(e) {
		if (e.which == 13) {
			add_option();
		}
	});
	
});