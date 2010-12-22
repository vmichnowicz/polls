<?php

// How long will we restrict the IP address of a user that already voted in a poll

$config['polls.ip_expire'] 						= 604800; // One week

// Database table names (these do nothing right now...)
$config['polls.polls_table_name'] 				= 'polls';
$config['poll.polls_options_table_name'] 		= 'poll_options';
$config['poll.poll_other_votes_table_name'] 	= 'poll_other_votes';
$config['poll.poll_votes_table_name'] 			= 'poll_votes';