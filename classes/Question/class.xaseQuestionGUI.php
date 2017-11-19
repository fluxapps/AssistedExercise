<?php
require_once 'class.xaseQuestionFormGUI.php';
require_once 'class.xaseQuestionTableGUI.php';
require_once 'class.xaseQuestionAccess.php';

/**
 * Class xaseQuestionGUI
 *
 * @author            Benjamin Seglias <bs@studer-raimann.ch>
 */
class xaseQuestionGUI {

	const ITEM_IDENTIFIER = 'question_id';
	const CMD_INDEX = 'index';
	const CMD_CANCEL = 'cancel';
	const CMD_EDIT = 'edit';
	const CMD_UPDATE = 'update';
	const CMD_APPLY_FILTER = 'applyFilter';
	const CMD_RESET_FILTER = 'resetFilter';

	/**
	 * @var ilObjAssistedExerciseFacade
	 */
	protected $obj_facade;



	public function __construct() {
		$this->obj_facade = ilObjAssistedExerciseFacade::getInstance($_GET['ref_id']);
	}


	public function executeCommand() {
		$nextClass = $this->obj_facade->getCtrl()->getNextClass();
		switch ($nextClass) {
			default:
				$this->performCommand();
		}
	}


	protected function performCommand() {
		$cmd = $this->obj_facade->getCtrl()->getCmd(self::CMD_INDEX);
		switch ($cmd) {
			case self::CMD_EDIT:
			case self::CMD_UPDATE:
				/*if (xaseQuestionAccess::hasWriteAccess($this->xase_settings, $this->xase_question)) {
					$this->{$cmd}();
					break;
				} else {
					ilUtil::sendFailure(ilAssistedExercisePlugin::getInstance()->txt('permission_denied'), true);
					break;
				}*/
			case self::CMD_INDEX:
			case self::CMD_CANCEL:
			case self::CMD_APPLY_FILTER:
			case self::CMD_RESET_FILTER:
				$this->{$cmd}();
				break;
				//case self::CMD_SET_ANSWER_STATUS_TO_CAN_BE_VOTED:
				/*if (xaseQuestionAccess::hasReadAccess($this->xase_settings, $this->xase_question)) {
					$this->{$cmd}();
					break;
				} else {
					ilUtil::sendFailure(ilAssistedExercisePlugin::getInstance()->txt('permission_denied'), true);
					break;
				}*/
		}
	}

	public function index() {

		if(xaseQuestionAccess::hasCreateAccess($this->obj_facade,$this->obj_facade->getUser()->getId())) {
			$new_item_link = $this->obj_facade->getCtrl()->getLinkTarget($this, xaseQuestionGUI::CMD_EDIT);
			$ilLinkButton = ilLinkButton::getInstance();
			$ilLinkButton->setCaption($this->obj_facade->getLanguageValue("add_task"), false);
			$ilLinkButton->setUrl($new_item_link);
			/** @var $ilToolbar ilToolbarGUI */
			$this->obj_facade->getDic()->toolbar()->addButtonInstance($ilLinkButton);
		}

		$table_gui = new xaseQuestionTableGUI($this, self::CMD_INDEX);
		$this->obj_facade->getTpl()->setContent($table_gui->getHTML());
		/*$this->obj_facade->getCtrl()->saveParameterByClass(xaseQuestionTableGUI::class, self::ITEM_IDENTIFIER);
		if (!xaseQuestionAccess::hasReadAccess($this->xase_settings, $this->xase_question)) {
			ilUtil::sendFailure($this->obj_facade->getLanguageValue('permission_denied'), true);
		}*/
		//$table_gui = new xaseQuestionTableGUI($this, self::CMD_INDEX);
		/*if ($this->xase_settings->getModus() != xaseSettingMODUS2) {
			$list = $this->createListing();
			$this->obj_facade->getTpl()->setContent($list . $xaseQuestionTableGUI->getHTML());
		} else {
			$this->obj_facade->getTpl()->setContent($xaseQuestionTableGUI->getHTML());
		}*/

		$this->obj_facade->getTpl()->show();

	}


