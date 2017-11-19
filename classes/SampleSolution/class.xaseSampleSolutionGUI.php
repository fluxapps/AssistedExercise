<?php

/**
 * Class xaseSampleSolutionGUI
 *
 * @author: Benjamin Seglias   <bs@studer-raimann.ch>
 */

class xaseSampleSolutionGUI {

	const ITEM_IDENTIFIER = 'question_id';
	const CMD_STANDARD = 'show_sample_solution';
	const CMD_CANCEL = 'cancel';
	/**
	 * @var ilObjAssistedExercise
	 */
	public $object;
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
	/**
	 * @var xaseAnswerAccess
	 */
	protected $answer_access;

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
		$this->access = ilObjAssistedExerciseAccess::getInstance($this->obj_facade,$this->obj_facade->getUser()->getId());
		$this->pl = ilAssistedExercisePlugin::getInstance();
		$this->object = ilObjectFactory::getInstanceByRefId($_GET['ref_id']);
		$this->xase_settings = xaseSetting::where([ 'assisted_exercise_object_id' => $this->object->getId() ])->first();
		//TODO set question_id Parameter
		$this->xase_question = new xaseQuestion($_GET[self::ITEM_IDENTIFIER]);
	}


	public function executeCommand() {

		$nextClass = $this->obj_facade->getCtrl()->getNextClass();
		switch ($nextClass) {
			case 'xasequestiongui':
				//has to be called because in this case parent::executeCommand is not executed(contains getStandardTempplate and Show)
				//Show Method has to be called in the corresponding methods
				$xaseQuestionGUI = new xaseQuestionGUI();
				$this->obj_facade->getCtrl()->forwardCommand($xaseQuestionGUI);
				break;

			default:
				$this->obj_facade->getTabsGUI()->activateTab(self::CMD_STANDARD);
				$this->performCommand();
		}
	}


	protected function performCommand() {
		$cmd = $this->obj_facade->getCtrl()->getCmd(self::CMD_STANDARD);
		switch ($cmd) {
			case self::CMD_STANDARD:
			case self::CMD_CANCEL:
				if ($this->access->hasReadAccess()) {
					$this->{$cmd}();
					break;
				} else {
					ilUtil::sendFailure(ilAssistedExercisePlugin::getInstance()->txt('permission_denied'), true);
					break;
				}
		}

	}


	public function show_sample_solution() {
		$this->obj_facade->getCtrl()->saveParameter($this, self::ITEM_IDENTIFIER);
		$this->obj_facade->getTabsGUI()->activateTab(self::CMD_STANDARD);
		$xaseSampleSolutionFormGUI = new xaseSampleSolutionFormGUI($this, $this->xase_question);
		$xaseSampleSolutionFormGUI->show_sample_solution();
		$this->obj_facade->getTpl()->setContent($xaseSampleSolutionFormGUI->getHTML());
		$this->obj_facade->getTpl()->show();
	}


	protected function cancel() {
		$this->obj_facade->getCtrl()->redirectByClass(array( 'ilObjAssistedExerciseGUI', 'xasequestiongui' ), xaseQuestionGUI::CMD_INDEX);
	}
}