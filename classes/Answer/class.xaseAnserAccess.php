<?php
require_once "./Customizing/global/plugins/Services/Repository/RepositoryObject/AssistedExercise/classes/ItemAbstract/class.xaseItemAbstractAccess.php";
require_once "./Customizing/global/plugins/Services/Repository/RepositoryObject/AssistedExercise/classes/class.ilObjAssistedExerciseAccess.php";

/**
 * Class xaseAnswerAccess
 *
 * @author  Martin Studer <ms@studer-raimann.ch>
 */

class xaseAnswerAccess extends xaseItemAbstractAccess {

	/**
	 * @var xaseAnswer
	 */
	protected $item;
	/**
	 * @var ilObjAssistedExerciseFacade
	 */
	protected $obj_facade;
	/**
	 * @var int
	 */
	protected $il_user_id;
	/**
	 * @var ilObjAssistedExerciseAccess
	 */
	protected $global_access;


	function hasReadAccess() {
		if($this->global_access->hasWriteAccess()) {
			return true;
		}

		if(!self::isTimeLimitRespected($this->obj_facade)) {
			return false;
		}

		if(!$this->obj_facade->getSetting()->getIsOnline()) {
			return false;
		}

		if($this->item->getUserId() == $this->il_user_id) {
			return true;
		}
	}


	function hasWriteAccess() {

		if(!$this->hasReadAccess()) {
			return false;
		}

		if(!self::isDisposalLimitRespected($this->obj_facade)) {
			return false;
		}

		switch($this->item->getAnswerStatus()) {
			case xaseAnswer::ANSWER_STATUS_OPEN:
			case xaseAnswer::ANSWER_STATUS_ANSWERED:
				if($this->item->getUserId() == $this->il_user_id) {
					return true;
				}
			break;
		}


		return false;
	}


	function hasDeleteAccess() {
		return false;
	}

	/**
	 * @param ilObjAssistedExerciseFacade $obj_facade
	 * @param int $il_user_id
	 * @param int $question_id
	 *
	 * @return bool
	 */
	static function hasCreateAccess(ilObjAssistedExerciseFacade $obj_facade,$il_user_id,$question_id = 0) {

		//falls der benuztzer bereits antworten hat, keine rechte für neue antworten!
		if(xaseAnswer::where(array('question_id' => $question_id,'user_id' => $il_user_id))->count() > 0) {
			return false;
		}


		$global_access = ilObjAssistedExerciseAccess::getInstance($obj_facade,$il_user_id);

		if(!self::isDisposalLimitRespected($obj_facade)) {
			return false;
		}

		if(!self::isTimeLimitRespected($obj_facade)) {
			return false;
		}

		if(!$obj_facade->getSetting()->getIsOnline()) {
			return false;
		}

		return true;
	}
}