<?php
/**
 * Class xaseSubmissionGUI
 *
 * @author: Benjamin Seglias   <bs@studer-raimann.ch>
 */

require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/AssistedExercise/classes/Question/class.xaseQuestionFormGUI.php');
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/AssistedExercise/classes/Submission/class.xaseSubmissionTableGUI.php');

class xaseSubmissionGUI {

	const CMD_STANDARD = 'showSubmissions';
	const CMD_CANCEL = 'cancel';
	const CMD_EDIT = 'edit';
	const CMD_UPDATE = 'update';
	const CMD_ADD_SUBMITTED_EXERCISE = "addSubmittedExercise";
	const CMD_APPLY_FILTER = 'applyFilter';
	const CMD_RESET_FILTER = 'resetFilter';
	const M1 = 1;
	const M2 = 2;
	const M3 = 3;
	/**
	 * @var ilObjAssistedExercise
	 */
	public $assisted_exercise;
	/**
	 * @var xaseAssessment
	 */
	public $xase_assessment;
	/**
	 * @var xaseQuestion
	 */
	public $xase_question;
	/**
	 * @var xaseSetting
	 */
	public $xase_settings;
	/**
	 * @var \ILIAS\DI\Container
	 */
	protected $dic;
	/**
	 * @var ilTemplate
	 */
	protected $tpl;
	/**
	 * @var ilTabsGUI
	 */
	protected $tabs;
	/**
	 * @var ilCtrl
	 */
	protected $ctrl;
	/**
	 * @var ilAssistedExercisePlugin
	 */
	protected $pl;
	/**
	 * @var ilObjAssistedExerciseAccess
	 */
	protected $access;


	public function __construct() {
		global $DIC;
		$this->dic = $DIC;
		$this->tpl = $this->dic['tpl'];
		$this->tabs = $DIC->tabs();
		$this->ctrl = $this->dic->ctrl();
		$this->access = new ilObjAssistedExerciseAccess();
		$this->pl = ilAssistedExercisePlugin::getInstance();
		$this->assisted_exercise = ilObjectFactory::getInstanceByRefId($_GET['ref_id']);
		$this->xase_settings = xaseSetting::where([ 'assisted_exercise_object_id' => $this->assisted_exercise->getId() ])->first();
		//$this->mode_settings = $this->getModeSetting($this->xase_settings->getModus());
		//TODO set question_id Parameter
		$this->xase_question = new xaseQuestion($_GET[xaseQuestionGUI::ITEM_IDENTIFIER]);
	}


	public function executeCommand() {

		$nextClass = $this->obj_facade->getCtrl()->getNextClass();
		switch ($nextClass) {
			default:
				$this->obj_facade->getTabsGUI()->activateTab(self::CMD_STANDARD);
				$this->performCommand();
		}
	}


	protected function performCommand() {
		$cmd = $this->obj_facade->getCtrl()->getCmd(self::CMD_STANDARD);
		switch ($cmd) {
			case self::CMD_STANDARD:
				if ($this->access->hasWriteAccess()) {
					$this->{$cmd}();
					break;
				} else {
					ilUtil::sendFailure(ilAssistedExercisePlugin::getInstance()->txt('permission_denied'), true);
					break;
				}
			case self::CMD_ADD_SUBMITTED_EXERCISE:
			case self::CMD_APPLY_FILTER:
			case self::CMD_RESET_FILTER:
				if ($this->access->hasReadAccess()) {
					$this->{$cmd}();
					break;
				} else {
					ilUtil::sendFailure(ilAssistedExercisePlugin::getInstance()->txt('permission_denied'), true);
					break;
				}
		}
	}


