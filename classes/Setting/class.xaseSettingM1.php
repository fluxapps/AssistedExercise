<?php
require_once "./Customizing/global/plugins/Services/Repository/RepositoryObject/AssistedExercise/classes/Setting/class.xaseSettingAbstract.php";
/**
 * Class xaseSettingM1
 *
 * @author  Benjamin Seglias <bs@studer-raimann.ch>
 */

class xaseSettingM1 extends xaseSettingAbstract {

	/**
	 * @var int
	 *
	 * @db_has_field  true
	 * @db_fieldtype  integer
	 * @db_length     1
	 * @db_is_notnull true
	 */
	protected $modus = xaseSettingAbstract::MODUS1;
	/**
	 * @var int
	 *
	 * @db_has_field  true
	 * @db_fieldtype  integer
	 * @db_length     1
	 * @db_is_notnull true
	 */
	protected $voting_enabled = 0;

	/**
	 * @var int
	 *
	 * @db_has_field  true
	 * @db_fieldtype  integer
	 * @db_length     1
	 * @db_is_notnull true
	 */
	protected $rate_answers = 0;
	/**
	 * @var string
	 *
	 * @db_has_field        true
	 * @db_fieldtype        timestamp
	 */
	protected $disposal_date;
	/**
	 * @var int
	 *
	 * @db_has_field  true
	 * @db_fieldtype  integer
	 * @db_length     1
	 * @db_is_notnull true
	 */
	protected $sample_solution_visible = 0;
	/**
	 * @var int
	 *
	 * @db_has_field  true
	 * @db_fieldtype  integer
	 * @db_length     1
	 * @db_is_notnull true
	 */
	protected $visible_if_exercise_finished = 0;
	/**
	 * @var string
	 *
	 * @db_has_field        true
	 * @db_fieldtype        timestamp
	 */
	protected $solution_visible_date;
	/**
	 * @var int
	 *
	 * @db_has_field  true
	 * @db_fieldtype  integer
	 * @db_length     1
	 * @db_is_notnull true
	 */
	protected $voting_points_enabled = 0;
	/**
	 * @var int
	 *
	 * @db_has_field  true
	 * @db_fieldtype  integer
	 * @db_length     1
	 * @db_is_notnull true
	 */
	protected $voting_points_percentage = 0;





	/**
	 * @return int
	 */
	public function getRateAnswers() {
		return $this->rate_answers;
	}


	/**
	 * @param int $rate_answers
	 */
	public function setRateAnswers($rate_answers) {
		$this->rate_answers = $rate_answers;
	}


	/**
	 * @return string
	 */
	public function getDisposalDate() {
		return $this->disposal_date;
	}


	/**
	 * @param string $disposal_date
	 */
	public function setDisposalDate($disposal_date) {
		$this->disposal_date = $disposal_date;
	}


	/**
	 * @return int
	 */
	public function getSampleSolutionVisible() {
		return $this->sample_solution_visible;
	}


	/**
	 * @param int $sample_solution_visible
	 */
	public function setSampleSolutionVisible($sample_solution_visible) {
		$this->sample_solution_visible = $sample_solution_visible;
	}


	/**
	 * @return int
	 */
	public function getVisibleIfExerciseFinished() {
		return $this->visible_if_exercise_finished;
	}


	/**
	 * @param int $visible_if_exercise_finished
	 */
	public function setVisibleIfExerciseFinished($visible_if_exercise_finished) {
		$this->visible_if_exercise_finished = $visible_if_exercise_finished;
	}


	/**
	 * @return string
	 */
	public function getSolutionVisibleDate() {
		return $this->solution_visible_date;
	}


	/**
	 * @param string $solution_visible_date
	 */
	public function setSolutionVisibleDate($solution_visible_date) {
		$this->solution_visible_date = $solution_visible_date;
	}


	/**
	 * @return int
	 */
	public function getVotingPointsEnabled() {
		return $this->voting_points_enabled;
	}


	/**
	 * @param int $voting_points_enabled
	 */
	public function setVotingPointsEnabled($voting_points_enabled) {
		$this->voting_points_enabled = $voting_points_enabled;
	}


	/**
	 * @return int
	 */
	public function getVotingPointsPercentage() {
		return $this->voting_points_percentage;
	}


	/**
	 * @param int $voting_points_percentage
	 */
	public function setVotingPointsPercentage($voting_points_percentage) {
		$this->voting_points_percentage = $voting_points_percentage;
	}


	/**
	 * @return int
	 */
	public function returnInitialVotingEnabled() {
		return 0;
	}


	function modusOffersHint() {
		return true;
	}


	function modusOffersSampleSolutions() {
		return true;
	}


	public function store() {

		//Reset Setting M2
		//nothing to do!

		parent::store();
	}
}