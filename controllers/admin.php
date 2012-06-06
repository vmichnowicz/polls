<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Create totally awesome polls.
 *
 * @author Victor Michnowicz
 * @category Modules
 */
class Admin extends Admin_Controller {

	/**
	 * Validation rules for creating a new poll
	 *
	 * @var array
	 * @access private
	 */
	private $poll_validation_rules = array();

	/**
	 * Constructor method
	 *
	 * @access public
	 * @return void
	 */
	public function __construct()
	{
		// First call the parent's constructor
		parent::__construct();

		// Load all the required stuff
		$this->lang->load('polls');
		$this->load->library('form_validation');
		$this->load->model('polls_m');
		$this->load->model('poll_options_m');
		$this->load->helper('poll_date');

		// Set the validation rules for creating and modifying a poll
		$this->poll_validation_rules = array(
			array(
				'field' => 'title',
				'label' => 'lang:polls.title_label',
				'rules' => 'trim|max_length[64]|required'
			),
			array(
				'field' => 'slug',
				'label' => 'lang:polls.slug_label',
				'rules' => 'trim|max_length[64]|required|alpha_dash'
			),
			array(
				'field' => 'type',
				'label' => 'lang:polls.type_label',
				'rules' => 'trim'
			),
			array(
				'field' => 'description',
				'label' => 'Description',
				'rules' => 'trim'
			),
			array(
				'field' => 'open_date',
				'label' => 'lang:polls.open_date_label ',
				'rules' => 'callback_date_check'
			),
			array(
				'field' => 'close_date',
				'label' => 'lang:polls.close_date_label',
				'rules' => 'callback_date_check'
			),
			array(
				'field' => 'comments_enabled',
				'label' => 'Enable Comments',
				'rules' => 'trim'
			),
			array(
				'field' => 'members_only',
				'label' => 'lang:polls.members_only_label',
				'rules' => 'trim'
			),
			array(
				'field' => 'publish',
				'label' => 'Publish Poll',
				'rules' => 'trim'
			)
		);
		
		// Put those cool buttons at the top of the admin panel
		$this->template->set_partial('shortcuts', 'admin/partials/shortcuts');
	}

	/**
	 * List all polls
	 *
	 * @access public
	 * @return void
	 */
	public function index()
	{
		// Get all the polls
		$data['polls'] = $this->polls_m->retrieve_polls();
		
		// Make sure that we have polls to display
		if ($data['polls'])
		{
			// For each poll (note the "&," we are assigning reference)
			foreach ($data['polls'] as &$poll)
			{
				// Add the poll options to our array
				$poll['options'] = $this->poll_options_m->retrieve_poll_options($poll['id']);
			}
		}
		// Load the view (and pass in the poll data)
		$this->template->title($this->module_details['name'])->build('admin/index', $data);
	}

	/**
	 * Create a new poll
	 *
	 * @access public
	 * @return void
	 */
	public function create()
	{
		// Set the validation rules
		$this->form_validation->set_rules($this->poll_validation_rules);
		
		// If form validation passed
		if ( $this->form_validation->run() )
		{
			$title = $this->input->post('title');
			$slug = $this->input->post('slug');
			$description = $this->input->post('description');
			$open_date = $this->input->post('open_date') ? DateTime::createFromFormat('Y#m#d', $this->input->post('open_date')) : NULL;
			$close_date = $this->input->post('close_date') ? DateTime::createFromFormat('Y#m#d', $this->input->post('close_date')) : NULL;
			$type = $this->input->post('type');
			$multiple_votes = $this->input->post('multiple_votes');
			$comments_enabled = $this->input->post('comments_enabled');
			$members_only = $this->input->post('members_only');

			// Insert poll
			$insert_poll = $this->polls_m->insert_poll($title, $slug, $description, $open_date, $close_date, $type, $multiple_votes, $comments_enabled, $members_only);
			$insert_options = TRUE; // Default to TRUE (If inserting of options fails then this will change to FALSE)

			// Get all options
			$options =  $this->input->post('options');

			// We only need to insert options if this poll has an array of options (also make sure we have an an insert ID for the poll)
			if ( is_array($options) AND count($options) > 0 AND $insert_poll )
			{
				$insert_options = $this->poll_options_m->insert_options($insert_poll, $this->input->post('options'));
			}

			// Add the poll AND poll options into the database
			if ($insert_poll AND $insert_options)
			{
				// Great success! Both the poll and all the poll options were added successfully
				$this->session->set_flashdata('success', lang('polls.create_success'));
				redirect('admin/polls');
			}

			// We were unable to add the poll and poll options into the database
			else
			{
				$this->session->set_flashdata('error', lang('polls.create_error'));
				redirect('admin/polls/create');
			}

		}

		// Get the POST data
		$data['poll'] = $_POST;	

		// Load the view
		$this->template
			->append_metadata( $this->load->view('fragments/wysiwyg', $this->data, TRUE) )
			->append_js('module::admin.js')
			->append_js('module::create.js')
			->title($this->module_details['name'], lang('polls.new_poll_label'))
			->build('admin/new_poll', $data);
	}

