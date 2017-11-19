<?php
/**
 * Class xaseHintLevel
 *
 * @author  Martin Studer <ms@studer-raimann.ch>
 */

class xaseHintLevel extends ActiveRecord {

	/**
	 * @return string
	 */
	public static function returnDbTableName() {
		return 'xase_hint_level';
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
	protected $hint_id;
	/**
	 * @var int
	 *
	 * @db_has_field  true
	 * @db_fieldtype  integer
	 * @db_length     4
	 * @db_is_notnull true
	 */
	protected $minus_points;
	/**
	 * @var int
	 *
	 * @db_has_field  true
	 * @db_fieldtype  integer
	 * @db_length     1
	 * @db_is_notnull true
	 */
	protected $hint_level;
	/**
	 * @var string
	 *
	 * @db_has_field        true
	 * @db_fieldtype        text
	 * @db_length           4000
	 * @db_is_notnull       true
	 */
	protected $hint_text;


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
	public function getHintId() {
		return $this->hint_id;
	}


	/**
	 * @param int $hint_id
	 */
	public function setHintId($hint_id) {
		$this->hint_id = $hint_id;
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


	/**
	 * @return int
	 */
	public function getHintLevel() {
		return $this->hint_level;
	}


	/**
	 * @param int $hint_level
	 */
	public function setHintLevel($hint_level) {
		$this->hint_level = $hint_level;
	}

	/**
	 * @return string
	 */
	public function getHintText() {
		return $this->hint_text;
	}


	/**
	 * @param string $hint_text
	 */
	public function setHintText($hint_text) {
		$this->hint_text = $hint_text;
	}
}