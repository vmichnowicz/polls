jQuery(document).ready(function($) {

	/**
	 * Automate URL slug
	 *
	 * @source http://stackoverflow.com/questions/1053902/how-to-convert-a-title-to-a-url-slug-in-jquery/1054592#1054592
	 */
	$('#title').keyup(function(){
		var text = $(this).val();
		text = text.toLowerCase();
		text = text.replace(/[^a-zA-Z0-9]+/g,'-');
		$("#slug").val(text);
	});
	
	// Add new option input focus set to "false" by default
	var option_focus = false;
	
	// When new option text input gets and loses focus
	$('#new_option_title').live('focusin focusout', function(e) {
		if (e.type == 'focusin') {
			option_focus = true;
		}
		else {
			option_focus = false;
		}
	});
	
	// When our form is subitted
	$('form').submit(function(e) {
		// If our new option text input has focus
		if (option_focus) {
			e.preventDefault();
		}
	});
	
	// Datepicker
	$("#open_date, #close_date").datepicker({ dateFormat: 'yy-mm-dd' });

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
		$.post(SITE_URL + 'admin/polls/ajax_update_order/' + poll_id, obj, function(data) {
			// It worked!!! (probably)
		});
	}

	// Sort options on page load along the "y" axis
	$('#options').sortable({
		axis: 'y',
		handle: '.move-handle',
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
			$.post(SITE_URL + 'admin/polls/ajax_add_option', {
					'poll_id': $('#poll_id').val(),
					'new_option_type': $('#new_option_type').val(),
					'new_option_title': $('#new_option_title').val()
				}, function(data) {

					// Load in new poll options and re-run sorting function on new data
					$(options).load(SITE_URL + 'admin/polls/update/' + poll_id + ' #section_options', function() {
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