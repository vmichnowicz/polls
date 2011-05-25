<?php

/**
 * Convert YYYY/MM/DD to a timestamp
 *
 * @param string 		Date string
 * @return mixed
 */
function date_to_timestamp($date)
{
	if ( ! $date)
	{
		return NULL;
	}
	else
	{
		$date_exploded = explode('/', $date, 3);
		
		$year = (int)$date_exploded[0];
		$month = (int)$date_exploded[1];
		$day = (int)$date_exploded[2];

		return mktime(0, 0, 0, $month, $day, $year);
	}
}

/**
 * Convert a timestamp to a date in the YYYY/MM/DD format
 *
 * @param int			Date timestamp
 * @return string		Date in YYYY/MM/DD format
 */
function timestamp_to_date($timestamp)
{
	if ( ! $timestamp)
	{
		return NULL;
	}
	else
	{
		return date('Y/n/d', $timestamp);
	}
}