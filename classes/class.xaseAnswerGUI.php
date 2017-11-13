<?php

/**
 * Class xaseAnswerGUI
 *
 * @author  Benjamin Seglias <bs@studer-raimann.ch>
 */

require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/AssistedExercise/classes/class.xaseAnswerFormGUI.php');
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/AssistedExercise/classes/class.xaseAnswerFormListGUI.php');

class xaseAnswerGUI {

	const M1 = "1";
	const M2 = "2";
	const M3 = "3";
	const ANSWER_IDENTIFIER = 'answer_id';
	const CMD_STANDARD = 'edit';
	const CMD_UPDATE = 'update';
	const CMD_UPDATE_AND_SET_STATUS_TO_VOTE = 'upadteAndSetStatusToVote';
	const CMD_CANCEL = 'cancel';
	const CMD_SHOW = 'show';

	/**
	 * @var ilObjAssistedExercise
	 */
	public $assisted_exercise;
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
	/**
	 * @var xaseItem
	 */
	protected $xase_item;
	/**
	 * @var xaseSettings
	 */
	public $xase_settings;
	/**
	 * @var xaseSettingsM1|xaseSettingsM2|xaseSettingsM3
	 */
	protected $mode_settings;


	public function __construct(ilObjAssistedExercise $assisted_exericse) {
		global $DIC;
		$this->dic = $DIC;
		$this->tpl = $this->dic['tpl'];
		$this->tabs = $DIC->tabs();
		$this->ctrl = $this->dic->ctrl();
		$this->access = new ilObjAssistedExerciseAccess();
		$this->pl = ilAssistedExercisePlugin::getInstance();
		$this->assisted_exercise = $assisted_exericse;
		$this->xase_settings = xaseSettings::where([ 'assisted_exercise_object_id' => $this->assisted_exercise->getId() ])->first();
		$this->mode_settings = $this->getModeSettings($this->xase_settings->getModus());
		$this->xase_item = new xaseItem($_GET['item_id']);

		$this->xase_answer = xaseAnswer::findOrGetInstance($_GET['answer_id']);
		$this->ctrl->saveParameter($this,self::ANSWER_IDENTIFIER);



		//parent::__construct();
	}


	public function executeCommand() {
		$nextClass = $this->ctrl->getNextClass();
		switch ($nextClass) {
			default:
				$this->tabs->activateTab(xaseItemGUI::CMD_STANDARD);
				$this->performCommand();
		}
	}


	protected function performCommand() {
		$cmd = $this->ctrl->getCmd(self::CMD_STANDARD);
		switch ($cmd) {
			case self::CMD_STANDARD:
			case self::CMD_SHOW:
			case self::CMD_UPDATE:
			case self::CMD_CANCEL:
			case self::CMD_UPDATE_AND_SET_STATUS_TO_VOTE:
				if ($this->access->hasReadAccess()) {
					$this->{$cmd}();
					break;
				} else {
					ilUtil::sendFailure(ilAssistedExercisePlugin::getInstance()->txt('permission_denied'), true);
					break;
				}
		}
	}

/*
	protected function getAnswer() {
		$xaseAnswer = xaseAnswer::where(array(
			'item_id' => $this->xase_item->getId(),
			'user_id' => $this->dic->user()->getId()
		), array( 'item_id' => '=', 'user_id' => '=' ))->first();
		if (empty($xaseAnswer)) {
			$xaseAnswer = new xaseAnswer();
		}

		return $xaseAnswer;
	}
*/

	protected function canVote() {
		$current_date = date('Y-m-d h:i:s', time());
		$current_date_datetime = DateTime::createFromFormat('Y-m-d H:i:s', $current_date);
		$start_voting_date_datetime = DateTime::createFromFormat('Y-m-d H:i:s', $this->mode_settings->getStartVotingDate());
		if ($start_voting_date_datetime->getTimestamp() <= $current_date_datetime->getTimestamp()
			|| $this->mode_settings->getStartVotingDate() == "0000-00-00 00:00:00") {
			return true;
		} else {
			return true;
		}
	}


	public function edit() {
		$this->tabs->activateTab(xaseItemGUI::CMD_STANDARD);
		$xaseAnswerFormGUI = new xaseAnswerFormGUI($this, $this->assisted_exercise, $this->xase_item);
		$xaseAnswerFormGUI->fillForm();
		$this->tpl->setContent($xaseAnswerFormGUI->getHTML());
		$this->tpl->show();
	}

	//TODO Refactor
	public function show() {
		if($answer_id = $_GET['answer_id']) {


			if($this->xase_answer->getAnswerStatus() == xaseAnswer::ANSWER_STATUS_CAN_BE_VOTED ||  $this->xase_answer->getUserId() == $this->dic->user()->getId()) {
				$this->tabs->activateTab(xaseItemGUI::CMD_STANDARD);
				$xaseAnswerFormGUI = new xaseAnswerFormGUI($this, $this->assisted_exercise, $this->xase_item, true);
				$xaseAnswerFormGUI->fillForm();
				$this->tpl->setContent($xaseAnswerFormGUI->getHTML());
				$this->tpl->show();
			} else {
				ilUtil::sendFailure(ilAssistedExercisePlugin::getInstance()->txt('permission_denied'), true);
				$this->ctrl->redirectByClass('xaseItemGUI', xaseItemGUI::CMD_STANDARD);
			}
		} else {

			ilUtil::sendFailure(ilAssistedExercisePlugin::getInstance()->txt('permission_denied'), true);
			$this->ctrl->redirectByClass('xaseItemGUI', xaseItemGUI::CMD_STANDARD);
		}
	}

	public function upadteAndSetStatusToVote() {
		$this->update(xaseAnswer::ANSWER_STATUS_CAN_BE_VOTED);
	}


	public function update($status = xaseAnswer::ANSWER_STATUS_ANSWERED) {
		$this->tabs->activateTab(xaseItemGUI::CMD_STANDARD);
		$xaseAnswerFormGUI = new xaseAnswerFormGUI($this, $this->assisted_exercise, $this->xase_item);
		if ($xaseAnswerFormGUI->updateObject($status)) {
			ilUtil::sendSuccess($this->pl->txt('changes_saved_success'), true);
			$this->ctrl->redirectByClass(xaseItemGUI::class, xaseItemGUI::CMD_STANDARD);
		} else {
			$xaseAnswerFormGUI->setValuesByPost();
			$xaseAnswerFormGUI->fillTaskInput();
			$this->tpl->setContent($xaseAnswerFormGUI->getHTML());
			$this->tpl->show();
		}
	}


	public function cancel() {
		$this->ctrl->redirectByClass('xaseItemGUI', xaseItemGUI::CMD_CANCEL);
	}


	protected function getModeSettings($mode) {
		if ($mode == self::M1) {
			return xaseSettingsM1::where([ 'settings_id' => $this->xase_settings->getId() ])->first();
		} elseif ($mode == self::M3) {
			return xaseSettingsM3::where([ 'settings_id' => $this->xase_settings->getId() ])->first();
		} else {
			return xaseSettingsM2::where([ 'settings_id' => $this->xase_settings->getId() ])->first();
		}
	}
}