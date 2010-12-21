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
		$poll_id = $options['poll_id'];
		
		$query = $this->db->where('id', $poll_id)->limit(1)->get('polls');
		
		$row = $query->row(); 
		
		$data = array(
			'slug' => $row->slug,
			'title' => $row->title,
			'description' => $row->description,
			'open_date' => $row->open_date,
			'close_date' => $row->close_date,
			'created' => $row->created
		);
		
		// Get options
		
		$query = $this->db->where('poll_id', $poll_id)->order_by('`order`', 'asc')->get('poll_options');
		
		$options = array();
		$votes = 0;
		
		if ($query->num_rows() > 0)
		{
			foreach ($query->result() as $row)
			{
				$options[] = array(
					'id' => $row->id,
					'type' 	=> $row->type,
					'title' 	=> $row->title,
					'votes' 	=> $row->votes
				);
			}
		}
		
		$query = $this->db->query("SELECT SUM(votes) AS sum FROM poll_options WHERE poll_id = '$poll_id'");
		
		$row = $query->row();
		
		$data['total_votes'] = $row->sum;
		
		$data['options'] = $options;
		
		// Send data
		return array('data' => $data);
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
