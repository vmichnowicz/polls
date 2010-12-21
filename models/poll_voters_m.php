<?php defined('BASEPATH') or exit('No direct script access allowed');

/**
 *
 * Poll voters model
 *
 * @author 			Victor Michnowicz
 * @category 		Modules
 *
 */
class Poll_voters_m extends MY_Model {
	
	/**
	 * Record user details in database
	 * 
	 * This allows us to make sure the same user does not vote multiple times in the same poll
	 *
	 * @author Victor Michnowicz
	 * @access public
	 * @param int poll ID
	 * @return null
	 */		
	public function record_voter($poll_id)
	{
		$data = array(
			'poll_id' 		=> $poll_id,
			'user_id' 		=> $this->ion_auth->logged_in() ? $this->session->userdata('user_id') : NULL,
			'session_id' 	=> $this->session->userdata('session_id'),
			'ip_address' 	=> $this->session->userdata('ip_address'),
			'timestamp' 	=> time()
		);
		
		$this->db->insert('poll_voters', $data);
	}
	
	/**
	 * Has a user already voted in this poll?
	 *
	 * @author Victor Michnowicz
	 * @access public
	 * @param int poll ID
	 * @return null
	 */	
	public function allready_voted($poll_id)
	{
		//First, let's see if we can find this poll in the userdata
		if ( $this->session->userdata('poll_' . $poll_id) )
		{
			return TRUE;
		}
		
		$user_id = $this->ion_auth->logged_in() ? $this->session->userdata('user_id') : NULL;
		$session_id = $this->session->userdata('session_id');;
		$ip_address = $this->session->userdata('ip_address');
		$current_time = time();
		
		$query = $this->db->query("
			SELECT *
			FROM poll_voters
			WHERE poll_id = $poll_id AND
				(user_id = '$user_id' OR session_id = '$session_id' OR ip_address = '$ip_address') 
		");
		
		if ($query->num_rows() > 0)
		{
			return TRUE;
		}
		else
		{
			return FALSE;
		}
		
	}

}