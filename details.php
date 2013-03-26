<?php defined('BASEPATH') or exit('No direct script access allowed');

class Module_Polls extends Module {

	public $version = '1.1';
	const MIN_PHP_VERSION = '5.3.0';

	/**
	 * Module information
	 *
	 * @access public
	 * @return void
	 */
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
			'menu' => 'content',
			'shortcuts' => array(
				array(
			 	   'name' => 'polls:new_poll_label',
				   'uri' => 'admin/polls/insert',
				),
			),
		);
	}

	/**
	 * Check the current version of PHP and thow an error if it's not good enough
	 *
	 * @access private
	 * @return boolean
	 */
	private function check_php_version()
	{
		// If current version of PHP is not up snuff
		if ( version_compare(PHP_VERSION, self::MIN_PHP_VERSION) < 0 )
		{
			show_error('This add-on requires PHP version ' . self::MIN_PHP_VERSION . ' or higher.');
			return FALSE;
		}
	}

	/**
	 * Install module
	 *
	 * @access public
	 * @return bool
	 */
	public function install()
	{
		$this->check_php_version();

		// Make sure all tables are gone first
		$this->uninstall();

		// Start transaction
		$this->db->trans_start();

		// Create polls table
		$this->db->query("
			CREATE TABLE IF NOT EXISTS `" . $this->db->dbprefix('polls') . "` (
			`id` tinyint(11) unsigned NOT NULL AUTO_INCREMENT,
			`slug` varchar(64) NOT NULL,
			`title` varchar(64) NOT NULL,
			`type` enum('single','multiple') NOT NULL DEFAULT 'single',
			`description` text,
			`open_date` int(16) unsigned DEFAULT NULL,
			`close_date` int(16) unsigned DEFAULT NULL,
			`created` int(16) unsigned NOT NULL,
			`last_updated` int(16) unsigned DEFAULT NULL,
			`multiple_votes` tinyint(1) unsigned NOT NULL DEFAULT '0',
			`comments_enabled` tinyint(1) unsigned NOT NULL DEFAULT '0',
			`members_only` tinyint(1) unsigned NOT NULL DEFAULT '0',
			`active` tinyint(1) NOT NULL DEFAULT '0',
			PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		");

		// Create poll_options table
		$this->db->query("
			CREATE TABLE IF NOT EXISTS `" . $this->db->dbprefix('poll_options') . "` (
			`id` smallint(11) unsigned NOT NULL AUTO_INCREMENT,
			`poll_id` tinyint(11) unsigned NOT NULL,
			`type` enum('defined','other') NOT NULL DEFAULT 'defined',
			`title` varchar(256) NOT NULL,
			`order` tinyint(2) unsigned DEFAULT NULL,
			`votes` mediumint(11) unsigned NOT NULL DEFAULT '0',
			PRIMARY KEY (`id`),
			KEY `poll_id` (`poll_id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		");

		// Create poll_other_votes table
		$this->db->query("
			CREATE TABLE IF NOT EXISTS `" . $this->db->dbprefix('poll_other_votes') . "` (
			`id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
			`parent_id` smallint(11) unsigned NOT NULL,
			`text` tinytext NOT NULL,
			`created` int(16) NOT NULL,
			PRIMARY KEY (`id`),
			KEY `parent_id` (`parent_id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		");

		// Create poll_voters table
		$this->db->query("
			CREATE TABLE IF NOT EXISTS `" . $this->db->dbprefix('poll_voters') . "` (
			`id` mediumint(32) unsigned NOT NULL AUTO_INCREMENT,
			`poll_id` tinyint(11) unsigned NOT NULL,
			`user_id` smallint(5) unsigned DEFAULT NULL,
			`session_id` varchar(40) NOT NULL,
			`ip_address` varchar(16) NOT NULL,
			`timestamp` int(11) unsigned NOT NULL,
			PRIMARY KEY (`id`),
			KEY `poll_id` (`poll_id`),
			KEY `user_id` (`user_id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		");

		// Referental integrity fo' sho
		$this->db->query("
			ALTER TABLE `" . $this->db->dbprefix('poll_options') . "`
			ADD CONSTRAINT `poll_options_ibfk_1`
			FOREIGN KEY (`poll_id`)
			REFERENCES `" . $this->db->dbprefix('polls') . "` (`id`)
			ON DELETE CASCADE
			ON UPDATE CASCADE;
		");

		$this->db->query("
			ALTER TABLE `" . $this->db->dbprefix('poll_other_votes') . "`
			ADD CONSTRAINT `poll_other_votes_ibfk_1`
			FOREIGN KEY (`parent_id`)
			REFERENCES `" . $this->db->dbprefix('poll_options') . "` (`id`)
			ON DELETE CASCADE
			ON UPDATE CASCADE;
		");

		$this->db->query("
			ALTER TABLE `" . $this->db->dbprefix('poll_voters') . "`
			ADD CONSTRAINT `poll_votes_ibfk_1`
			FOREIGN KEY (`poll_id`)
			REFERENCES `" . $this->db->dbprefix('polls') . "` (`id`)
			ON DELETE CASCADE
			ON UPDATE CASCADE;
		");

		// End transaction
		$this->db->trans_complete();

		// If transaction was successful retrun TRUE, else FALSE
		return $this->db->trans_status() ? TRUE : FALSE;
	}

	/**
	 * Uninstall module
	 *
	 * Due to foreign key constraints we must drop tables in a very specific
	 * order.
	 *
	 * @access public
	 * @return bool
	 */
	public function uninstall()
	{
		$this->dbforge->drop_table('poll_voters');
		$this->dbforge->drop_table('poll_other_votes');
		$this->dbforge->drop_table('poll_options');
		$this->dbforge->drop_table('polls');
		
		return TRUE;
	}

	/**
	 * Upgrade module
	 *
	 * @access public
	 * @param string
	 * @return bool
	 */
	public function upgrade($old_version)
	{
		$this->check_php_version();

		// Start transaction
		$this->db->trans_start();

		// Version 0.4 (the first official release)
		if ($old_version == '0.4')
		{
			// Update polls table
			$this->db->query("
				ALTER TABLE  `polls` ADD  `type` enum('single','multiple') NOT NULL DEFAULT 'single'
			");

			// Update poll_options table
			$this->db->query("
				ALTER TABLE  `poll_options` ADD  `type` enum('defined','other') NOT NULL DEFAULT 'defined'
			");

			// Add poll_other_votes table
			$this->db->query("
				CREATE TABLE IF NOT EXISTS `poll_other_votes` (
				`id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
				`parent_id` smallint(11) unsigned NOT NULL,
				`text` tinytext NOT NULL,
				`created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
				PRIMARY KEY (`id`),
				KEY `parent_id` (`parent_id`)
				) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
			");

			// Add poll_voters table
			$this->db->query("
				CREATE TABLE IF NOT EXISTS `poll_voters` (
				`id` mediumint(32) unsigned NOT NULL AUTO_INCREMENT,
				`poll_id` tinyint(11) unsigned NOT NULL,
				`user_id` smallint(5) unsigned DEFAULT NULL,
				`session_id` varchar(40) NOT NULL,
				`ip_address` varchar(16) NOT NULL,
				`timestamp` int(11) unsigned NOT NULL,
				PRIMARY KEY (`id`),
				KEY `poll_id` (`poll_id`),
				KEY `user_id` (`user_id`)
				) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
			");

			// Referental integrity fo' sho
			$this->db->query("
				ALTER TABLE `poll_other_votes`
				ADD CONSTRAINT `poll_other_votes_ibfk_1`
				FOREIGN KEY (`parent_id`)
				REFERENCES `poll_options` (`id`)
				ON DELETE CASCADE
				ON UPDATE CASCADE;
			");

			$this->db->query("
				ALTER TABLE `poll_voters`
				ADD CONSTRAINT `poll_votes_ibfk_1`
				FOREIGN KEY (`poll_id`)
				REFERENCES `polls` (`id`)
				ON DELETE CASCADE
				ON UPDATE CASCADE;
			");
		}
		// Version 0.5
		elseif ($old_version == '0.5')
		{
			$this->db->query("
				ALTER TABLE  `polls` ADD  `multiple_votes` TINYINT(1) NOT NULL DEFAULT  '0' AFTER  `last_updated`
			");
		}

		// If less than version 0.8
		if ($old_version < '0.8')
		{
			// Rename all tables to add prefix
			$this->db->query("RENAME TABLE  `polls` TO  `" . $this->db->dbprefix('polls') . "`");
			$this->db->query("RENAME TABLE  `poll_options` TO  `" . $this->db->dbprefix('poll_options') . "`");
			$this->db->query("RENAME TABLE  `poll_other_votes` TO  `" . $this->db->dbprefix('poll_other_votes') . "`");
			$this->db->query("RENAME TABLE  `poll_voters` TO  `" . $this->db->dbprefix('poll_voters') . "`");
			$this->db->query("RENAME TABLE  `poll_options` TO  `" . $this->db->dbprefix('poll_options') . "`");
			$this->db->query("RENAME TABLE  `poll_other_votes` TO  `" . $this->db->dbprefix('poll_other_votes') . "`");
			$this->db->query("RENAME TABLE  `poll_voters` TO  `" . $this->db->dbprefix('poll_voters') . "`");
		}

		if ($old_version < '1.0')
		{
			// Versions less than 1.0 had a TIMESTAMP type for poll_other_options (makes more sense IMO, but everything else in PyroCMS is using UNIX timestamps)
			$this->db->query("
				ALTER TABLE `" . $this->db->dbprefix('poll_other_votes') . "` CHANGE `created` `created` INT(16) NOT NULL
			");

			// Make poll option titles longer
			$this->db->query("
				ALTER TABLE `" . $this->db->dbprefix('poll_options') . "` CHANGE `title` `title` VARCHAR(256) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL
			");

			// Add "active" status for polls
			$this->db->query("
				ALTER TABLE `" . $this->db->dbprefix('polls') . "` ADD `active` TINYINT(1) NOT NULL DEFAULT '0'
			");
		}

		// End transaction
		$this->db->trans_complete();

		// If transaction was successful retrun TRUE, else FALSE
		return $this->db->trans_status() ? TRUE : FALSE;
	}

	/**
	 * Help
	 *
	 * @access public
	 * @return string
	 */
	public function help()
	{
		return '<a href="https://github.com/vmichnowicz/polls">View Source on Github</a>';
	}
}

// EOF