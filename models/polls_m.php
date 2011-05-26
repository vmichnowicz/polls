<?php defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Add, edit, delete, and update polls
 *
 * @author Victor Michnowicz
 * @category Modules
 */
class Polls_m extends MY_Model {
	
	/**
	 * Get all polls
	 *
	 * @access public
	 * @return mixed
	 */
	public function get_all()
	{
		$results = array();

		$query = $this->db->get('polls');
		
		if ($query->num_rows() > 0)
		{
			foreach ($query->result() as $row)
   			{
				// If poll has a close date and is currently open
				if ( $row->close_date AND ( $row->close_date > time() AND $row->open_date < time() ) )
				{
					$poll_open = TRUE;
				}
				// If poll is closed
				else
				{
					$poll_open = FALSE;
				}
			
				$results[] = array(
					'id' 				=> $row->id,
					'slug' 				=> $row->slug,
					'title' 			=> $row->title,
					'description' 		=> $row->description,
					'open_date' 		=> $row->open_date,
					'close_date' 		=> $row->close_date,
					'is_open' 			=> $poll_open,
					'created' 			=> $row->created,
					'last_updated' 		=> $row->last_updated,
					'type' 				=> $row->type,
					'multiple_votes'	=> $row->multiple_votes,
					'comments_enabled' 	=> (bool)$row->comments_enabled,
					'members_only' 		=> (bool)$row->members_only
				);
			}
			
			// Return all polls
			return $results;
		}
		
		// If the query returned no results, return FALSE
		return FALSE;
	}
	
	/**
	 * Make sure a poll exists
	 *
	 * @access public
	 * @param int poll ID
	 * @return bool
	 */	
	public function poll_exists($id)
	{
		$query = $this->db
			->where('id', $id)
			->limit(1)
			->get('polls');
		
		return $query->num_rows() > 0 ? TRUE : FALSE;
	}
	
	/**
	 * Get the poll ID from its slug
	 *
	 * @access public
	 * @param int poll slug
	 * @return mixed
	 */		
	public function get_poll_id_from_slug($slug)
	{
		$query = $this->db
			->where('slug', $slug)
			->limit(1)
			->get('polls');

		if ($query->num_rows() > 0)
		{
			$row = $query->row(); 
			return $row->id;
		}
		else
		{
			return FALSE;
		}
	}
	
	/**
	 * Return poll data
	 *
	 * @access public
	 * @param int poll ID
	 * @return mixed
	 */
	public function get_poll_by_id($id)
	{

		$query = $this->db
			->where('id', $id)
			->limit(1)
			->get('polls');
		
		if ($query->num_rows() > 0)
		{
			$row = $query->row(); 
			
			// If poll has a close date and is currently open
			if ( $row->close_date AND ( $row->close_date > time() AND $row->open_date < time() ) )
			{
				$poll_open = TRUE;
			}
			// If poll is closed
			else
			{
				$poll_open = FALSE;
			}
			
			$data = array(
				'id' 				=> $row->id,
				'slug' 				=> $row->slug,
				'title' 			=> $row->title,
				'description' 		=> $row->description,
				'open_date' 		=> $row->open_date,
				'close_date' 		=> $row->close_date,
				'is_open' 			=> $poll_open,
				'type' 				=> $row->type,
				'created' 			=> $row->created,
				'multiple_votes'	=> $row->multiple_votes,
				'comments_enabled' 	=> (bool)$row->comments_enabled,
				'members_only' 		=> (bool)$row->members_only
			);
			
			return $data;
		}
		
		// If the poll does not exist
		return FALSE;
	}

	/**
	 * Insert a new poll into the database
	 *
	 * @access public
	 * @param array $input The data to insert (a copy of $_POST)
	 * @return bool
	 */
	public function add($input)
	{
		// Prep data for insertion into the database
		$data = array(
			'title' 			=> $input['title'],
			'slug' 				=> $input['slug'],
			'description' 		=> $input['description'],
			'open_date' 		=> date_to_timestamp($input['open_date']),
			'close_date' 		=> date_to_timestamp($input['close_date']),
			'type' 				=> $input['type'],
			'multiple_votes'	=> (bool)$input['multiple_votes'],
			'comments_enabled' 	=> (bool)$input['comments_enabled'],
			'members_only' 		=> (bool)$input['members_only'],
			'created' 			=> time()
		);
		
		// Insert that data
		$this->db->insert('polls', $data);
		
		return $this->db->affected_rows() > 0 ? TRUE : FALSE;
	}
	
	/**
	 * Delete a poll from the database
	 *
	 * @access public
	 * @param int poll ID
	 * @return bool
	 */	
	public function delete($id)
	{		
		$this->db
			->from('polls')
			->where('id', $id)
			->delete();
			
		return $this->db->affected_rows() > 0 ? TRUE : FALSE;
	}

	/**
	 * Update an existing poll
	 *
	 * @access public
	 * @param int $id The ID of the poll to update
	 * @param array $input The POST data to use for updating the DB record
	 * @return bool
	 */
	public function update($id, $input)
	{
		// Get the poll data
		$data = array(
			'title' 			=> $input['title'],
			'slug' 				=> $input['slug'],
			'description' 		=> $input['description'],
			'open_date' 		=> date_to_timestamp($input['open_date']),
			'close_date' 		=> date_to_timestamp($input['close_date']),
			'type' 				=> $input['type'],
			'multiple_votes' 	=> (bool)$input['multiple_votes'],
			'comments_enabled' 	=> (bool)$input['comments_enabled'],
			'members_only' 		=> (bool)$input['members_only'],
			'last_updated' 		=> time()
		);
		
		// Update poll data
		$this->db
			->where('id', $id)
			->update('polls', $data);
		
		return $this->db->affected_rows() > 0 ? TRUE : FALSE;
	}

}