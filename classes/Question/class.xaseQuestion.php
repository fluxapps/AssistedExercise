<?php
/**
 * Class xaseQuestion
 *
 * @author  Benjamin Seglias <bs@studer-raimann.ch>
 */

class xaseQuestion extends ActiveRecord {

	const SEVERITY_RATING_FROM = 1;
	const SEVERITY_RATING_TO = 5;

	/**
	 * @return string
	 */
	public static function returnDbTableName() {
		return 'xase_question';
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
	protected $assisted_exercise_id;
	/**
	 * @var string
	 *
	 * @db_has_field        true
	 * @db_fieldtype        text
	 * @db_length           256
	 * @db_is_notnull       true
	 */
	protected $title;
	/**
	 * @var string
	 *
	 * @db_has_field        true
	 * @db_fieldtype        text
	 * @db_length           4000
	 * @db_is_notnull       true
	 */
	protected $question_text;
	/**
	 * @var int
	 *
	 * @db_has_field  true
	 * @db_fieldtype  integer
	 * @db_length     4
	 * @db_is_notnull true
	 */
	protected $created_by;
	/**
	 * @var int
	 *
	 * @db_has_field  true
	 * @db_fieldtype  integer
	 * @db_length     4
	 * @db_is_notnull true
	 */
	protected $max_points = 0;
	/**
	 * @var string
	 *
	 * @db_has_field        true
	 * @db_fieldtype        text
	 * @db_length           4000
	 * @db_is_notnull       false
	 */
	protected $sample_solution;
	/**
	 * @var xaseHint[]
	 */
	protected $hints;


	/**
	 * @return int
	 */
	public function getCreatedBy() {
		return $this->created_by;
	}


	/**
	 * @param int $created_by
	 */
	public function setCreatedBy($created_by) {
		$this->created_by = $created_by;
	}

	/**
	 * @return int
	 */
	public function getId() {
		return $this->id;
	}


	/**
	 * @return int
	 */
	public function getAssistedExerciseId() {
		return $this->assisted_exercise_id;
	}


	/**
	 * @param int $assisted_exercise_id
	 */
	public function setAssistedExerciseId($assisted_exercise_id) {
		$this->assisted_exercise_id = $assisted_exercise_id;
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
	 * @return string
	 */
	public function getTitle() {
		return $this->title;
	}


	/**
	 * @param string $title
	 */
	public function setTitle($title) {
		$this->title = $title;
	}


	/**
	 * @return string
	 */
	public function getQuestiontext() {
		return $this->question_text;
	}


	/**
	 * @param string $question_text
	 */
	public function setQuestiontext($question_text) {
		$this->question_text = $question_text;
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
	 * @return string
	 */
	public function getSampleSolution() {
		return $this->sample_solution;
	}


	/**
	 * @param string $sample_solution
	 */
	public function setSampleSolution($sample_solution) {
		$this->sample_solution = $sample_solution;
	}


	//Other
	public function returnItemSeverityRatingAverage() {
		global $ilDB;

		$sql = "select ROUND(AVG(item_severity_rating),1) as item_severity_rating_avg  
				from xase_answer where question_id = ".$ilDB->quote($this->getId(), 'integer')." and item_severity_rating > 0";

		$set = $ilDB->query($sql);
		//return the first result. should be only one
		while($row = $ilDB->fetchAssoc($set)) {
			return $row['item_severity_rating_avg'];
		}

		return false;
	}


	public function afterObjectLoad() {
		$this->setHints(xaseHint::where(array('question_id' => $this->getId()))->get());
	}


	/**
	 * @return xaseHint[]
	 */
	public function getHints() {
		return $this->hints;
	}

	/**
	 * @param xaseHint[] $hints
	 */
	public function setHints($hints) {
		$this->hints = $hints;
	}



	public function store() {
		parent::store();

		if(count($this->getHints()) > 0) {
			foreach($this->getHints() as $hint) {
				$hint->setQuestionId($this->getId());
				$hint->store();
			}
		}
	}
}