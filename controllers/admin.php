<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Create totally awesome polls.
 *
 * @author Victor Michnowicz
 * @category Modules
 */
class Admin extends Admin_Controller {

    public $data;

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

		// Set the validation rules for creating and modifying a poll
		$this->poll_validation_rules = array(
			array(
				'field' => 'title',
				'label' => 'lang:polls:title_label',
				'rules' => 'trim|max_length[64]|required'
			),
			array(
				'field' => 'slug',
				'label' => 'lang:polls:slug_label',
				'rules' => 'trim|max_length[64]|required|alpha_dash'
			),
			array(
				'field' => 'type',
				'label' => 'lang:polls:type_label',
				'rules' => 'trim'
			),
			array(
				'field' => 'description',
				'label' => 'Description',
				'rules' => 'trim'
			),
			array(
				'field' => 'open_date',
				'label' => 'lang:polls:open_date_label ',
				'rules' => 'callback_date_check'
			),
			array(
				'field' => 'close_date',
				'label' => 'lang:polls:close_date_label',
				'rules' => 'callback_date_check'
			),
			array(
				'field' => 'comments_enabled',
				'label' => 'Enable Comments',
				'rules' => 'trim'
			),
			array(
				'field' => 'members_only',
				'label' => 'lang:polls:members_only_label',
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

    protected function update_search_index($poll, array $options = null)
    {
        // Load the search index model
        $this->load->model('search/search_index_m');

        $options_array = null;
        $options_string = null;

        if ( ! empty($options) )
        {
            foreach($options as $option)
            {
                $options_array[] = preg_replace('/[^a-z0-9]+/i', ' ', $option['title']);
            }
        }

        $options_string = $options_array ? implode(', ', $options_array) : null;

        $this->search_index_m->index(
            'polls',
            'polls:poll',
            'polls:polls',
            $poll['id'],
            'polls/' . $poll['slug'],
            $poll['title'],
            $poll['description'],
            array(
                'cp_edit_uri'   => 'admin/polls/edit/' . $poll['id'],
                'cp_delete_uri' => 'admin/blog/delete/' . $poll['id'],
                'keywords'      => Keywords::process($options_string),
            )
        );
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
		$polls = $this->polls_m->retrieve_polls();
		
		// Make sure that we have polls to display
		if ( is_array($polls) AND count($polls) > 0 )
		{
			// For each poll (note the "&," we are assigning reference)
			foreach ($polls as &$poll)
			{
				// Add the poll options to our array
				$poll['options'] = $this->poll_options_m->retrieve_poll_options( $poll['id'] );
			}
		}

		// Load the view (and pass in the poll data)
		$this->template
			->append_css('module::admin.css')
			->title($this->module_details['name'])
			->build('admin/index', array('polls' => $polls));
	}

	/**
	 * Create a new poll
	 *
	 * @access public
	 * @return void
	 */
	public function insert()
	{
		// Set the validation rules
		$this->form_validation->set_rules($this->poll_validation_rules);

		// If form validation passed
		if ( $this->form_validation->run() )
		{
			$title = $this->input->post('title');
			$slug = $this->input->post('slug');

			// Insert poll
			$insert_id = $this->polls_m->insert_poll($title, $slug);

			// If poll was added successfully
			if ( ! empty($insert_id) )
			{
				$this->session->set_flashdata('success', lang('polls:create_success'));
				redirect('admin/polls/update/' . $insert_id);
			}
			else
			{
				$this->session->set_flashdata('error', lang('polls:create_error'));
				redirect('admin/polls/insert');
			}
		}

		$data['poll'] = $this->input->post('poll');

		// Load the view
		$this->template
			->append_metadata( $this->load->view('fragments/wysiwyg', $this->data, TRUE) )
			->append_js('module::admin.js')
			->title($this->module_details['name'], lang('polls:new_poll_label'))
			->build('admin/insert', $data);
	}

	/**
	 * Update an existing poll
	 *
	 * @access public
	 * @param int			ID of the poll to update
	 * @return void
	 */
	public function update($id = NULL)
	{
		// Make sure poll exists
		if ( empty($id) OR ! $this->polls_m->poll_exists($id) )
		{
			$this->session->set_flashdata('error', lang('polls:not_exist_error'));
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
			$active = $this->input->post('active');

			$update_poll = $this->polls_m->update_poll($id, $title, $slug, $description, $open_date, $close_date, $type, $multiple_votes, $comments_enabled, $members_only, $active);
			$update_options = TRUE; // Default to TRUE (If updating of options fails then this will change to FALSE)

			// Get all poll options
			$options = $this->input->post('options');

			// We only need to update options if this poll has an array of options (also make sure updating of poll was successful)
			if ( is_array($options) AND count($options) > 0 AND $update_poll )
			{
				$update_options = $this->poll_options_m->update_options($id, $options);
			}

            // Update search index
            $this->update_search_index($poll, $options);

			if ($update_poll AND $update_options)
			{
				$this->session->set_flashdata('success', lang('polls:update_success'));
				redirect('admin/polls/update/' . $id);
			}

			// That update did not go well
			else
			{
				$this->session->set_flashdata('error', lang('polls:update_error'));
				redirect('admin/polls/update/' . $id);
			}
		}

        // Build that thang
		$this->template
			->append_metadata( $this->load->view('fragments/wysiwyg', $this->data, TRUE) )
			->append_js('module::admin.js')
			->append_css('module::admin.css')
			->title($this->module_details['name'], lang('polls:new_poll_label'))
			->build('admin/update', $data);
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
		$poll = $this->polls_m->retrieve_poll($id);
		$options = $this->poll_options_m->retrieve_poll_options($id);
		$total_votes = $this->poll_options_m->get_total_votes($id);

		// Calculate percentages for each poll option
		if ( ! empty($options))
		{
			foreach ($options as &$option)
			{
				if ($option['votes'] > 0)
				{
					$option['percent'] = round($option['votes'] / $total_votes * 100, 1);
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
			->title($this->module_details['name'], lang('polls:results_label'))
			->build('admin/results', array('poll' => $poll, 'options' => $options, 'total_votes' => $total_votes));
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
		$success = TRUE;
		$ids = ( isset($id) AND ctype_digit($id) ) ? array($id) : $this->input->post('action_to');
		
		if ( empty($ids) OR ! is_array($ids) )
		{
			$this->session->set_flashdata('error', lang('polls:id_error'));
			redirect('admin/polls');
		}

		// Loop through each ID
		foreach ($ids as $id)
		{
			// Does the poll exist?
			if ( $this->polls_m->poll_exists($id) )
			{
				// Delete this poll
				if ( ! $this->polls_m->delete($id) )
				{
					$success = FALSE;
					$this->session->set_flashdata('error', lang('polls:delete_error'));
				}
			}
		}

		// If no errors in deleting the polls
		if ($success === TRUE)
		{
			$this->session->set_flashdata('success', lang('polls:delete_success'));
		}

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
		$this->form_validation->set_message('date_check', lang('polls:invalid_date') );
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
	 * @return void
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