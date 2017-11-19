<?php
/**
 * Class xasePoint
 *
 * @author  Benjamin Seglias <bs@studer-raimann.ch>
 */

class xasePoint extends ActiveRecord {

	/**
	 * @return string
	 */
	public static function returnDbTableName() {
		return 'xase_point';
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
	 * @db_is_notnull false
	 */
	protected $user_id;
	/**
	 * @var int
	 *
	 * @db_has_field  true
	 * @db_fieldtype  integer
	 * @db_length     4
	 * @db_is_notnull false
	 */
	protected $question_id;
	/**
	 * @var int
	 *
	 * @db_has_field  true
	 * @db_fieldtype  integer
	 * @db_length     4
	 * @db_is_notnull false
	 */
	protected $max_points;
	/**
	 * @var int
	 *
	 * @db_has_field  true
	 * @db_fieldtype  float
	 * @db_length     4
	 * @db_is_notnull false
	 */
	protected $total_points;
	/**
	 * @var int
	 *
	 * @db_has_field  true
	 * @db_fieldtype  integer
	 * @db_length     4
	 * @db_is_notnull false
	 */
	protected $points_teacher;
	/**
	 * @var int
	 *
	 * @db_has_field  true
	 * @db_fieldtype  float
	 * @db_length     4
	 * @db_is_notnull false
	 */
	protected $additional_points;
	/**
	 * @var int
	 *
	 * @db_has_field  true
	 * @db_fieldtype  integer
	 * @db_length     4
	 * @db_is_notnull false
	 */
	protected $minus_points;

	/**
	 * @return int
	 */
	public function getId() {
		return $this->id;
	}


	/**
	 * @return int
	 */
	public function getPointId() {
		return $this->point_id;
	}


	/**
	 * @param int $point_id
	 */
	public function setPointId($point_id) {
		$this->point_id = $point_id;
	}


	/**
	 * @return int
	 */
	public function getUserId() {
		return $this->user_id;
	}


	/**
	 * @param int $user_id
	 */
	public function setUserId($user_id) {
		$this->user_id = $user_id;
	}


	/**
	 * @return int
	 */
	public function getQuestionId() {
		return $this->question_id;
	}


	/**
	 * @param int $question_id
	 */
	public function setQuestionId($question_id) {
		$this->question_id = $question_id;
	}


	/**
	 * @return int
	 */
	public function getMaxPoints() {
		return $this->max_points;
	}


	/**
	 * @param int $max_points
	 */
	public function setMaxPoints($max_points) {
		$this->max_points = $max_points;
	}


	/**
	 * @return float
	 */
	public function getTotalPoints() {
		return $this->total_points;
	}


	/**
	 * @param float $total_points
	 */
	public function setTotalPoints($total_points) {
		$this->total_points = $total_points;
	}


	/**
	 * @return int
	 */
	public function getPointsTeacher() {
		return $this->points_teacher;
	}


	/**
	 * @param int $points_teacher
	 */
	public function setPointsTeacher($points_teacher) {
		$this->points_teacher = $points_teacher;
	}


	/**
	 * @return float
	 */
	public function getAdditionalPoints() {
		return $this->additional_points;
	}


	/**
	 * @param float $additional_points
	 */
	public function setAdditionalPoints($additional_points) {
		$this->additional_points = $additional_points;
	}


	/**
	 * @return int
	 */
	public function getMinusPoints() {
		return $this->minus_points;
	}


	/**
	 * @param int $minus_points
	 */
	public function setMinusPoints($minus_points) {
		$this->minus_points = $minus_points;
	}
}