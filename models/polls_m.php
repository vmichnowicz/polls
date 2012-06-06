<?php defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Add, edit, delete, and update polls
 *
 * @author Victor Michnowicz
 * @category Modules
 */
class Polls_m extends MY_Model {

	const TYPE_SINGLE = 'single';
	const TYPE_MULTIPLE = 'multiple';
	protected static $types = array(self::TYPE_SINGLE, self::TYPE_MULTIPLE);

	/**
	 * Get all polls
	 *
	 * Optionally get an individual poll by providing a poll ID.
	 *
	 * @access public
	 * @param int
	 * @return array
	 */
	public function retrieve_polls($id = NULL)
	{
		$return = array();

		// If a poll ID was provided (and the ID is a valid integer)
		if ( isset($id) AND ( is_int($id) OR ctype_digit($id) ) )
		{
			// Get only this poll
			$this->db
				->where('id', $id)
				->limit(1);
		}

		$query = $this->db->get('polls');

		if ($query->num_rows() > 0)
		{
			foreach ($query->result() as $row)
   			{
				// DateTime objects
				$open_date = $row->open_date ? DateTime::createFromFormat('U', $row->open_date) : NULL;
				$close_date = $row->close_date ? DateTime::createFromFormat('U', $row->close_date) : NULL;
				$now_date = new DateTime();

				// Default to open poll status
				$is_open = TRUE;

				// If this poll has an open date
				if ($open_date instanceof DateTime)
				{
					// If the open date is in the future
					if ($open_date > $now_date)
					{
						$is_open = FALSE;
					}
				}

				// If this poll has a close date
				if ($close_date instanceof DateTime)
				{
					// If the close date has already passed
					if ($close_date < $now_date)
					{
						$is_open = FALSE;
					}
				}

				$return[ (int)$row->id ] = array(
					'id' 				=> (int)$row->id,
					'slug' 				=> $row->slug,
					'title' 			=> $row->title,
					'description' 		=> $row->description,
					'open_date' 		=> $open_date,
					'close_date' 		=> $close_date,
					'is_open' 			=> $is_open,
					'created' 			=> $row->created ? DateTime::createFromFormat('U', $row->created) : NULL,
					'last_updated' 		=> $row->last_updated ? DateTime::createFromFormat('U', $row->last_updated) : NULL,
					'type' 				=> $row->type,
					'multiple_votes'	=> (bool)$row->multiple_votes,
					'comments_enabled' 	=> (bool)$row->comments_enabled,
					'members_only' 		=> (bool)$row->members_only
				);
			}
		}

		// Return all polls
		return $return;
	}

	/**
	 * Get an individual poll
	 *
	 * @access public
	 * @param int
	 * @return array|null
	 */
	public function retrieve_poll($id)
	{
		$poll = $this->retrieve_polls($id);
		return ( is_array($poll) AND count($poll) === 1 ) ? array_shift($poll) : NULL;
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

		return $query->num_rows();
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
			->select('id')
			->where('slug', $slug)
			->limit(1)
			->get('polls');

		return $query->num_rows() > 0 ? $query->row()->id : FALSE;
	}

	/**
	 * Insert a new poll into the database
	 *
	 * @access public
	 * @param string		Title
	 * @param string		Slug
	 * @param string		Description
	 * @param DateTime		Open date
	 * @param boolean		MultipleTime Close date
	 * @param string		Type
	 * @param boolean		Multiple votes
	 * @param boolean		Comments enabled
	 * @param boolean		Members only
	 * @return bool
	 */
	public function insert_poll($title, $slug, $description = '', DateTime $open_date = NULL, DateTime $close_date = NULL, $type = self::TYPE_SINGLE, $multiple_votes = FALSE, $comments_enabled = FALSE, $members_only = FALSE)
	{
		// Prep data for insertion into the database
		$data = array(
			'title' 			=> $title,
			'slug' 				=> $slug,
			'description' 		=> $description,
			'open_date' 		=> $open_date instanceof DateTime ? $open_date->format('U') : NULL,
			'close_date' 		=> $close_date instanceof DateTime ? $close_date->format('U') : NULL,
			'type' 				=> in_array($type, self::$types) ? $type : self::TYPE_SINGLE,
			'multiple_votes'	=> (bool)$multiple_votes,
			'comments_enabled'	=> (bool)$comments_enabled,
			'members_only' 		=> (bool)$members_only,
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
	 * @param int			Poll ID
	 * @param string		Title
	 * @param string		Slug
	 * @param string		Description
	 * @param DateTime		Open date
	 * @param boolean		MultipleTime Close date
	 * @param string		Type
	 * @param boolean		Multiple votes
	 * @param boolean		Comments enabled
	 * @param boolean		Members only
	 * @return bool
	 */
	public function update_poll($id, $title, $slug, $description = '', DateTime $open_date = NULL, DateTime $close_date = NULL, $type = self::TYPE_SINGLE, $multiple_votes = FALSE, $comments_enabled = FALSE, $members_only = FALSE)
	{
		// Get the poll data
		$data = array(
			'title' 			=> $title,
			'slug' 				=> $slug,
			'description' 		=> $description,
			'open_date' 		=> $open_date instanceof DateTime ? $open_date->format('U') : NULL,
			'close_date' 		=> $close_date instanceof DateTime ? $close_date->format('U') : NULL,
			'type' 				=> in_array($type, self::$types) ? $type : self::TYPE_SINGLE,
			'multiple_votes' 	=> (bool)$multiple_votes,
			'comments_enabled' 	=> (bool)$comments_enabled,
			'members_only' 		=> (bool)$members_only,
			'last_updated' 		=> time()
		);

		// Update poll data
		$this->db
			->where('id', $id)
			->update('polls', $data);

		return $this->db->affected_rows() > 0 ? TRUE : FALSE;
	}

}