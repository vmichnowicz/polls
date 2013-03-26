jQuery(document).ready(function($) {

	/**
	 * Automate URL slug
	 *
	 * @source http://stackoverflow.com/questions/1053902/how-to-convert-a-title-to-a-url-slug-in-jquery/1054592#1054592
	 */
	$('#title').on('keyup change', function() {
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

    /**
     * Function that gets run when the sort order changes
     *
     * @url https://github.com/vmichnowicz/polls/issues/7
     * @todo Seems like this sort thing got messed up in a newer vesion of PyroCMS, this needs to be revisited
     */
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
		handle: '.move-handle',
		update: function() {
			sort();
		}
	});

	// Add a poll option
	var add_option = function() {
		var options = $('#section_options');

        var poll_id = $('#poll_id').val();
		var type = $('#new_option_type');
		var title = $('#new_option_title');

		// Make sure user entered a title
		if ( $.trim( title.val() ) !== '' ) {
            /**
             * Create random option key. Make sure it contains a letter.
             * @url http://stackoverflow.com/questions/1349404/generate-a-string-of-5-random-characters-in-javascript
             */
            var random = 'l' + Math.random().toString(36).substr(7);

            var li = $('<li />');
            var selectInput = $('<select name="options[' + random + '][type]"></select>');
            var defined = $('<option value="defined" value="Defined">').text('Other');
            var other = $('<option value="defined" value="Other">').text('Defined');
            var titleInput = $('<input type="text" name="options[' + random + '][title]"></select>').val( title.val() );
            var removeOption = $('<input type="button" class="remove_option" value="Remove Option" />');

            selectInput.append(defined, other).val( type.val() );
            li.append(selectInput, document.createTextNode(' '), titleInput, document.createTextNode(' '), removeOption);

            $('#options').append(li);
            title.val('');
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

    /**
     * Remove a poll option
     *
     * Show confirmation message, remove option text, then hide list element. When we process the form after form
     * submission we will notice the empty option text and remove that option from the database.
     */
    $('input[type="button"].remove_option').live('click', function(e) {
        e.preventDefault();
        if ( confirm('Are you sure you want to remove this poll option?') ) {
            var li = $(this).closest('li');
            li.find('input[type="text"]').val('').end().slideUp('fast');
        }
    });
});