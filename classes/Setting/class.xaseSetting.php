<?php
require_once "./Customizing/global/plugins/Services/Repository/RepositoryObject/AssistedExercise/classes/Setting/class.xaseSettingAbstract.php";

/**
 * Class xaseSetting
 *
 * @author  Benjamin Seglias <bs@studer-raimann.ch>
 */

class xaseSetting extends xaseSettingAbstract {

	/**
	 * @return int
	 */
	public function returnInitialVotingEnabled() {
		return 0;
	}

	function modusOffersHint() {
		return false;
	}

	function modusOffersSampleSolutions() {
		return false;
	}
}