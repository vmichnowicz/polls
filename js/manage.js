jQuery(document).ready(function($) {
	
	var option_focus = false;
	
	$('#new_option_title').bind({
		focusin: function() {
			option_focus = true;
			console.log('has focus...');
		},
		focusout: function() {
			option_focus = false;
			console.log('does not have focus...');
		}
	});
	
	$('#new_option_title').keyup(function(e) {
		if (e.which == 13) {
			add_option();
		}
	});
	
	$('form').submit(function(e) {
		if (option_focus) {
			e.preventDefault();
		}
	});
	
	$('#add_new_option').live('click', function() {
		var options = $('#section_options');
		var poll_id = $('#poll_id').val();
		var type = $('#new_option_type').val();
		var title = $('#new_option_title').val();
		
		// Make sure user entered a title
		if (title)
		{
			$.post(BASE_URL + 'admin/polls/ajax_add_option', {
					'poll_id': $('#poll_id').val(),
					'new_option_type': $('#new_option_type').val(),
					'new_option_title': $('#new_option_title').val()
				}, function(data) {
				
					$(options).load(BASE_URL + 'admin/polls/manage/' + poll_id + ' #section_options');
				
			});
		}
		
	});
	
	function add_option() {
		var type = $('#new_option_type').val();
		var title = $('#new_option_title').val();
		
		if (title) {
		
			var type_input = $('#new_option_type').clone();
			var title_input = $('#new_option_title').clone();
			
			$(type_input).val(type);
			$(title_input).val(title);
			
			$(type_input).attr('name', 'options[' + option_count + '][type]');
			$(title_input).attr('name', 'options[' + option_count + '][title]');
			
			$(type_input).attr('id', '');
			$(title_input).attr('id', '');
			
			var el = $('<li />');
			
			$(el).append(type_input)
			$(el).append(title_input);
			
			$('#options').append(el)
			$(el).hide().slideDown('slow');
			
			
			option_count++;
			
			$('#new_option_title').val('');
		}
	}
	
	var option_count = 0;
	
	$('#add_a_new_option').click(function() {
		add_option();
	});
	
	// Datepicker
	$("#open_date, #close_date").datepicker({ dateFormat: 'yy/m/d' });
	
});