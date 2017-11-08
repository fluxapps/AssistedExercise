<?php
/**
 * Class xaseVoting
 *
 * @author  Benjamin Seglias <bs@studer-raimann.ch>
 */

class xaseVoting extends ActiveRecord {

	/**
	 * @return string
	 */
	public static function returnDbTableName() {
		return 'rep_robj_xase_voting';
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
	protected $item_id;
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
	 * @return int
	 */
	public function getItemId() {
		return $this->item_id;
	}


	/**
	 * @param int $item_id
	 */
	public function setItemId($item_id) {
		$this->item_id = $item_id;
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