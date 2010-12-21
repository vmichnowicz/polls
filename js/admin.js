jQuery(document).ready(function($) {
	
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
	$("#open_date, #close_date").datepicker({ dateFormat: 'yy/m/d' });
	
});