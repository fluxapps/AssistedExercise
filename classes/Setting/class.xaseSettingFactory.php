<?php
require_once "./Customizing/global/plugins/Services/Repository/RepositoryObject/AssistedExercise/classes/Setting/class.xaseSetting.php";
require_once "./Customizing/global/plugins/Services/Repository/RepositoryObject/AssistedExercise/classes/Setting/class.xaseSettingM1.php";
require_once "./Customizing/global/plugins/Services/Repository/RepositoryObject/AssistedExercise/classes/Setting/class.xaseSettingM2.php";

/**
 * Class xaseSetting
 *
 * @author  Martin Studer <ms@studer-raimann.ch>
 */

class xaseSettingFactory {

	/**
	 * @param int $id
	 * @param int $modus
	 *
	 * @return xaseSettingM1|xaseSettingM2
	 */
	private static function getSetting($id, $modus) {

		switch($modus) {
			case xaseSetting::MODUS1:
				return 	xaseSettingM1::findOrGetInstance($id);
				break;
			case xaseSetting::MODUS2:
				return 	xaseSettingM2::findOrGetInstance($id);
				break;
		}
	}

	/**
	 * @param int $assisted_exercise_ref_id
	 *
	 * @description Returns an existing Object with given ref_id or a new Instance with object_id set but not yet created
	 *
	 * @return xaseSettingM1|xaseSettingM2|xaseSettingM3
	 */
	public static function findOrGetInstanceByRefId($assisted_exercise_ref_id) {

		$assisted_exercise_object_id = ilObject2::_lookupObjId($assisted_exercise_ref_id);

		return self::findOrGetInstanceByObjId($assisted_exercise_object_id);
	}

	/**
	 * @param int $assisted_exercise_object_id
	 *
	 * @description Returns an existing Object with given object_id
	 * or a new Instance of xaseSettingM1 with given object_id set but not yet created
	 *
	 * @return xaseSettingM1|xaseSettingM2|xaseSettingM3
	 */
	public static function findOrGetInstanceByObjId($assisted_exercise_object_id) {
		/**
		 * @var xaseSetting $obj
		 */
		$obj = xaseSetting::where(array('assisted_exercise_object_id' => $assisted_exercise_object_id))->first();

		if (is_object($obj)) {
			return self::getSetting($obj->getId(),$obj->getModus());
		} else {
			$obj = new xaseSettingM1(0);
			$obj->setAssistedExerciseObjectId($assisted_exercise_object_id);
			return $obj;
		}
	}
}