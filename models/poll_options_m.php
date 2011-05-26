<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Create totally awesome polls.
 *
 * @author Victor Michnowicz
 * @category Modules
 *
 */
class Poll_options_m extends MY_Model {
	
	/**
	 * Get all poll options with the poll ID that is passed in
	 *
	 * @author Victor Michnowicz
	 * @access public
	 * @param int 			ID of the poll
	 * @return mixed
	 */
	public function get_all_where_poll_id($id)
	{
		$results = array();

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
						$other[$r->id] = array(
							'id' => $r->id,
							'text' => htmlentities($r->text, ENT_QUOTES, 'UTF-8'), // Convert all applicable characters to HTML entities
							'created' => strtotime($r->created)
						);
					}
				}
				
				$results[$row->id] = array(
					'id' => $row->id,
					'type' => $row->type,
					'title' => $row->title,
					'votes' => $row->votes,
					'other' => $other
				);
			}
			
			// Return all polls
			return $results;
		}
		
		// If the query returned no results, return NULL
		return NULL;
	}

	/**
	 * Insert new poll options into the database
	 *
	 * @author Victor
	 * @access public
	 * @param int 			ID of the poll
	 * @param array 		The poll option data to insert
	 * @return bool
	 */
	public function add($poll_id, $options)
	{
		// Used for poll option order
		$count = 0;
		
		$this->db->trans_start();

		// For each poll option
		foreach ($options as $option)
		{
			// If the option is not blank
			if ($option != '')
			{
				$data = array(
					'poll_id' 	=> $poll_id,
					'type' 		=> $option['type'],
					'title' 	=> $option['title'],
					'`order`' 	=> $count
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
	 * Add a single poll option
	 *
	 * Used on poll modification page with ajax_add_option() method
	 *
	 * @author Victor
	 * @access public
	 * @param int 			ID of the poll
	 * @param string		Option type ("defined" or "other")
	 * @param string		Option title
	 * @return int
	 */
	public function add_single($poll_id, $option_type, $option_title)
	{
		
		$query = $this->db
			->select('`order`')
			->where('poll_id', $poll_id)
			->order_by('`order`', 'desc')
			->limit(1)
			->get('poll_options');
		
		$row = $query->row();
		
		$order = $row->order + 1;
		
		$data = array(
			'poll_id' 	=> $poll_id,
			'type' 		=> $option_type,
			'title' 	=> trim($option_title),
			'`order`' 	=> $order
		);
		
		// Insert poll option into the database
		$this->db->insert('poll_options', $data); 

		return $this->db->affected_rows() > 0 ? TRUE : FALSE;
	}
	
	/**
	 * Get the total number of votes for a given poll
	 *
	 * @author Victor
	 * @access public
	 * @param int 			ID of the poll
	 * @return int
	 */	
	public function get_total_votes($poll_id)
	{
		$votes = 0;
		
		$query = $this->db->query("
			SELECT SUM(votes) AS sum
			FROM poll_options
			WHERE poll_id = '$poll_id'
		");
		
		$row = $query->row();
		
		return $row->sum;
	}
	
	/**
	 * Check to see if a poll options exists
	 *
	 * @author Victor Michnowicz
	 * @access public
	 * @param int 			The ID of the poll
	 * @param array 		The ID of the poll option
	 * @return bool
	 */	
	public function poll_option_exists($poll_id, $poll_option_id)
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
	 * @author Victor Michnowicz
	 * @access public
	 * @param int 			The ID of the poll option
	 * @param string 		The "other" vote text (optional)
	 * @return bool
	 */	
	public function record_vote($poll_option_id, $poll_other_vote = NULL)
	{
		$this->db->trans_start();

		$this->db
			->set('votes', 'votes + 1', FALSE)
			->where('id', $poll_option_id)
			->update('poll_options');
		
		// If an "other" poll option was specified
		if ($poll_other_vote)
		{
			$data = array(
				'parent_id' 	=> $poll_option_id,
				'text' 			=> $poll_other_vote
			);
			
			$this->db->insert('poll_other_votes', $data);
		}
		
		$this->db->trans_complete();

		return $this->db->trans_status() ? TRUE : FALSE;
	}
	
	/**
	 * Update poll option order
	 *
	 * @author Victor Michnowicz
	 * @access public
	 * @param int 			The ID of the poll
	 * @param int 			The poll option ID
	 * @param int 			The poll option order
	 * @return bool
	 */
	public function option_order($poll_id, $poll_option_id, $order)
	{
		// Make sure this poll option exists 
		if ($this->poll_option_exists($poll_id, $poll_option_id))
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
		// If this poll option does not exist
		else
		{
			return FALSE;
		}
	} 

	/**
	 * Update existing poll options (or add a new one if the poll option does not exist)
	 *
	 * @author Victor Michnowicz
	 * @access public
	 * @param int 			The ID of the poll
	 * @param array 		The data to use for updating the DB record
	 * @return bool
	 */
	public function update($poll_id, $input)
	{
		$this->db->trans_start();

		foreach ( $input as $option_id => $option )
		{
			// Get the option title
			$option_title = $option['title'];
			
			// Get option type (default to "defined")
			$option_type = $option['type'] == 'other' ? 'other' : 'defined';
			
			// If this poll option exists
			if ( $this->poll_option_exists($poll_id, $option_id) )
			{
				// If the title is blank (the user wants to delete this option)
				if ($option_title == '')
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