	public function edit() {

		$this->obj_facade->getCtrl()->saveParameter($this, self::ITEM_IDENTIFIER);

		$form = new xaseQuestionFormGUI($this);
		$form->fillForm();
		$this->obj_facade->getTpl()->setContent($form->getHTML());
		$this->obj_facade->getTpl()->show();
	}





	public function update() {
		$this->obj_facade->getCtrl()->saveParameter($this, self::ITEM_IDENTIFIER);

		$form = new xaseQuestionFormGUI($this);
		$form->setValuesByPost();
		if($form->checkInput()) {
			$form->update();
			ilUtil::sendSuccess($this->obj_facade->getLanguageValue('changes_saved_success'), true);
			$this->obj_facade->getCtrl()->redirect($this);
		}

		$this->obj_facade->getTpl()->setContent($form->getHTML());
	}





	protected function applyFilter() {
		$xaseQuestionTableGUI = new xaseQuestionTableGUI($this, self::CMD_INDEX, $this->object);
		$xaseQuestionTableGUI->writeFilterToSession();
		$this->obj_facade->getCtrl()->redirect($this, self::CMD_INDEX);
	}


	protected function resetFilter() {
		$xaseQuestionTableGUI = new xaseQuestionTableGUI($this, self::CMD_INDEX, $this->object);
		$xaseQuestionTableGUI->resetFilter();
		$xaseQuestionTableGUI->resetOffset();
		$this->obj_facade->getCtrl()->redirect($this, self::CMD_INDEX);
	}


	protected function cancel() {
		$this->obj_facade->getCtrl()->redirect($this, self::CMD_INDEX);
	}


	/*
	protected function setAnswerStatusToCanBeVoted() {
		$answers_from_user = xaseQuestionTableGUI::getAnswersFromUser($this->object, $this->dic);
		if (!empty($answers_from_user)) {
			foreach ($answers_from_user as $answer) {
				if ($answer->getAnswerStatus() == xaseAnswer::ANSWER_STATUS_ANSWERED) {
					$answer->setAnswerStatus(xaseAnswer::ANSWER_STATUS_CAN_BE_VOTED);
				}
				$answer->store();
			}
		}
		$this->obj_facade->getCtrl()->redirect($this, self::CMD_STANDARD);
	}*/


	protected function getMaxAchievedPoints() {
		$answers_from_user = xaseQuestionTableGUI::getAnswersFromUser($this->object, $this->dic);

		if (empty($answers_from_user)) {
			return 0;
		}

		$max_achieved_points = 0;
		foreach ($answers_from_user as $answer) {
			$xasePoint = xasePoint::where(array( 'id' => $answer->getPointId() ))->first();
			$max_achieved_points += $xasePoint->getTotalPoints();
		}

		return $max_achieved_points;
	}


	protected function getTotalUsedHints() {
		$answers_from_user = xaseQuestionTableGUI::getAnswersFromUser($this->object, $this->dic);
		if (empty($answers_from_user)) {
			return 0;
		}
		$total_used_hints = 0;
		foreach ($answers_from_user as $answer) {
			$total_used_hints += $answer->getNumberOfUsedHints();
		}

		return $total_used_hints;
	}




	protected function getDisposalDate() {
		/*if ($this->mode_settings->getDisposalDate() == "0000-00-00 00:00:00" || empty($this->mode_settings->getDisposalDate())) {
			return $this->obj_facade->getLanguageValue('no_disposal_date');
		} else {
			return $this->mode_settings->getDisposalDate();
		}*/
	}


	public function createListing() {
		$f = $this->dic->ui()->factory();
		$renderer = $this->dic->ui()->renderer();

		$unordered = $f->listing()->descriptive(array(
			$this->obj_facade->getLanguageValue('max_achievable_points') => strval(xaseQuestionTableGUI::getMaxAchievablePoints($this->object->getId(), $this->xase_settings->getModus())),
			$this->obj_facade->getLanguageValue('max_achieved_points') => strval($this->getMaxAchievedPoints()),
			$this->obj_facade->getLanguageValue('total_used_hints') => strval($this->getTotalUsedHints()),
			$this->obj_facade->getLanguageValue('disposal_date') => $this->getDisposalDate(),
		));

		return $renderer->render($unordered);
	}
}