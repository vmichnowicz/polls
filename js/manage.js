jQuery(document).ready(function($) {
	
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
	
	var option_count = 0;
	
	$('#add_a_new_option').click(function() {
		
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
	});
	
	/*
	$('input[name="options[]"]').live('focusin focusout', function(e) {
		var val = $(this).val();
		var inputs = $('input[name="options[]"]');
		var blank_inupts = 0;
		
		$(inputs).each(function() {
			if ($(this).val() == '')
			{
				blank_inupts = blank_inupts + 1;
			}
		});
		
		// Focus in
		if (e.type == 'focusin')
		{
			if ( ! val && blank_inupts < 2)
			{
				$(this).clone().appendTo('#options').wrap('<li />');
				
			}
		}
		
		// Focus out
		if (e.type == 'focusout')
		{
			if (!val && blank_inupts > 2)
			{
				$(this).remove();
			}
		}
	});
	*/
	
	// Datepicker
	$("#open_date, #close_date").datepicker({ dateFormat: 'yy/m/d' });
	
});
