<?php
/**
 * Class xaseComment
 *
 * @author  Benjamin Seglias <bs@studer-raimann.ch>
 */

class xaseComment extends ActiveRecord {

	/**
	 * @return string
	 */
	public static function returnDbTableName() {
		return 'xase_comment';
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
	protected $answer_id;
	/**
	 * @var string
	 *
	 * @db_has_field        true
	 * @db_fieldtype        text
	 * @db_length           4000
	 * @db_is_notnull       true
	 */
	protected $body;
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
	 * @return int
	 */
	public function getId() {
		return $this->id;
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
	 * @return string
	 */
	public function getBody() {
		return $this->body;
	}


	/**
	 * @param string $body
	 */
	public function setBody($body) {
		$this->body = $body;
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