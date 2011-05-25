<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Create totally awesome polls.
 *
 * @author Victor Michnowicz
 * @category Modules
 *
 */
class Polls extends Public_Controller {
	
	public $already_voted;
	public $poll_open;
	
	/**
	 * Constructor method
	 *
	 * @access public
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
		
		// Load the required classes
		$this->load->model('polls_m');
		$this->load->model('poll_options_m');
		$this->load->model('poll_voters_m');
		$this->load->model('comments/comments_m');
		$this->load->helper('cookie');
		$this->lang->load('polls');
	}
	
	/**
	 * Index method
	 *
	 * @access public
	 * @return void
	 */
	public function index()
	{
		$data['polls'] = $this->polls_m->get_all();
		$this->template
			->title('polls')
			->set_breadcrumb( lang('polls.polls'))
			->append_metadata( css('polls.css', 'polls') )
			->build('index', $data);
	}
	
	/**
	 * See if a poll is open
	 *
	 * @access private
	 * @param int			Open date
	 * @param int			Close date
	 * @return bool
	 */
	private function poll_open($open_date = NULL, $close_date = NULL)
	{
		$close_date = $close_date ? $close_date : time() * 2;

		return ( $close_date > time() AND $open_date < time() ) ? TRUE : FALSE;
	}

	/**
	 * See if the current user is allowed to vote in a provided poll
	 *
	 * @access private
	 * @param array			Poll data array
	 * @return bool
	 */
	private function can_vote($data)
	{
		// Is this poll only for logged in members?
		$members_only = $data['members_only'];
		
		// If this poll is for members only and the user is not a member
		if ( $members_only AND !$this->ion_auth->logged_in() )
		{
			return FALSE;
		}
		
		// If this poll is not open?
		if ( ! $this->poll_open($data['open_date'], $data['close_date']) )
		{
			return FALSE;
		}
		
		// Has the user already voted in this poll?
		$this->already_voted = $this->poll_voters_m->already_voted($data['id']);
		
		// If this poll does not allow multiple votes
		if ( $this->already_voted AND !$data['multiple_votes'] )
		{
			return FALSE;
		}

		// We are good!
		return TRUE;
	}
	
	/**
	 * View a single poll
	 *
	 * @author Victor Michnowicz
	 * @access public
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

			// Can this user vote?
			$can_vote = $this->can_vote($data['poll']);
			
			// If the user decided to vote, and can vote
			if ( $this->input->post('vote') AND $can_vote )
			{
				// Make sure current session matches the session ID in the hidden input field
				// If the user has cookies disabled then the session ID will have changed
				if ($this->session->userdata('session_id') != $this->input->post('session_id'))
				{
					show_error(lang('polls.cookies_required'));
				}
				
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
					$votes_ids[] = $vote['id'];
				}
				
				// Set session data so this user can not vote again
				$this->session->set_userdata('poll_' . $poll_id, $votes);
				
				// Record user IP and session data in database 
				$this->poll_voters_m->record_voter($poll_id);
				
				// User just voted
				$already_voted = TRUE;
				
				// Redirect user to results
				redirect('polls/results/' . $data['poll']['slug']);
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
			
			// Do we want comments?
			$data['comments_enabled'] = ( $data['poll']['comments_enabled'] == 1 ) ? TRUE : FALSE;
		
			// If this user can vote and we are not forcing results
			if ($can_vote AND !$show_results)
			{
				$this->template
					->title($data['poll']['title'])
					->append_metadata( css('polls.css', 'polls') )
					->set_breadcrumb( lang('polls.polls'), 'polls')
					->set_breadcrumb( $data['poll']['title'] )
					->build('poll_open', $data);
			}
		
			// The user can not vote in the poll or show_results is now TRUE
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
	 * @access public
	 * @param string 			The slug of the poll
	 */
	function results($slug = NULL)
	{
		$this->poll($slug, TRUE);
	}
	
}