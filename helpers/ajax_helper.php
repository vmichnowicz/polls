<?php

// Is this an AJAX response?
function is_ajax()
{
	if (strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')
	{ 
		 return TRUE;
	}
	else
	{
		return FALSE;
	}
}
