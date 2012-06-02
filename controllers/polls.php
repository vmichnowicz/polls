<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Create totally awesome polls.
 *
 * @author Victor Michnowicz
 * @category Modules
 */
class Polls extends Public_Controller {

	private $already_voted;
	private $poll_open;

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

		return $close_date > time() AND $open_date < time() ? TRUE : FALSE;
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

		// If this poll is for members only and the user is not logged in
		if ( $members_only AND ! $this->ion_auth->logged_in() )
		{
			return FALSE;
		}

		// If this poll is not open
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
	 * @access public
	 * @param string 			The slug of the poll
	 * @param bool 				Force showing of results
	 * @return void
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

			// Multiple option polls use checkbox inputs, single option polls use radio inputs
			$data['poll']['input_type'] = $data['poll']['type'] == 'single' ? 'radio' : 'checkbox';

			// Can this user vote?
			$can_vote = $this->can_vote($data['poll']);

			// If the user decided to vote, and can vote
			if ( $this->input->post('submit') AND $can_vote )
			{
				/**
				 * Make sure current session matches the session ID in the hidden input field
				 * If the user has cookies disabled then the session ID will have changed
				 */
				if ($this->session->userdata('session_id') != $this->input->post('session_id'))
				{
					show_error( lang('polls.cookies_required') );
				}

				// Grab all votes (or vote)
				$options = $this->input->post('options');

				// If no options were submitted
				if ($options === FALSE)
				{
					show_error( lang('polls.no_options_submitted') );
				}

				// Grab all "other" votes
				$other_options = $this->input->post('other_options');

				// If user sumitted multiple votes in a poll that only allows one vote (very naugty!)
				if (count($options) > 1 AND $data['poll']['type'] != 'multiple')
				{
					show_404();
				}

				// Get all poll options
				$poll_options = $this->poll_options_m->get_all_where_poll_id($poll_id);

				// Loop through all of our selected poll optoins
				foreach ($options as $option_id)
				{
					// If this poll option is not a valid option for the current poll
					if ( ! array_key_exists($option_id, $poll_options) )
					{
						show_404();
					}

					// Default to NULL "other" option text
					$other = NULL;

					// If this current poll option is of type "other"
					if ($poll_options[ $option_id ]['type'] == 'other')
					{
						// If this poll option has corresponding "other" text
						if (array_key_exists($option_id, $other_options))
						{
							$other = trim($other_options[ $option_id ]);
						}
					}

					// Record the vote
					$this->poll_options_m->record_vote($option_id, $other);
				}

				// Set session data so this user can not vote again (unless we explicitly allow it in the poll settings)
				$this->session->set_userdata('poll_' . $poll_id, $options);

				// Record user IP and session data in database 
				$this->poll_voters_m->record_voter($poll_id);

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
			$data['comments_enabled'] = $data['poll']['comments_enabled'] ? TRUE : FALSE;

			// If this user can vote and we are not forcing results
			if ($can_vote AND ! $show_results)
			{
				$this->template
					->title($data['poll']['title'])
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
						->set_breadcrumb( lang('polls.polls'), 'polls')
						->set_breadcrumb( $data['poll']['title'], 'polls/' . $data['poll']['slug'] )
						->set_breadcrumb( lang('polls.results') )
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
	 * @access public
	 * @param string 			The slug of the poll
	 * @return void
	 */
	public function results($slug = NULL)
	{
		$this->poll($slug, TRUE);
	}

}

// EOF