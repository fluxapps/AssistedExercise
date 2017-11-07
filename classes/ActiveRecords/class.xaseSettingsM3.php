<?php
/**
 * Class xaseSettingsM3
 *
 * @author  Benjamin Seglias <bs@studer-raimann.ch>
 */

class xaseSettingsM3 extends ActiveRecord {

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
	 * @var int
	 *
	 * @db_has_field  true
	 * @db_fieldtype  integer
	 * @db_length     1
	 * @db_is_notnull true
	 */
	protected $rate_answers = 1;
	/**
	 * @var string
	 *
	 * @db_has_field        true
	 * @db_fieldtype        timestamp
	 */
	protected $disposal_date;
	/**
	 * @var int
	 *
	 * @db_has_field  true
	 * @db_fieldtype  integer
	 * @db_length     1
	 * @db_is_notnull true
	 */
	protected $sample_solution_visible = 1;
	/**
	 * @var string
	 *
	 * @db_has_field        true
	 * @db_fieldtype        timestamp
	 */
	protected $start_voting_date;
	/**
	 * @var int
	 *
	 * @db_has_field  true
	 * @db_fieldtype  integer
	 * @db_length     1
	 * @db_is_notnull true
	 */
	protected $voting_points = 1;
	/**
	 * @var int
	 *
	 * @db_has_field  true
	 * @db_fieldtype  integer
	 * @db_length     1
	 * @db_is_notnull false
	 */
	protected $voting_points_percentage;


	/**
	 * @return int
	 */
	public function getId() {
		return $this->id;
	}


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
	 * @return int
	 */
	public function getRateAnswers() {
		return $this->rate_answers;
	}


	/**
	 * @param int $rate_answers
	 */
	public function setRateAnswers($rate_answers) {
		$this->rate_answers = $rate_answers;
	}


	/**
	 * @return string
	 */
	public function getDisposalDate() {
		return $this->disposal_date;
	}


	/**
	 * @param string $disposal_date
	 */
	public function setDisposalDate($disposal_date) {
		$this->disposal_date = $disposal_date;
	}


	/**
	 * @return int
	 */
	public function getSampleSolutionVisible() {
		return $this->sample_solution_visible;
	}


	/**
	 * @param int $sample_solution_visible
	 */
	public function setSampleSolutionVisible($sample_solution_visible) {
		$this->sample_solution_visible = $sample_solution_visible;
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


	/**
	 * @return int
	 */
	public function getVotingPoints() {
		return $this->voting_points;
	}


	/**
	 * @param int $voting_points
	 */
	public function setVotingPoints($voting_points) {
		$this->voting_points = $voting_points;
	}


	/**
	 * @return int
	 */
	public function getVotingPointsPercentage() {
		return $this->voting_points_percentage;
	}


	/**
	 * @param int $voting_points_percentage
	 */
	public function setVotingPointsPercentage($voting_points_percentage) {
		$this->voting_points_percentage = $voting_points_percentage;
	}
}