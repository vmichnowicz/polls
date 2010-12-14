<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 *
 * Create totally awesome polls.
 *
 * @author 	Victor Michnowicz
 * @category 	Modules
 *
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
	 * @author Victor Michnowicz
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
		$this->load->helper('date');

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
	 * @author Victor Michnowicz
	 * @access public
	 * @return void
	 */
	public function index()
	{
		// Get all the polls
		$data['polls'] = $this->polls_m->get_all();
		
		// Make sure that we have polls to display
		if ($data['polls'])
		{
			// For each poll (note the "&," we are assigning reference)
			foreach ($data['polls'] as &$poll)
			{
				// Add the poll options to our array
				$poll['options'] = $this->poll_options_m->get_all_where_poll_id($poll['id']);
			}
		}
		// Load the view (and pass in the poll data)
		$this->template->title($this->module_details['name'])->build('admin/index', $data);
	}

	/**
	 * Create a new poll
	 *
	 * @author Victor Michnowicz
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
			
			// Add the poll AND poll options into the database
			if ( $this->polls_m->add($_POST) AND $this->poll_options_m->add($this->db->insert_id(), $_POST['options']) )
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
			->append_metadata( js('manage.js', 'polls') )
			->title($this->module_details['name'], lang('polls.new_poll_label'))
			->build('admin/new_poll', $data);
	}

	/**
	 * Manage an existing poll
	 *
	 * @author Victor Michnowicz
	 * @access public
	 * @param int $id The ID of the poll to manage
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
		$poll = $this->polls_m->get_poll_by_id($id);		
		$options = $this->poll_options_m->get_all_where_poll_id($id);
		
		$poll['options'] = $options;
		
		$data['poll'] = $poll;

		// If this form validation passed
		if ( $this->form_validation->run() )
		{

			// Update both the poll and poll options
			if ( $this->polls_m->update($id, $_POST) AND $this->poll_options_m->update($id, $_POST['options']) )
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
			->append_metadata( js('manage.js', 'polls') )
			->title($this->module_details['name'], lang('polls.new_poll_label'))
			->build('admin/manage_poll', $data);
	}

	/**
	 * Delete an existing poll
	 *
	 * @author Victor Michnowicz
	 * @access public
	 * @param int $id The ID of the poll to delete
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
	 * @author Victor Michnowicz
	 * @access public
	 * @param string YYYY/MM/DD date string
	 * @return bool
	 */
	public function date_check($date)
	{
		// If no date was provided, return TRUE
		if (!$date) {
			return TRUE;
		}
		
		// Split up the date
		$date_exploded = explode('/', $date, 3);
		
		// Make sure we are dealing with three chunks
		if (count($date_exploded) == 3)
		{
			$year = (int)$date_exploded[0];
			$month = (int)$date_exploded[1];
			$day = (int)$date_exploded[2];
			
			// If the user entered a valid date
			if(checkdate($month, $day, $year))
			{
				return TRUE;
			}
		}
		
		$this->form_validation->set_message('date_check', lang('polls.invalid_date') );
		return FALSE;
	}
	
	/**
	 * Add a poll option
	 *
	 * @author Victor Michnowicz
	 * 
	 * @access public
	 * 
	 * @param string YYYY/MM/DD date string
	 * 
	 * @return string
	 */
	public function ajax_add_option()
	{
		$poll_id = $this->input->post('poll_id');
		$option_type = $this->input->post('new_option_type');
		$option_title = $this->input->post('new_option_title');
		
		if ($this->poll_options_m->add_single($poll_id, $option_type, $option_title))
		{
			return TRUE;
		}
	}

	public function ajax_update_order()
	{
		// This would be cool. Do this.
	}
	
	
	
}
