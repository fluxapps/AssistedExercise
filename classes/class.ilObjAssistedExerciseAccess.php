<?php

/**
 * Class    ilObjAssistedExerciseAccess
 *
 * @author  Benjamin Seglias <bs@studer-raimann.ch>
 * @author  Martin Studer <ms@studer-raimann.ch>
 *
 */
require_once('./Services/Repository/classes/class.ilObjectPluginAccess.php');

class ilObjAssistedExerciseAccess extends ilObjectPluginAccess {

	/**
	 * @var ilObjAssistedExerciseFacade
	 */
	protected $obj_facade;
	/**
	 * @var int
	 */
	protected $il_user_id;


	/**
	 * ilObjAssistedExerciseAccess constructor.
	 *
	 * @param ilObjAssistedExerciseFacade $obj_facade
	 * @param int                         $il_user_id
	 */
	public function __construct() {
	}


	/**
	 * ilObjAssistedExerciseAccess constructor.
	 *
	 * @param ilObjAssistedExerciseFacade $obj_facade
	 * @param int                         $il_user_id
	 *
	 * @return ilObjAssistedExerciseAccess
	 */
	public static function getInstance($obj_facade, $il_user_id = 0) {
		$access = new self();
		$access->obj_facade = $obj_facade;
		$access->il_user_id = $il_user_id;

		return $access;
	}


	public function hasReadAccess() {
		if ($this->obj_facade == 0 || $this->il_user_id == 0) {
			return false;
		}

		if($this->hasWriteAccess()) {
			return true;
		}

		if (!self::isTimeLimitRespected($this->obj_facade)) {
			return false;
		}

		if(!self::checkOnline($this->obj_facade->getIlObjObId())) {
			return false;
		}

		return $this->obj_facade->getDic()->access()->checkAccessOfUser($this->il_user_id, 'read', '', $this->obj_facade->getIlObjRefId());
	}


	public function hasWriteAccess() {
		return $this->obj_facade->getDic()->access()->checkAccessOfUser($this->il_user_id, 'write', '', $this->obj_facade->getIlObjRefId());
	}


	public function hasDeleteAccess() {
		return $this->obj_facade->getDic()->access()->checkAccessOfUser($this->il_user_id, 'delete', '', $this->obj_facade->getIlObjRefId());
	}


	/**
	 * @param $a_id
	 *
	 * @return bool
	 */
	public static function checkOnline($a_id) {
		global $ilDB;
		//return true;
		$set = $ilDB->query('SELECT is_online FROM xase_settings WHERE assisted_exercise_object_id = ' . $ilDB->quote($a_id, 'integer'));
		$rec = $ilDB->fetchAssoc($set);

		return (boolean)$rec['is_online'];
	}


	public static function isTimeLimitRespected($obj_facade) {
		if ($obj_facade->getSetting()->getIsTimeLimited()) {
			$current_date = date('Y-m-d h:i:s', time());
			$current_date_datetime = DateTime::createFromFormat('Y-m-d H:i:s', $current_date);

			$start_date = DateTime::createFromFormat('Y-m-d H:i:s', $obj_facade->getSetting()->getStartDate());
			if ($current_date_datetime->getTimestamp() < $start_date->getTimestamp()) {
				return false;
			}

			$end_date = DateTime::createFromFormat('Y-m-d H:i:s', $obj_facade->getSetting()->getEndDate());
			if ($current_date_datetime->getTimestamp() > $end_date->getTimestamp()) {
				return false;
			}
		}

		return true;
	}


	/**
	 * @param ilObjAssistedExerciseFacade $obj_facade
	 *
	 * @return bool
	 */
	public static function isDisposalLimitRespected($obj_facade) {
		if ($obj_facade->getSetting()->getRateAnswers()) {
			$current_date = date('Y-m-d h:i:s', time());
			$current_date_datetime = DateTime::createFromFormat('Y-m-d H:i:s', $current_date);

			$end_date = DateTime::createFromFormat('Y-m-d H:i:s', $obj_facade->getSetting()->getDisposalDate());

			if ($current_date_datetime->getTimestamp() > $end_date->getTimestamp()) {
				return false;
			}
		}

		return true;
	}
}