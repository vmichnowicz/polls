<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Widget_Polls extends Widgets {
	
	public $title = 'Poll Widget';
	public $description = 'Display some polls.';
	public $author = 'Victor Michnowicz';
	public $website = 'http://www.vmichnowicz.com/';
	public $version = '0.1';
	
	public $fields = array(
		array(
			'field'   => 'poll_id',
			'label'   => 'Poll',
			'rules'   => 'required'
		)
	);
	
	public function run($options)
	{
		if (empty($options['field_name']))
		{
			//return an array of data that will be parsed by views/display.php
			return array('output' => '');
		}

		// Store the feed items
		return array('output' => $options['html']);
	}
	
	public function form()
	{
		// Get all polls
		$query = $this->db->get('polls');
		
		// Our polls array
		$polls = array();
		
		// Get query results
		if ($query->num_rows() > 0)
		{
			foreach ($query->result() as $row)
			{
				$polls[$row->id] = $row->title;
			}
		}
		
		return array('polls' => $polls);
	}
	
}
