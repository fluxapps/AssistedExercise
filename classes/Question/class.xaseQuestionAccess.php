<?php
require_once "./Customizing/global/plugins/Services/Repository/RepositoryObject/AssistedExercise/classes/ItemAbstract/class.xaseItemAbstractAccess.php";
require_once "./Customizing/global/plugins/Services/Repository/RepositoryObject/AssistedExercise/classes/class.ilObjAssistedExerciseAccess.php";

/**
 * Class xaseQuestionAccess
 *
 * @author  Martin Studer <ms@studer-raimann.ch>
 */

class xaseQuestionAccess extends xaseItemAbstractAccess {

	/**
	 * @var xaseQuestion
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

		return true;
	}


	function hasWriteAccess() {

		if(self::hasCreateAccess($this->obj_facade,$this->il_user_id)) {

			if(xaseAnswer::where(array('question_id' => $this->item->getId()))->count()) {
				return false;
			}

			if(xaseUsedHintLevel::where(array('question_id' => $this->item->getId()))->count()) {
				return false;
			}

			if($this->global_access->hasWriteAccess()) {
				return true;
			}

			if($this->item->getUserId() == $this->il_user_id) {
				return true;
			}
		}

		return false;
	}

	function hasAccessToSampleSolution() {
		if(!$this->hasReadAccess()) {
			return false;
		}

		if(!$this->obj_facade->getSetting()->modusOffersSampleSolutions()) {
			return false;
		}

		if(!$this->obj_facade->getSetting()->getSampleSolutionVisible()) {
			return false;
		}

		//TODO Refactor because of nummeric value
		if($this->obj_facade->getSetting()->getVisibleIfExerciseFinished() == 1) {
			if(xaseAnswer::where(array('answer_status' => xaseAnswer::ANSWER_STATUS_CAN_BE_VOTED, 'user_id' => $this->il_user_id, 'question_id' => $this->item->getId() ))->count() > 0) {
				return true;
			}
		}

		//TODO Refactor because of nummeric value
		if($this->obj_facade->getSetting()->getVisibleIfExerciseFinished() == 2) {

			$current_date = date('Y-m-d h:i:s', time());
			$current_date_datetime = DateTime::createFromFormat('Y-m-d H:i:s', $current_date);

			$start_date = DateTime::createFromFormat('Y-m-d H:i:s', $this->obj_facade->getSetting()->getSolutionVisibleDate());

			if($current_date_datetime->getTimestamp() > $start_date->getTimestamp()) {
				return true;
			}
		}

		return false;

	}

	function hasVotingAccess() {

			if(!$this->hasReadAccess()) {
				return false;
			}

			if(!self::isDisposalLimitRespected($this->obj_facade)) {
				return false;
			}

			if(count(xaseVotings::getUnvotedAnswersOfUser($this->obj_facade->getIlObjObId(), $this->obj_facade->getUser()->getId(), $this->item->getId())) >= 2) {

				if($this->obj_facade->getSetting()->getVotingEnabled()) {
					return true;
				}

			}

			return false;
	}

	function hasVotingDeleteAccess() {

		if(!self::isTimeLimitRespected($this->obj_facade)) {
			return false;
		}

		if(!$this->obj_facade->getSetting()->getIsOnline()) {
			return false;
		}

		if(!self::isDisposalLimitRespected($this->obj_facade)) {
			return false;
		}

		$voted = xaseVoting::where(array('user_id' => $this->obj_facade->getUser()->getId(), 'question_id' => $this->item->getId()))->first();

		if(is_object($voted)) {
			return true;
		}

		return false;
	}

	function hasDeleteAccess() {
		return $this->hasWriteAccess();
	}

	/**
	 * @param ilObjAssistedExerciseFacade $obj_facade
	 * @param int $il_user_id
	 * @param int $parent_obj_id
	 *
	 * @return bool
	 */
	static function hasCreateAccess(ilObjAssistedExerciseFacade $obj_facade,$il_user_id,$parent_obj_id = 0) {

		$global_access = ilObjAssistedExerciseAccess::getInstance($obj_facade,$il_user_id);


		if($global_access->hasWriteAccess()) {
			return true;
		}

		if(!$global_access->hasReadAccess()) {
			return false;
		}

		if($obj_facade->getSetting()->getModus() == xaseSetting::MODUS2) {
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

		return false;
	}

}