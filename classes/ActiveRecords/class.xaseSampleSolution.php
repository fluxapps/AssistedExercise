<?php
/**
 * Class xaseSampleSolution
 *
 * @author  Benjamin Seglias <bs@studer-raimann.ch>
 */

class xaseSampleSolution extends ActiveRecord {

	/**
	 * @return string
	 */
	public static function returnDbTableName() {
		return 'rep_robj_xase_samp_sol';
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
	 * @var string
	 *
	 * @db_has_field        true
	 * @db_fieldtype        text
	 * @db_length           4000
	 * @db_is_notnull       true
	 */
	protected $solution;


	/**
	 * @return int
	 */
	public function getId() {
		return $this->id;
	}


	/**
	 * @return string
	 */
	public function getSolution() {
		return $this->solution;
	}


	/**
	 * @param string $solution
	 */
	public function setSolution($solution) {
		$this->solution = $solution;
	}
}