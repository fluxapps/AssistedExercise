<?php
require_once "./Customizing/global/plugins/Services/Repository/RepositoryObject/AssistedExercise/classes/Setting/class.xaseSettingAbstract.php";
/**
 * Class xaseSettingM2
 *
 * @author  Benjamin Seglias <bs@studer-raimann.ch>
 */

class xaseSettingM2 extends xaseSettingAbstract {

	/**
	 * @var int
	 *
	 * @db_has_field  true
	 * @db_fieldtype  integer
	 * @db_length     1
	 * @db_is_notnull true
	 */
	protected $modus = xaseSettingAbstract::MODUS2;
	/**
	 * @var int
	 *
	 * @db_has_field  true
	 * @db_fieldtype  integer
	 * @db_length     1
	 * @db_is_notnull true
	 */
	protected $voting_enabled = 1;

	/**
	 * @return int
	 */
	public function returnInitialVotingEnabled() {
		return 1;
	}

	function modusOffersHint() {
		return false;
	}


	function modusOffersSampleSolutions() {
		return false;
	}
}