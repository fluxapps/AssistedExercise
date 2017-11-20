<?php
require_once 'class.xaseHintLevel.php';
/**
 * Class xaseHint
 *
 * @author  Benjamin Seglias <bs@studer-raimann.ch>
 */

class xaseHint extends ActiveRecord {

	/**
	 * @return string
	 */
	public static function returnDbTableName() {
		return 'xase_hint';
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
	protected $question_id;
	/**
	 * @var int
	 *
	 * @db_has_field  true
	 * @db_fieldtype  integer
	 * @db_length     4
	 * @db_is_notnull true
	 */
	protected $hint_number;
	/**
	 * @var int
	 *
	 * @db_has_field  true
	 * @db_fieldtype  integer
	 * @db_length     1
	 * @db_is_notnull true
	 */
	protected $is_template;
	/**
	 * @var string
	 *
	 * @db_has_field        true
	 * @db_fieldtype        text
	 * @db_length           600
	 * @db_is_notnull       true
	 */
	protected $label;
	/**
	 * @var xaseHintLevel[]
	 */
	protected $hint_levels;


	/**
	 * @return int
	 */
	public function getId() {
		return $this->id;
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
	public function getHintNumber() {
		return $this->hint_number;
	}


	/**
	 * @param int $hint_number
	 */
	public function setHintNumber($hint_number) {
		$this->hint_number = $hint_number;
	}


	/**
	 * @return int
	 */
	public function getisTemplate() {
		return $this->is_template;
	}


	/**
	 * @param int $is_template
	 */
	public function setIsTemplate($is_template) {
		$this->is_template = $is_template;
	}


	/**
	 * @return string
	 */
	public function getLabel() {
		return $this->label;
	}


	/**
	 * @param string $label
	 */
	public function setLabel($label) {
		$this->label = $label;
	}

	public function afterObjectLoad() {
		$this->setHintLevels(xaseHintLevel::where(array('hint_id' => $this->getId()))->get());
	}

	/**
	 * @return xaseHintLevel[]
	 */
	public function getHintLevels() {
		return $this->hint_levels;
	}


	/**
	 * @param xaseHintLevel[] $hint_levels
	 */
	public function setHintLevels($hint_levels) {
		$this->hint_levels = $hint_levels;
	}

	public function store() {


		parent::store();

		xaseHintLevel::where(array('hint_id' => $this->getId()))->getAR()->delete();

		if(count($this->getHintLevels()) > 0) {
			foreach($this->getHintLevels() as $hintlevel) {
				$hintlevel->setHintId($this->getId());
				$hintlevel->store();
			}
		}
	}
}