	public function addSubmittedExercise() {
		//get only the answers from the items from the current exercise

		//$answers_from_current_user = xaseAnswer::where(array('user_id' => $this->dic->user()->getId(), 'question_id' => $this->xase_question->getId()))->get();

		$all_items_assisted_exercise = xaseQuestion::where(array( 'assisted_exercise_id' => $this->assisted_exercise->getId() ))->get();

		$answers_from_current_user = xaseQuestionTableGUI::getAllUserAnswersFromAssistedExercise($all_items_assisted_exercise, $this->dic, $this->dic->user());

		/*
		 * @var xaseAnswer $answers_from_current_user
		 */
		//TODO test it with multiple answers to see what the variables contains in this case
		foreach ($answers_from_current_user as $answer_from_current_user) {
			if (is_array($answers_from_current_user)) {
				$answer_from_current_user_object = xaseAnswer::where(array( 'id' => $answer_from_current_user['id'] ))->first();
				$answer_from_current_user_object->setAnswerStatus(xaseAnswer::ANSWER_STATUS_CAN_BE_VOTED);
				$answer_from_current_user_object->setSubmissionDate(date('Y-m-d H:i:s'));
				$answer_from_current_user_object->setIsAssessed(0);
				$answer_from_current_user_object->store();
			} else {
				$answer_from_current_user_object = xaseAnswer::where(array( 'id' => $answers_from_current_user['id'] ));
				$answer_from_current_user_object->setAnswerStatus(xaseAnswer::ANSWER_STATUS_CAN_BE_VOTED);
				$answer_from_current_user_object->setSubmissionDate(date('Y-m-d H:i:s'));
				$answer_from_current_user_object->setIsAssessed(0);
				$answer_from_current_user_object->store();
				break;
			}
		}
		ilUtil::sendSuccess($this->obj_facade->getLanguageValue('success_message_exercise_submitted'), true);
		$this->obj_facade->getCtrl()->redirectByClass(xaseQuestionGUI::class, xaseQuestionGUI::CMD_INDEX);
	}


	public function showSubmissions() {
		if (!$this->access->hasWriteAccess()) {
			ilUtil::sendFailure($this->obj_facade->getLanguageValue('permission_denied'), true);
		}
		//$xaseSubmissionTableGUI = new xaseSubmissionTableGUI($this, self::CMD_STANDARD, $this->assisted_exercise);
		/*if(self::isDisposalDateExpired($this->mode_settings)) {
			$this->obj_facade->getTpl()->setContent($xaseSubmissionTableGUI->getHTML());
		} else {
			$this->obj_facade->getTpl()->setContent($this->info_panel_disposal_date() . $xaseSubmissionTableGUI->getHTML());
		}*/
		$this->obj_facade->getTpl()->show();
	}


	protected function applyFilter() {
		/*$xaseSubmissionTableGUI = new xaseSubmissionTableGUI($this, self::CMD_STANDARD, $this->assisted_exercise);
		$xaseSubmissionTableGUI->writeFilterToSession();
		$this->obj_facade->getCtrl()->redirect($this, self::CMD_STANDARD);*/
	}


	protected function resetFilter() {
	/*	$xaseSubmissionTableGUI = new xaseSubmissionTableGUI($this, self::CMD_STANDARD, $this->assisted_exercise);
		$xaseSubmissionTableGUI->resetFilter();
		$xaseSubmissionTableGUI->resetOffset();
		$this->obj_facade->getCtrl()->redirect($this, self::CMD_STANDARD);*/
	}

	protected function cancel() {
		$this->obj_facade->getCtrl()->redirect($this, self::CMD_STANDARD);
	}



	public static function isDisposalDateExpired($mode_settings) {
		/*$current_date = date('Y-m-d h:i:s', time());
		$current_date_datetime = DateTime::createFromFormat('Y-m-d H:i:s', $current_date);
		$disposal_date_datetime = DateTime::createFromFormat('Y-m-d H:i:s', $mode_settings->getDisposalDate());
		if (($disposal_date_datetime->getTimestamp() > $current_date_datetime->getTimestamp())
			|| $mode_settings->getDisposalDate() == "0000-00-00 00:00:00") {
			return false;
		} else {
			return true;
		}*/
	}

	protected function info_panel_disposal_date() {
		global $DIC;
		$f = $DIC->ui()->factory();
		$renderer = $DIC->ui()->renderer();

		$panel = $f->panel()->standard(
			$this->obj_facade->getLanguageValue("info_regarding_disposal_date"),
			$f->legacy($this->obj_facade->getLanguageValue("assessment_can_be_done_after_the_defined_disposal_date"))
		);

		return $renderer->render($panel);
	}
}