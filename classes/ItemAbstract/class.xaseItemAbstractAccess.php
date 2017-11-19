<?php
/**
 * Class xaseItemAbstractAccess
 *
 * @author  Martin Studer <ms@studer-raimann.ch>
 */

abstract class xaseItemAbstractAccess implements xaseItemAbstractInterface {

	/**
	 * @var ilObjAssistedExerciseFacade
	 */
	protected $obj_facade;
	/**
	 * @var ActiveRecord
	 */
	protected $item;
	/**
	 * @var int
	 */
	protected $il_user_id;
	/**
	 * @var ilObjAssistedExerciseAccess
	 */
	protected $global_access;


	/**
	 * xaseItemAbstractAccess constructor.
	 *
	 * @param ActiveRecord $item
	 * @param int $il_user_id
	 */
	public function __construct($item,$il_user_id) {
		$this->obj_facade = ilObjAssistedExerciseFacade::getInstance($_GET['ref_id']);
		$this->item =  $item;
		$this->il_user_id =  $il_user_id;
		$this->global_access = ilObjAssistedExerciseAccess::getInstance($this->obj_facade,$il_user_id);
	}

	/**
	 * @return bool
	 */
	abstract function hasReadAccess();
	/**
	 * @return bool
	 */
	abstract function hasWriteAccess();
	/**
	 * @return bool
	 */
	abstract function hasDeleteAccess();

	//TODO Move to excercise Access
	public static function isTimeLimitRespected($obj_facade) {
		if($obj_facade->getSetting()->getIsTimeLimited()) {
			$current_date = date('Y-m-d h:i:s', time());
			$current_date_datetime = DateTime::createFromFormat('Y-m-d H:i:s', $current_date);

			$start_date = DateTime::createFromFormat('Y-m-d H:i:s', $obj_facade->getSetting()->getStartDate());
			if($current_date_datetime->getTimestamp() < $start_date->getTimestamp()) {
				return false;
			}

			$end_date = DateTime::createFromFormat('Y-m-d H:i:s', $obj_facade->getSetting()->getEndDate());
			if($current_date_datetime->getTimestamp() > $end_date->getTimestamp()) {
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
		if($obj_facade->getSetting()->getRateAnswers()) {
			$current_date = date('Y-m-d h:i:s', time());
			$current_date_datetime = DateTime::createFromFormat('Y-m-d H:i:s', $current_date);

			$end_date = DateTime::createFromFormat('Y-m-d H:i:s', $obj_facade->getSetting()->getDisposalDate());

			if($current_date_datetime->getTimestamp() > $end_date->getTimestamp()) {
				return false;
			}
		}

		return true;
	}
}

//We Work with an Interface, because abstract static functions are not possible in php
interface xaseItemAbstractInterface {

	/**
	 * @param ilObjAssistedExerciseFacade $obj_facade
	 * @param int $il_user_id
	 * @param int $parent_obj_id
	 *
	 * @return bool
	 */
	static function hasCreateAccess(ilObjAssistedExerciseFacade $obj_facade,$il_user_id,$parent_obj_id = 0);
}