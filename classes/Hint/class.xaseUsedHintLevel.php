<?php
class xaseUsedHintLevel extends ActiveRecord {

	/**
	 * @return string
	 */
	public static function returnDbTableName() {
		return 'xase_used_hint_level';
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
	protected $hint_level_id;
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
	 * @var int
	 *
	 * @db_has_field  true
	 * @db_fieldtype  integer
	 * @db_length     4
	 * @db_is_notnull false
	 */
	protected $user_id;


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
	public function getHintLevelId() {
		return $this->hint_level_id;
	}


	/**
	 * @param int $hint_level_id
	 */
	public function setHintLevelId($hint_level_id) {
		$this->hint_level_id = $hint_level_id;
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
	public function getUserId() {
		return $this->user_id;
	}


	/**
	 * @param int $user_id
	 */
	public function setUserId($user_id) {
		$this->user_id = $user_id;
	}
}