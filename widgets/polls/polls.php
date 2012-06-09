<?php defined('BASEPATH') or exit('No direct script access allowed');

class Widget_Polls extends Widgets {
	
	public $title = 'Poll Widget';
	public $description = 'Display a poll.';
	public $author = 'Victor Michnowicz';
	public $website = 'http://www.vmichnowicz.com/';
	public $version = '0.4';
	public $fields = array(
		array(
			'field'		=> 'poll_id',
			'label'		=> 'Poll',
			'rules'		=> 'required'
		)
	);

	/**
	 * Constructor
	 *
	 * @access public
	 * @return void
	 */
	public function __construct()
	{
		// Load models
		$this->load->model('modules/module_m');
		$this->load->model('polls/polls_m');
		$this->load->model('polls/poll_options_m');
		$this->load->model('polls/poll_voters_m');
		
		// Load language file
		$this->lang->load('polls/polls');
	}

	/**
	 * Run widget
	 *
	 * @access public
	 * @return array
	 */
	public function run($options)
	{
		// Get poll ID
		$poll_id = $options['poll_id'];
		
		// Get poll data
		$data = $this->polls_m->retrieve_poll($poll_id, TRUE);

		// If this poll exists
		if ($data)
		{
			// Has user alread voted in this poll?
			$data['already_voted'] = $this->poll_voters_m->already_voted($poll_id);

			// Set input type
			$data['input_type'] = $data['type'] == 'single' ? 'radio' : 'checkbox';

			// Get options
			$data['poll_options'] = $this->poll_options_m->retrieve_poll_options($poll_id);

			// Get total votes
			$data['total_votes'] = $this->poll_options_m->get_total_votes($poll_id);

			// Send data
			return $data;
		}

		return FALSE;
	}

	/**
	 * Display form
	 *
	 * @access public
	 * @return array
	 */
	public function form()
	{
		// Get all [active] polls
		$polls = $this->polls_m->retrieve_polls(TRUE);
		return array('polls' => $polls);
	}
	
}