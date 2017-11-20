<?php
/**
 * Class xaseAnswer
 *
 * @author  Benjamin Seglias <bs@studer-raimann.ch>
 */

class xaseAnswer extends ActiveRecord {

	const ANSWER_STATUS_OPEN = 0;
	const ANSWER_STATUS_ANSWERED = 1;
	const ANSWER_STATUS_CAN_BE_VOTED = 2;


	/**
	 * @return string
	 */
	public static function returnDbTableName() {
		return 'xase_answer';
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
	 * @var string
	 *
	 * @db_has_field        true
	 * @db_fieldtype        text
	 * @db_length           4000
	 * @db_is_notnull       true
	 */
	protected $answer_text;
	/**
	 * @var string
	 *
	 * @db_has_field        true
	 * @db_fieldtype        timestamp
	 */
	protected $submission_date;
	/**
	 * @var int
	 *
	 * @db_has_field  true
	 * @db_fieldtype  integer
	 * @db_length     1
	 * @db_is_notnull false
	 */
	protected $answer_status;
	/**
	 * @var int
	 *
	 * @db_has_field  true
	 * @db_fieldtype  integer
	 * @db_length     1
	 * @db_is_notnull false
	 */
	protected $is_assessed;
	/**
	 * @var int
	 *
	 * @db_has_field  true
	 * @db_fieldtype  integer
	 * @db_length     4
	 * @db_is_notnull false
	 */
	//protected $number_of_upvotings;
	/**
	 * @var int
	 *
	 * @db_has_field  true
	 * @db_fieldtype  integer
	 * @db_length     4
	 * @db_is_notnull true
	 */
	protected $question_severity_rating;


	/**
	 * @return int
	 */
	public function getId() {
		return $this->id;
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
	 * @return string
	 */
	public function getAnswertext() {
		return $this->answer_text;
	}


	/**
	 * @param string $answer_text
	 */
	public function setAnswertext($answer_text) {
		$this->answer_text = $answer_text;
	}


	/**
	 * @return string
	 */
	public function getSubmissionDate() {
		return $this->submission_date;
	}


	/**
	 * @param string $submission_date
	 */
	public function setSubmissionDate($submission_date) {
		$this->submission_date = $submission_date;
	}


	/**
	 * @return string
	 */
	public function getAnswerStatus() {
		return $this->answer_status;
	}


	/**
	 * @param string $answer_status
	 */
	public function setAnswerStatus($answer_status) {
		$this->answer_status = $answer_status;
	}


	/**
	 * @return int
	 */
	public function getisAssessed() {
		return $this->is_assessed;
	}


	/**
	 * @param int $is_assessed
	 */
	public function setIsAssessed($is_assessed) {
		$this->is_assessed = $is_assessed;
	}


	/**
	 * @return int
	 */
	public function getQuestionSeverityRating() {
		return $this->question_severity_rating;
	}


	/**
	 * @param int $question_severity_rating
	 */
	public function setQuestionSeverityRating($question_severity_rating) {
		$this->question_severity_rating = $question_severity_rating;
	}



	//Other
	public function returnNumberOfUpvotings() {
		return xaseVoting::where(array('answer_id' => $this->getId(), 'voting_type' => xaseVoting::VOTING_TYPE_UP))->count();
	}


	/**
	 * @return xaseQuestion
	 */
	public function returnQuestion() {
		return xaseQuestion::findOrGetInstance($this->getQuestionId());
	}
}