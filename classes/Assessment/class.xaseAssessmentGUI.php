<?php
/**
 * Class xaseAssessmentGUI
 *
 * @author: Benjamin Seglias   <bs@studer-raimann.ch>
 */

require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/AssistedExercise/classes/Assessment/class.xaseAssessmentFormGUI.php');

class xaseAssessmentGUI {

	const   CMD_STANDARD = 'edit';
	const   CMD_UPDATE = 'update';
	const   CMD_CANCEL = 'cancel';
	const   CMD_VIEW_ASSESSMENT = 'view_assessment';
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
	 * @var xaseQuestion
	 */
	protected $xase_question;
	/**
	 * @var int
	 */
	protected $is_student;

	/**
	 * @var ilObjAssistedExerciseFacade
	 */
	protected $obj_facade;


	public function __construct() {
		$this->obj_facade = ilObjAssistedExerciseFacade::getInstance($_GET['ref_id']);

		global $DIC;
		$this->dic = $DIC;
		$this->tpl = $this->dic['tpl'];
		$this->tabs = $DIC->tabs();
		$this->ctrl = $this->dic->ctrl();
		$this->access = ilObjAssistedExerciseAccess::getInstance($this->obj_facade, $this->obj_facade->getUser()->getId());
		$this->pl = ilAssistedExercisePlugin::getInstance();
		$this->xase_question = new xaseQuestion($_GET['question_id']);
	}


	public function executeCommand() {
		$nextClass = $this->obj_facade->getCtrl()->getNextClass();
		switch ($nextClass) {
			default:
				$this->obj_facade->getTabsGUI()->activateTab(xaseQuestionGUI::CMD_INDEX);
				$this->performCommand();
		}
	}


	protected function performCommand() {
		$cmd = $this->obj_facade->getCtrl()->getCmd(self::CMD_STANDARD);
		switch ($cmd) {
			case self::CMD_STANDARD:
			case self::CMD_UPDATE:
			case self::CMD_CANCEL:
				if ($this->access->hasWriteAccess()) {
					$this->{$cmd}();
					break;
				} else {
					ilUtil::sendFailure(ilAssistedExercisePlugin::getInstance()->txt('permission_denied'), true);
					$this->obj_facade->getTpl()->show();
					break;
				}
			case self::CMD_VIEW_ASSESSMENT:
				if ($this->access->hasReadAccess()) {
					$this->{$cmd}();
					break;
				} else {
					ilUtil::sendFailure(ilAssistedExercisePlugin::getInstance()->txt('permission_denied'), true);
					$this->obj_facade->getTpl()->show();
					break;
				}
		}


	}


	protected function isStudent() {
		if ($this->access->hasWriteAccess($_GET['ref_id'], $this->dic->user()->getId())) {
			$this->is_student = false;
		} else {
			$this->is_student = true;
		}
	}


	public function view_assessment() {
		$this->obj_facade->getCtrl()->saveParameter($this, 'answer_id');
		$this->obj_facade->getTabsGUI()->activateTab(xaseSubmissionGUI::CMD_STANDARD);
		$xaseAssessmentFormGUI = new xaseAssessmentFormGUI($this, $this->assisted_exercise, true);
		$xaseAssessmentFormGUI->fillForm();
		$this->obj_facade->getTpl()->setContent($xaseAssessmentFormGUI->getHTML());
		$this->obj_facade->getTpl()->show();
	}


	public function edit() {
		$this->obj_facade->getCtrl()->saveParameter($this, 'answer_id');
		$this->obj_facade->getTabsGUI()->activateTab(xaseSubmissionGUI::CMD_STANDARD);
		$xaseAssessmentFormGUI = new xaseAssessmentFormGUI($this, $this->assisted_exercise);
		$xaseAssessmentFormGUI->fillForm();
		$this->obj_facade->getTpl()->setContent($xaseAssessmentFormGUI->getHTML());
		$this->obj_facade->getTpl()->show();
	}


	public function update() {
		$this->obj_facade->getCtrl()->saveParameter($this, 'answer_id');
		$this->obj_facade->getTabsGUI()->activateTab(xaseSubmissionGUI::CMD_STANDARD);
		$xaseAssessmentFormGUI = new xaseAssessmentFormGUI($this, $this->assisted_exercise);
		if ($xaseAssessmentFormGUI->updateObject()) {
			ilUtil::sendSuccess($this->obj_facade->getLanguageValue('changes_saved_success'), true);
			$this->obj_facade->getCtrl()->redirect($this, self::CMD_STANDARD);
		}

		$xaseAssessmentFormGUI->setValuesByPost();
		$this->obj_facade->getTpl()->setContent($xaseAssessmentFormGUI->getHTML());
		$this->obj_facade->getTpl()->show();
	}


	public function cancel() {
		if (!ilObjAssistedExerciseAccess::hasWriteAccess($_GET['ref_id'], $this->dic->user()->getId())) {
			$this->obj_facade->getCtrl()->redirectByClass('xaseitemgui', xaseQuestionGUI::CMD_INDEX);
		} else {
			$this->obj_facade->getCtrl()->redirectByClass('xasesubmissiongui', xaseSubmissionGUI::CMD_STANDARD);
		}
	}
}