	/**
	 * Manage an existing poll
	 *
	 * @access public
	 * @param int			ID of the poll to manage
	 * @return void
	 */
	public function manage($id = NULL)
	{
		// Make sure poll exists
		if ( ! $this->polls_m->poll_exists($id) )
		{
			redirect('admin/polls');
		}

		$this->form_validation->set_rules($this->poll_validation_rules);

		// Get the poll and poll options
		$poll = $this->polls_m->retrieve_poll($id);
		$options = $this->poll_options_m->retrieve_poll_options($id);

		$poll['options'] = $options;

		$data['poll'] = $poll;

		// If this form validation passed
		if ( $this->form_validation->run() )
		{
			$title = $this->input->post('title');
			$slug = $this->input->post('slug');
			$description = $this->input->post('description');
			$open_date = $this->input->post('open_date') ? DateTime::createFromFormat('Y#m#d', $this->input->post('open_date')) : NULL;
			$close_date = $this->input->post('close_date') ? DateTime::createFromFormat('Y#m#d', $this->input->post('close_date')) : NULL;
			$type = $this->input->post('type');
			$multiple_votes = $this->input->post('multiple_votes');
			$comments_enabled = $this->input->post('comments_enabled');
			$members_only = $this->input->post('members_only');

			$update_poll = $this->polls_m->update_poll($id, $title, $slug, $description, $open_date, $close_date, $type, $multiple_votes, $comments_enabled, $members_only);
			$update_options = TRUE; // Default to TRUE (If updating of options fails then this will change to FALSE)

			// Get all poll options
			$options = $this->input->post('options');

			// We only need to update options if this poll has an array of options (also make sure updating of poll was successful)
			if ( is_array($options) AND count($options) > 0 AND $update_poll )
			{
				$update_options = $this->poll_options_m->update_options($id, $options);
			}

			if ($update_poll AND $update_options)
			{
				$this->session->set_flashdata('success', lang('polls.update_success'));
				redirect('admin/polls/manage/' . $id);	
			}

			// That update did not go well
			else
			{
				$this->session->set_flashdata('error', lang('polls.update_error'));
				redirect('admin/polls/manage/' . $id);
			}
		}

		// Build that thang
		$this->template
			->append_metadata( $this->load->view('fragments/wysiwyg', $this->data, TRUE) )
			->append_js('module::admin.js')
			->append_js('module::manage.js')
			->append_css('module::admin.css')
			->append_css('module::manage.css')
			->title($this->module_details['name'], lang('polls.new_poll_label'))
			->build('admin/manage_poll', $data);
	}

	/**
	 * View poll results
	 *
	 * @access public
	 * @param int			ID of the poll
	 * @return void
	 */
	public function results($id = NULL)
	{
		$data['poll'] = $this->polls_m->retrieve_poll($id);
		$data['options'] = $this->poll_options_m->retrieve_poll_options($id);
		$data['total_votes'] = $this->poll_options_m->get_total_votes($id);

		// Calculate percentages for each poll option
		if ( ! empty($data['options']))
		{
			foreach ($data['options'] as &$option)
			{
				if ($option['votes'] > 0)
				{
					$option['percent'] = round($option['votes'] / $data['total_votes'] * 100, 1);
				}
				else
				{
					$option['percent'] = 0;
				}
			}
		}

		// Build that thang
		$this->template
			->append_css('module::results.css')
			->title($this->module_details['name'], lang('polls.results_label'))
			->build('admin/poll_results', $data);
	}

	/**
	 * Delete an existing poll
	 *
	 * @access public
	 * @param int			ID of the poll to delete
	 * @return void
	 */
	public function delete($id = NULL)
	{
		$id_array = array();

		// Multiple IDs or just a single one?
		if ( $_POST )
		{
			$id_array = $_POST['action_to'];
		}
		else
		{
			if ( $id !== NULL )
			{
				$id_array[0] = $id;
			}
		}

		if ( empty($id_array) )
		{
			$this->session->set_flashdata('error', lang('polls.id_error'));
			redirect('admin/polls');
		}

		// Loop through each ID
		foreach ( $id_array as $id)
		{
			// Get the poll
			$poll = $this->polls_m->poll_exists($id);

			// Does the poll exist?
			if ($poll)
			{
				// Delete this poll
				if (!$this->polls_m->delete($id) )
				{
					$this->session->set_flashdata('error', 'something did not go well');
				}
			}
		}

		$this->session->set_flashdata('success', lang('polls.delete_success'));
		redirect('admin/polls');
	}

	/**
	 * Validate a date (used for CIs form validation)
	 * 
	 * @access public
	 * @param string YYYY-MM-DD date string
	 * @return bool
	 */
	public function date_check($date)
	{
		// If no date was provided, return TRUE
		if ($date === NULL OR $date === '')
		{
			return TRUE;
		}

		// Method returns FALSE on failure
		if ( DateTime::createFromFormat('Y#m#d', $date) )
		{
			return TRUE;
		}

		// If we got this far we do not have a valid date
		$this->form_validation->set_message('date_check', lang('polls.invalid_date') );
		return FALSE;
	}

	/**
	 * Add a poll option
	 *
	 * @access public
	 * @return bool
	 */
	public function ajax_add_option()
	{
		$poll_id = $this->input->post('poll_id');
		$option_type = $this->input->post('new_option_type');
		$option_title = $this->input->post('new_option_title');

		return $this->poll_options_m->insert_option($poll_id, $option_type, $option_title);
	}

	/**
	 * Update poll option order
	 *
	 * @access public
	 * @param int 			The ID of the poll
	 * @return null
	 */
	public function ajax_update_order($poll_id)
	{
		// Make sure we have POST data
		if (isset($_POST))
		{
			foreach($_POST as $id=>$order)
			{
				$this->poll_options_m->option_order($poll_id, $id, $order);
			}
		}
	}

}