<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 *
 * Create totally awesome polls.
 *
 * @author 		Victor Michnowicz
 * @category 	Modules
 *
 */
class Polls extends Public_Controller {

	/**
	 * Constructor method
	 *
	 * @access public
	 * 
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
		
		// Load the required classes
		$this->load->model('polls_m');
		$this->load->model('poll_options_m');
		$this->load->model('poll_votes_m');
		$this->load->model('comments/comments_m');
		$this->load->helper('cookie');
		$this->lang->load('polls');
	}
	
	/**
	 * Index method
	 *
	 * @access public
	 * 
	 * @return void
	 */
	public function index()
	{
		$data['polls'] = $this->polls_m->get_all();
		$this->template
			->title('polls')
			->append_metadata( css('polls.css', 'polls') )
			->build('index', $data);
	}
	
	/**
	 * View a single poll
	 *
	 * @author Victor Michnowicz
	 * 
	 * @access public
	 * 
	 * @param string 			The slug of the poll
	 * @param bool 				Force showing of results
	 */
	public function poll($slug = NULL, $show_results = FALSE)
	{
	
		// Get poll ID from the provided slug
		$poll_id = $this->polls_m->get_poll_id_from_slug($slug);
		
		// If this poll exists
		if ($poll_id)
		{
			// Get the data for this particular poll
			$data['poll'] = $this->polls_m->get_poll_by_id($poll_id);
			
			// Is this poll only for logged in members?
			$members_only = $data['poll']['members_only'];
			$members_only_check = ( $members_only AND !$this->ion_auth->logged_in() ) ? FALSE : TRUE;
			
			// Are we sure the user has not already voted in this poll?
			$already_voted = $this->poll_voters_m->allready_voted($poll_id);
			
			// If the user decided to vote, has not alreay voted in this poll, AND this poll is not members only AND the user is not logged in
			if ( $this->input->post('vote') AND ! $already_voted AND $members_only_check )
			{
				// Grab all votes (or vote)
				$votes = $this->input->post('vote');
				
				// Grab all "other" inputs
				$other = $this->input->post('other');
				
				// Array that will later be stored in cookies and used to determine what option(s) user voted for
				$votes_ids = array();
				
				// If user sumitted multiple votes in a poll that only allows one vote (very naugty!)
				if (count($votes) > 1 AND $data['poll']['type'] != 'multiple')
				{
					show_404();
				}
				
				// If our $votes are *not* an array (that means our poll type is "single")
				if ( ! is_array($votes) )
				{
					$votes = array(
						(int)$votes => array(
							'id' => $votes
						)
					);
				}
				
				// If we have "other" inputs
				if ($other)
				{
					// Loop through each $other input
					foreach ($other as $vote_id => $vote_info)
					{
						// If user entered text in our "other" text input field
						if (trim($vote_info['other']))
						{
							/*
							 * If the user selected this option
							 * 
							 * If a user just inputs text into this field, but does not select the corresponding
							 * radio or checkbox input as well, the vote will not be cast.
							 * A user *must* select the checkbox or radio button to cast a vote.
							 * It is assumed that JavaScript will be used to hide the text input unless
							 * the checkbox or radio button is selected.
							 */
							if ( isset($votes[(int)$vote_id]) )
							{
								// Merge our user input text into our $votes array
								$votes[$vote_id]['other'] = trim($vote_info['other']);
							}
						}
					}
				}
				
				// Go through our $votes array
				foreach ($votes as $vote)
				{
					// Make sure this poll option exists
					if ($this->poll_options_m->poll_option_exists($poll_id, $vote['id']))
					{
						// Get "other" vote (if it exists)
						$other = isset($vote['other']) ? $vote['other'] : NULL;
						
						// Record the vote
						$this->poll_options_m->record_vote($vote['id'], $other);
					}
					
					// Add ID to $votes_ids array
					array_push($votes_ids, $vote['id']);
				}
				
				// Set session data so this user can not vote again
				$this->session->set_userdata('poll_' . $poll_id, $votes);
				
				// Record user IP and session data in database 
				$this->poll_votes_m->record_vote($poll_id);
				
				// User just voted
				$already_voted = TRUE;
			}
			
			// Get poll options and votes
			$data['poll']['options'] = $this->poll_options_m->get_all_where_poll_id($poll_id);
			$data['poll']['total_votes'] = $this->poll_options_m->get_total_votes($poll_id);
			$data['user_vote'] = $this->session->userdata('poll_' . $poll_id) ? $this->session->userdata('poll_' . $poll_id) : array();
			
			// Calculate percentages for each poll option
			if ( ! empty($data['poll']['options']))
			{
				foreach ($data['poll']['options'] as &$option)
				{
					if ($option['votes'] > 0)
					{
						$option['percent'] = round($option['votes'] / $data['poll']['total_votes'] * 100, 1);
					}
					else
					{
						$option['percent'] = 0;
					}
				}
			}
		
			// Is this poll open?
			$close_date = ( $data['poll']['close_date'] ) ? $data['poll']['close_date'] : time() * 2;
			$open_date = $data['poll']['open_date'];
			
			if ( $close_date > time() AND $open_date < time() )
			{
				$poll_open = TRUE;
			}
			else
			{
				$poll_open = FALSE;
			}
			
			// Do we want comments?
			$data['comments_enabled'] = ( $data['poll']['comments_enabled'] == 1 ) ? TRUE : FALSE;
		
			// If this poll is currently open AND the user has not already voted for this poll AND show_results is still FALSE AND the member check passes
			if ( $poll_open AND !$already_voted AND !$show_results AND $members_only_check)
			{
				$this->template
					->title($data['poll']['title'])
					->append_metadata( css('polls.css', 'polls') )
					->set_breadcrumb( lang('polls.polls'), 'polls')
					->set_breadcrumb( $data['poll']['title'] )
					->build('poll_open', $data);
			}
		
			// This poll is closed, the user has already voted in it, or show_results is now TRUE
			else
			{
				if ($show_results)
				{
					$this->template
						->title($data['poll']['title'])
						->append_metadata( css('polls.css', 'polls') )
						->set_breadcrumb( lang('polls.polls'), 'polls')
						->set_breadcrumb( $data['poll']['title'] )
						->build('poll_closed', $data);
				}
				else
				{
					redirect('polls/results/' . $slug);
				}
			}	
			
		}
		// If this poll does not exist, show 404
		else
		{
			show_404();
		}
		
	}
	
	/**
	 * Show poll results
	 *
	 * @author Victor MichnUNowicz
	 * 
	 * @access public
	 * 
	 * @param string 			The slug of the poll
	 * @param bool 				Force showing of results
	 */
	function results($slug = NULL)
	{
		$this->poll($slug, TRUE);
	}
	
}