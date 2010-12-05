<?php defined('BASEPATH') or exit('No direct script access allowed');

class Module_Polls extends Module {

	public $version = '0.3';

	public function info()
	{
		return array(
			'name' => array(
				'en' => 'Polls'
			),
			'description' => array(
				'en' => 'Create totally awesome polls.'
			),
			'frontend' => TRUE,
			'backend' => TRUE,
			'menu' => FALSE
		);
	}

	public function install()
	{
		// Create polls table
		$this->db->query("	
			CREATE TABLE IF NOT EXISTS `polls` (
			`id` tinyint(11) unsigned NOT NULL AUTO_INCREMENT,
			`slug` varchar(64) NOT NULL,
			`title` varchar(64) NOT NULL,
			`description` text,
			`open_date` int(16) unsigned DEFAULT NULL,
			`close_date` int(16) unsigned DEFAULT NULL,
			`created` int(16) unsigned NOT NULL,
			`last_updated` int(16) unsigned DEFAULT NULL,
			`comments_enabled` tinyint(1) NOT NULL DEFAULT '0',
			`members_only` tinyint(1) NOT NULL DEFAULT '0',
			PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
		");
		
		// Create poll_options table
		$this->db->query("
			CREATE TABLE IF NOT EXISTS `poll_options` (
			`id` smallint(11) unsigned NOT NULL AUTO_INCREMENT,
			`poll_id` tinyint(11) unsigned NOT NULL,
			`title` varchar(64) NOT NULL,
			`order` tinyint(2) unsigned DEFAULT NULL,
			`votes` mediumint(11) unsigned NOT NULL DEFAULT '0',
			PRIMARY KEY (`id`),
			KEY `poll_id` (`poll_id`)
			) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
		");
		
		// Referental integrity fo' sho
		$this->db->query("
			ALTER TABLE `poll_options`
			ADD CONSTRAINT `poll_options_ibfk_1`
			FOREIGN KEY (`poll_id`)
			REFERENCES `polls` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
		");
		
		// It worked!
		return TRUE;
	}

	public function uninstall()
	{
		// Get 
		$this->db->query("DROP TABLE `poll_options`, `polls`");
		return TRUE;
	}

	public function upgrade($old_version)
	{
		// Your Upgrade Logic
		return TRUE;
	}

	public function help()
	{
		// Return a string containing help info
		// You could include a file and return it here.
		return "Some Help Stuff";
	}
}
/* End of file details.php */