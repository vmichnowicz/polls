jQuery(document).ready(function($) {

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
	
	// Datepicker
	$("#open_date, #close_date").datepicker({ dateFormat: 'yy/m/d' });
	
});