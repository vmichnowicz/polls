<?php defined('BASEPATH') or exit('No direct script access allowed');

/**
 *
 * The galleries mo
 *
 * @author 			Victor Michnowicz
 * @category 		Modules
 *
 */
class Poll_votes_m extends MY_Model {
	
	/**
	 * asdf
	 *
	 * @author Victor Michnowicz
	 * @access public
	 * @param int poll ID
	 * @return null
	 */		
	public function record_vote($poll_id)
	{
		$data = array(
			'poll_id' 		=> $poll_id,
			'user_id' 		=> $this->ion_auth->logged_in() ? $this->session->userdata('user_id') : NULL,
			'session_id' 	=> $this->session->userdata('session_id'),
			'ip_address' 	=> $this->session->userdata('ip_address')
		);
		
		$this->db->insert('poll_votes', $data);
	}
	
	/**
	 * asdf
	 *
	 * @author Victor Michnowicz
	 * @access public
	 * @param int poll ID
	 * @return null
	 */	
	public function allready_voted($poll_id)
	{
		
		$user_id = $this->ion_auth->logged_in() ? $this->session->userdata('user_id') : '';
		$session_id = $this->session->userdata('session_id');;
		$ip_address = $this->session->userdata('ip_address');
		
		$query = $this->db->query("
			SELECT *
			FROM poll_votes
			WHERE poll_id = $poll_id AND
				(user_id = $user_id OR session_id = '$session_id' OR ip_address = '$ip_address') 
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