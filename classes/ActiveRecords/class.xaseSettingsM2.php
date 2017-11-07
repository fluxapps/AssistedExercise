<?php
/**
 * Class xaseSettingsM2
 *
 * @author  Benjamin Seglias <bs@studer-raimann.ch>
 */

class xaseSettingsM2 extends ActiveRecord {

	/**
	 * @return string
	 */
	public static function returnDbTableName() {
		return 'rep_robj_xase_sett_m3';
	}


	/**
	 * @var int
	 *
	 * @db_has_field        true
	 * @db_fieldtype        integer
	 * @db_length           8
	 * @db_is_primary       true
	 * @con_sequence        true
	 */
	protected $id;
	/**
	 * @var int
	 *
	 * @db_has_field  true
	 * @db_fieldtype  integer
	 * @db_length     4
	 * @db_is_notnull true
	 */
	protected $settings_id;
	/**
	 * @var string
	 *
	 * @db_has_field        true
	 * @db_fieldtype        timestamp
	 */
	protected $start_voting_date;


	/**
	 * @return int
	 */
	public function getSettingsId() {
		return $this->settings_id;
	}


	/**
	 * @param int $settings_id
	 */
	public function setSettingsId($settings_id) {
		$this->settings_id = $settings_id;
	}


	/**
	 * @return string
	 */
	public function getStartVotingDate() {
		return $this->start_voting_date;
	}


	/**
	 * @param string $start_voting_date
	 */
	public function setStartVotingDate($start_voting_date) {
		$this->start_voting_date = $start_voting_date;
	}
}