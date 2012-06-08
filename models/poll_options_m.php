<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Create totally awesome polls.
 *
 * @author Victor Michnowicz
 * @category Modules
 */
class Poll_options_m extends MY_Model {

	// Poll option type constants
	const TYPE_DEFINED = 'defined';
	const TYPE_OTHER = 'other';

	// Array of legit poll option types
	protected static $types = array(self::TYPE_DEFINED, self::TYPE_OTHER);

	/**
	 * Get all poll options related to a particular poll
	 *
	 * @access public
	 * @param int 			ID of the poll
	 * @return mixed
	 */
	public function retrieve_poll_options($id)
	{
		$return = array();

		$query = $this->db
			->where('poll_id', $id)
			->order_by('`order`', 'asc')
			->get('poll_options');

		if ($query->num_rows() > 0)
		{
			foreach ($query->result() as $row)
   			{
   				// Let's see if we have any other votes for this poll option
				$q = $this->db
					->where('parent_id', $row->id)
					->get('poll_other_votes');

				$other = array();

				if ($q->num_rows() > 0)
				{
					foreach ($q->result() as $r)
					{
						$other[ (int) $r->id ] = array(
							'id' => (int)$r->id,
							'text' => htmlentities($r->text, ENT_QUOTES, 'UTF-8'), // Convert all applicable characters to HTML entities
							'created' => $r->created ? DateTime::createFromFormat('U', $r->created) : NULL
						);
					}
				}

				$return[ (int) $row->id ] = array(
					'id' => (int)$row->id,
					'type' => $row->type,
					'title' => $row->title,
					'votes' => (int)$row->votes,
					'other' => $other
				);
			}

		}
		return $return;
	}

	/**
	 * Insert an individual poll option
	 *
	 * @access public
	 * @param type $poll_id
	 * @param type $type
	 * @param type $title
	 * @param type $order
	 * @return int
	 */
	public function insert_option($poll_id, $type, $title, $order = NULL)
	{
		// If we do not have an order
		if ( ! isset($order) OR ! is_int($order) OR ! ctype_digit($order) )
		{
			$order = 0; // Default to 0

			$query = $this->db
				->select('`order`')
				->where('poll_id', $poll_id)
				->order_by('`order`', 'desc')
				->limit(1)
				->get('poll_options');

			if ($query->num_rows() > 0)
			{
				$order = $query->row()->order + 1;
			}
		}

		$data = array(
			'poll_id' 	=> $poll_id,
			'type' 		=> in_array($type, self::$types) ? $type : self::TYPE_DEFINED, // Default to "defined" type
			'title' 	=> trim($title),
			'`order`' 	=> $order
		);

		$this->db->insert('poll_options', $data);

		return $this->db->affected_rows() > 0 ? $this->db->insert_id() : FALSE;
	}

	/**
	 * Insert new poll options into the database
	 *
	 * @access public
	 * @param int 			ID of the poll
	 * @param array 		Array of poll option data to insert
	 * @return bool
	 */
	public function insert_options($poll_id, array $options)
	{
		// Used for poll option order
		$count = 0;

		$this->db->trans_start();

		// For each poll option
		foreach ($options as $option)
		{
			// If the option is not blank
			if ( ! empty($option) )
			{
				$data = array(
					'poll_id' => $poll_id,
					'type'    => in_array($option['type'], self::$types) ? $option['type'] : self::TYPE_DEFINED, // Default to "defined" type
					'title'   => isset($option['title']) ? $option['title'] : '',
					'`order`' => $count
				);
				// Insert poll option into the database
				$this->db->insert('poll_options', $data);

				// Add +1 to our counter
				$count++;
			}
		}

		$this->db->trans_complete();

		return $this->db->trans_status() ? TRUE : FALSE;
	}

