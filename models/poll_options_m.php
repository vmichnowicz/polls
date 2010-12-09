<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 *
 * Create totally awesome polls.
 *
 * @author 	Victor Michnowicz
 * @category 	Modules
 *
 */
class Poll_options_m extends MY_Model {
	
	/**
	 * Get all poll options with the poll ID that is passed in
	 *
	 * @author Victor Michnowicz
	 * @access public
	 * @param int $id ID of the poll
	 * @return mixed
	 */
	public function get_all_where_poll_id($id)
	{
		$results = array();

		$query = $this->db->query("SELECT * FROM poll_options WHERE poll_id = $id");
		
		if ($query->num_rows() > 0)
		{
			foreach ($query->result() as $row)
   			{
				$results[] = array(
					'id' => $row->id,
					'type' => $row->type,
					'title' => $row->title,
					'votes' => $row->votes
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
	 * @param int $poll_id ID of the poll
	 * @param array $options The poll option data to insert
	 * @return bool
	 */
	public function add($poll_id, $options)
	{
		// For each poll option
		foreach ($options as $option)
		{
			// If the option is not blank
			if ($option != '')
			{
				$data = array(
					'poll_id' => $poll_id,
					'type' => $type,
					'title' => $option
				);
				// Insert poll option into the database
				$this->db->insert('poll_options', $data); 
			}
		}
		
		return TRUE;

	}
	
	/**
	 * Get the total number of votes for a given poll
	 *
	 * @author Victor 
	 * @access public
	 * @param int $poll_id ID of the poll
	 * @return int
	 */	
	public function get_total_votes($poll_id)
	{
		$votes = 0;
		
		$this->db->select('votes');
		$this->db->where('poll_id', $poll_id);
		$query = $this->db->get('poll_options');
		
		if ($query->num_rows() > 0)
		{
			foreach ($query->result() as $row)
			{
				$votes = $votes + $row->votes;
			}
		}
		
		return $votes;
	}
	
	/**
	 * Check to see if a poll options exists
	 *
	 * @author Victor Michnowicz
	 * @access public
	 * @param int $poll_id The ID of the poll
	 * @param array $poll_option_id The ID of the poll option
	 * @return bool
	 */	
	public function poll_option_exists($poll_id, $poll_option_id)
	{
		$this->db->select('id');
		$this->db->where('poll_id', $poll_id);
		$this->db->where('id', $poll_option_id);
		$query = $this->db->get('poll_options');
		
		// Did we find poll option with the ID and poll ID provided?
		return ($query->num_rows() > 0) ? TRUE : FALSE;
	}
	
	/**
	 * Record a vote for a given poll option
	 *
	 * @author Victor Michnowicz
	 * 
	 * @access public
	 * 
	 * @param int 			The ID of the poll option
	 * @param string 		The "other" vote text (optional)
	 * 
	 * @return bool
	 */	
	public function record_vote($poll_option_id, $poll_other_vote = NULL)
	{
		
		$this->db->query("UPDATE poll_options SET votes = votes +1 WHERE id = $poll_option_id");
		
		// If an "other" poll option was specified
		if ($poll_other_vote)
		{
			$data = array(
				'parent_id' 	=> $poll_option_id,
				'text' 			=> $poll_other_vote
			);
			
			$this->db->insert('poll_other_votes', $data);
		}
		
		return TRUE;
	}

	/**
	 * Update existing poll options
	 *
	 * @author Victor Michnowicz
	 * @access public
	 * @param int $poll_id The ID of the poll
	 * @param array $input The data to use for updating the DB record
	 * @return bool
	 */
	public function update($poll_id, $input)
	{
		
		foreach ( $input as $option_id => $option_title )
		{
			// If this poll option exists
			if ( $this->poll_option_exists($poll_id, $option_id) )
			{
				// If the title is blank (the user wants to delete this option)
				if ($option_title == '')
				{
					// Delete it!
					$this->db->where( 'id', $option_id );
					$this->db->delete('poll_options');
				}
				
				// The title is not blank (the user wants to update this mofo fo' sho)
				else
				{
					// Update it!
					$this->db->where( 'id', $option_id );
					$this->db->update( 'poll_options', array('title' => $option_title) ); 
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
		
		return TRUE;

	}

}
