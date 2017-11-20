<?php
/**
 * Class xaseSettingAbstract
 *
 * @author  Benjamin Seglias <bs@studer-raimann.ch>
 */

abstract class xaseSettingAbstract extends ActiveRecord {

	const MODUS1 = 1;
	const MODUS2 = 2;

	/**
	 * @var int
	 *
	 * @db_has_field        true
	 * @db_fieldtype        integer
	 * @db_length           8
	 * @db_is_primary       true
	 * @con_is_unique       true
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
	protected $assisted_exercise_object_id;
	/**
	 * @var int
	 *
	 * @db_has_field  true
	 * @db_fieldtype  integer
	 * @db_length     1
	 * @db_is_notnull true
	 */
	protected $is_online = 0;
	/**
	 * @var int
	 *
	 * @db_has_field  true
	 * @db_fieldtype  integer
	 * @db_length     1
	 * @db_is_notnull true
	 */
	protected $is_time_limited = 0;
	/**
	 * @var string
	 *
	 * @db_has_field        true
	 * @db_fieldtype        timestamp
	 */
	protected $start_date;
	/**
	 * @var string
	 *
	 * @db_has_field        true
	 * @db_fieldtype        timestamp
	 */
	protected $end_date;
	/**
	 * @var int
	 *
	 * @db_has_field  true
	 * @db_fieldtype  integer
	 * @db_length     1
	 * @db_is_notnull true
	 */
	protected $always_visible = 0;
	/**
	 * @var int
	 *
	 * @db_has_field  true
	 * @db_fieldtype  integer
	 * @db_length     1
	 * @db_is_notnull true
	 */
	protected $modus = 0;
	/**
	 * @var int
	 *
	 * @db_has_field  true
	 * @db_fieldtype  integer
	 * @db_length     1
	 * @db_is_notnull true
	 */
	protected $voting_enabled;

	/**
	 * @return string
	 */
	public static function returnDbTableName() {
		return 'xase_settings';
	}

	public function __construct($primary_key = 0, arConnector $connector = NULL) {
		parent::__construct($primary_key, $connector);
	}


	/**
	 * @return int
	 */
	public function getId() {
		return $this->id;
	}


	/**
	 * @param int $id
	 */
	public function setId($id) {
		$this->id = $id;
	}


	/**
	 * @return int
	 */
	public function getAssistedExerciseObjectId() {
		return $this->assisted_exercise_object_id;
	}


	/**
	 * @param int $assisted_exercise_object_id
	 */
	public function setAssistedExerciseObjectId($assisted_exercise_object_id) {
		$this->assisted_exercise_object_id = $assisted_exercise_object_id;
	}


	/**
	 * @return int
	 */
	public function getIsOnline() {
		return $this->is_online;
	}


	/**
	 * @param int $is_online
	 */
	public function setIsOnline($is_online) {
		$this->is_online = $is_online;
	}


	/**
	 * @return int
	 */
	public function getIsTimeLimited() {
		return $this->is_time_limited;
	}


	/**
	 * @param int $is_time_limited
	 */
	public function setIsTimeLimited($is_time_limited) {
		$this->is_time_limited = $is_time_limited;
	}


	/**
	 * @return string
	 */
	public function getStartDate() {
		return $this->start_date;
	}


	/**
	 * @param string $start_date
	 */
	public function setStartDate($start_date) {
		$this->start_date = $start_date;
	}


	/**
	 * @return string
	 */
	public function getEndDate() {
		return $this->end_date;
	}


	/**
	 * @param string $end_date
	 */
	public function setEndDate($end_date) {
		$this->end_date = $end_date;
	}


	/**
	 * @return int
	 */
	public function getAlwaysVisible() {
		return $this->always_visible;
	}


	/**
	 * @param int $always_visible
	 */
	public function setAlwaysVisible($always_visible) {
		$this->always_visible = $always_visible;
	}


	/**
	 * @return int
	 */
	public function getModus() {
		return $this->modus;
	}


	/**
	 * @param int $modus
	 */
	public function setModus($modus) {
		$this->modus = $modus;
	}


	/**
	 * @return int
	 */
	public function getVotingEnabled() {
		return $this->voting_enabled;
	}


	/**
	 * @param int $voting_enabled
	 */
	public function setVotingEnabled($voting_enabled) {
		$this->voting_enabled = $voting_enabled;
	}

	/**
	 * @return int
	 */
	abstract function returnInitialVotingEnabled();
	/**
	 * @return bool
	 */
	abstract function modusOffersHint();
	/**
	 * @return bool
	 */
	abstract function modusOffersSampleSolutions();
}