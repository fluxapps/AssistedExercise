<?php

/**
 * Class    ilObjAssistedExercise
 *
 * @author  Benjamin Seglias <bs@studer-raimann.ch>
 */

require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/AssistedExercise/classes/ActiveRecords/class.xaseSettings.php');
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/AssistedExercise/classes/ActiveRecords/class.xaseSettingsM1.php');
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/AssistedExercise/classes/ActiveRecords/class.xaseSettingsM2.php');
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/AssistedExercise/classes/ActiveRecords/class.xaseSettingsM3.php');

class ilObjAssistedExercise extends ilObjectPlugin {

	/**
	 * Constructor
	 *
	 * @access        public
	 *
	 * @param int $a_ref_id
	 */
	function __construct($a_ref_id = 0) {
		parent::__construct($a_ref_id);
	}


	public final function initType() {
		$this->setType(ilAssistedExercisePlugin::PLUGIN_PREFIX);
	}


	public function doCreate() {
	}


	public function doRead() {
		parent::doRead();
	}


	public function doUpdate() {
		parent::doUpdate();
	}


	public function doDelete() {
		/**
		 * @var $xaseSettings xaseSettings
		 */
		$xaseSettings = xaseSettings::getCollection()->where(array( 'assisted_exercise_object_id' => $this->getId() ), '=')->first();
		if ($xaseSettings->getModus() == 1
			&& xaseSettingsM1::getCollection()->where(array( 'settings_id' => $xaseSettings->getId() ), '=')->hasSets()) {
			$xaseSettingsM1 = xaseSettingsM1::getCollection()->where(array( 'settings_id' => $xaseSettings->getId() ), '=')->first();
			$xaseSettingsM1->delete();
		} elseif ($xaseSettings->getModus() == 3 && xaseSettingsM3::getCollection()->where(array( 'settings_id' => $xaseSettings->getId() ), '=')) {
			$xaseSettingsM3 = xaseSettingsM3::getCollection()->where(array( 'settings_id' => $xaseSettings->getId() ), '=')->first();
			$xaseSettingsM3->delete();
		} elseif ($xaseSettings->getModus() == 2 && xaseSettingsM2::getCollection()->where(array( 'settings_id' => $xaseSettings->getId() ), '=')) {
			$xaseSettingsM2 = xaseSettingsM2::getCollection()->where(array( 'settings_id' => $xaseSettings->getId() ), '=')->first();
			$xaseSettingsM2->delete();
		}
		$xaseSettings->delete();
	}

	/**
	 * @param ilObjAssistedExercise $new_obj Instance of
	 * @param int                   $a_target_id obj_id of the new created object
	 * @param int                   $a_copy_id
	 *
	 * @return bool|void
	 */
	public function doCloneObject($new_obj, $a_target_id, $a_copy_id = NULL) {
		assert(is_a($new_obj, ilObjAssistedExercise::class));
	}
}