	/**
	 * Get the total number of votes for a given poll
	 *
	 * @access public
	 * @param int 			ID of the poll
	 * @return int
	 */	
	public function get_total_votes($poll_id)
	{
		$query = $this->db->query("
			SELECT SUM(votes) AS sum
			FROM "  . $this->db->dbprefix('poll_options') . "
			WHERE poll_id = '$poll_id'
		");

		$row = $query->row();

		return $row->sum;
	}

	/**
	 * Check to see if a poll options exists
	 *
	 * @access public
	 * @param int 			The ID of the poll
	 * @param array 		The ID of the poll option
	 * @return bool
	 */	
	public function check_option_exists($poll_id, $poll_option_id)
	{
		$query = $this->db
			->select('id')
			->where('poll_id', $poll_id)
			->where('id', $poll_option_id)
			->get('poll_options');

		// Did we find poll option with the ID and poll ID provided?
		return ($query->num_rows() > 0) ? TRUE : FALSE;
	}

	/**
	 * Record a vote for a given poll option
	 *
	 * @access public
	 * @param int 			The ID of the poll option
	 * @param string 		The "other" vote text (optional)
	 * @return bool
	 */	
	public function update_option_votes($poll_option_id, $poll_other_vote = NULL)
	{
		$this->db->trans_start();

		$this->db
			->set('votes', 'votes + 1', FALSE)
			->where('id', $poll_option_id)
			->update('poll_options');

		// If an "other" poll option was specified
		if ( isset($poll_other_vote) AND $poll_other_vote !== '' )
		{
			$data = array(
				'parent_id' => $poll_option_id,
				'text' 		=> $poll_other_vote,
				'created'	=> time()
			);

			$this->db->insert('poll_other_votes', $data);
		}

		$this->db->trans_complete();

		return $this->db->trans_status();
	}

	/**
	 * Update poll option order
	 *
	 * @access public
	 * @param int 			The ID of the poll
	 * @param int 			The poll option ID
	 * @param int 			The poll option order
	 * @return bool
	 */
	public function option_order($poll_id, $poll_option_id, $order)
	{
		// Make sure this poll option exists 
		if ($this->check_option_exists($poll_id, $poll_option_id))
		{
			$data = array(
				'`order`' => (int)$order
			);
			
			// Update order
			$this->db
				->where('id', $poll_option_id)
				->update('poll_options', $data);

			// Did this query affect any rows?
			return $this->db->affected_rows() > 0 ? TRUE : FALSE; 
		}

		return FALSE;
	} 

	/**
	 * Update existing poll options (or add a new one if the poll option does not exist)
	 *
	 * @access public
	 * @param int 			The ID of the poll
	 * @param array 		The data to use for updating the DB record
	 * @return bool
	 */
	public function update_options($poll_id, array $options)
	{
		$this->db->trans_start();

		foreach ($options as $option_id => $option)
		{
			// Get the option title
			$option_title = isset($option['title']) ? $option['title'] : '';

			// Get option type (default to "defined")
			$option_type = ( isset($option['type']) AND in_array($option['type'], self::$types) ) ? $option['type'] : self::TYPE_DEFINED;

			// If this poll option exists
			if ( $this->check_option_exists($poll_id, $option_id) )
			{
				// If the title is blank (the user wants to delete this option)
				if ($option_title === '')
				{
					// Delete it!
					$this->db
						->where('id', $option_id)
						->delete('poll_options');
				}

				// The title is not blank (the user wants to update this mofo fo' sho)
				else
				{
					$data = array(
						'title' 	=> $option_title,
						'type' 		=> $option_type
					);

					// Update it!
					$this->db
						->where('id', $option_id)
						->update('poll_options', $data); 
				}
			}

			// If this poll option does not exist
			else
			{
				// If this option is not blank (the user just added another poll option)
				if ($option_title != '')
				{
					// Insert the new option into the database
					$this->db->insert( 'poll_options', array('poll_id' => $poll_id, 'title' => $option_title) );
				}
			}
		}

		$this->db->trans_complete();

		return $this->db->trans_status() ? TRUE : FALSE;
	}

}