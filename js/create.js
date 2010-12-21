jQuery(document).ready(function($) {
	
	// Keep track of how many poll options we have
	var option_count = 0;
	
	// Add an option
	function add_option() {
		var type = $('#new_option_type').val();
		var title = $('#new_option_title').val();
		
		// Make sure user entered some text
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
			
			// Advance out option counter by +1
			option_count++;
			
			// Clear new option text input
			$('#new_option_title').val('');
		}
	}
	
	// 
	$('#add_new_option').click(function() {
		add_option();
	});
	
	$('#new_option_title').keyup(function(e) {
		if (e.which == 13) {
			add_option();
		}
	});
	
});