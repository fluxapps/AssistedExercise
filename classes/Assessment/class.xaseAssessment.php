<?php
/**
 * Class xaseAssessment
 *
 * @author: Benjamin Seglias   <bs@studer-raimann.ch>
 */

class xaseAssessment extends ActiveRecord {

	/**
	 * @return string
	 */
	public static function returnDbTableName() {
		return 'xase_assessm';
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
	protected $user_id;
	/**
	 * @var int
	 *
	 * @db_has_field  true
	 * @db_fieldtype  integer
	 * @db_length     4
	 * @db_is_notnull true
	 */
	protected $answer_id;
	/**
	 * @var int
	 *
	 * @db_has_field  true
	 * @db_fieldtype  integer
	 * @db_length     4
	 * @db_is_notnull true
	 */
	protected $question_id;
	/**
	 * @var string
	 *
	 * @db_has_field        true
	 * @db_fieldtype        text
	 * @db_length           4000
	 * @db_is_notnull       false
	 */
	protected $assessment_comment;
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
	protected $minus_points;


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
	public function getAnswerId() {
		return $this->answer_id;
	}


	/**
	 * @param int $answer_id
	 */
	public function setAnswerId($answer_id) {
		$this->answer_id = $answer_id;
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
	 * @return string
	 */
	public function getAssessmentComment() {
		return $this->assessment_comment;
	}


	/**
	 * @param string $assessment_comment
	 */
	public function setAssessmentComment($assessment_comment) {
		$this->assessment_comment = $assessment_comment;
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
	 * @return int
	 */
	public function getAdditionalPoints() {
		return $this->additional_points;
	}


	/**
	 * @param int $additional_points
	 */
	public function setAdditionalPoints($additional_points) {
		$this->additional_points = $additional_points;
	}


	/**
	 * @return int
	 */
	public function getTotalPoints() {
		return $this->total_points;
	}


	/**
	 * @param int $total_points
	 */
	public function setTotalPoints($total_points) {
		$this->total_points = $total_points;
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