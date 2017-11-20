<?php

/**
 * Class xaseQuestionDeleteGUI
 *
 * @ilCtrl_isCalledBy xaseQuestionDeleteGUI: ilObjAssistedExerciseGUI
 */


require_once "./Customizing/global/plugins/Services/Repository/RepositoryObject/AssistedExercise/classes/Question/class.xaseQuestion.php";


class xaseQuestionDeleteGUI {

	const ITEM_IDENTIFIER = 'question_id';
	const CMD_STANDARD = 'delete';
	const CMD_CONFIRM_DELETE = 'confirmedDelete';
	const CMD_CANCEL_DELETE = 'canceledDelete';
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
		$this->access = new ilObjAssistedExerciseAccess();
		$this->pl = ilAssistedExercisePlugin::getInstance();
		$this->object = ilObjectFactory::getInstanceByRefId($_GET['ref_id']);
		$this->xase_settings = xaseSetting::where([ 'assisted_exercise_object_id' => $this->object->getId() ])->first();
		$this->xase_question = new xaseQuestion($_GET[self::ITEM_IDENTIFIER]);
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
			case self::CMD_CONFIRM_DELETE:
			case self::CMD_CANCEL_DELETE:
				/*if (xaseQuestionAccess::hasDeleteAccess($this->xase_settings, $this->xase_question)) {
					$this->{$cmd}();
					break;
				} else {
					ilUtil::sendFailure(ilAssistedExercisePlugin::getInstance()->txt('permission_denied'), true);
					break;
				}*/
		}
	}


	public function delete() {
		$this->obj_facade->getCtrl()->saveParameter($this, self::ITEM_IDENTIFIER);

		include_once("./Services/Utilities/classes/class.ilConfirmationGUI.php");
		$cgui = new ilConfirmationGUI();

		$cgui->setHeaderText($this->obj_facade->getLanguageValue('confirm_delete_question'));
		$cgui->setFormAction($this->obj_facade->getCtrl()->getFormAction($this));
		$cgui->setCancel($this->dic->language()->txt('cancel'), "canceledDelete");
		$cgui->setConfirm($this->dic->language()->txt('confirm'), "confirmedDelete");

		$cgui->addItem('', '', xaseQuestion::where(array( 'id' => $_GET['question_id'] ))->first()
			->getItemTitle(), ilObject::_getIcon($this->object->getId()));

		$this->obj_facade->getTpl()->setContent($cgui->getHTML());
		$this->obj_facade->getTpl()->show();
	}


	public function confirmedDelete() {

		// Get Item
		$xaseQuestion = xaseQuestion::where(array( 'id' => $_GET['question_id'] ))->first();


		// Get all Hints and delete all associated Level and finally the Hint itself
		$xaseHints = xaseHint::where(array( 'question_id' => $_GET['question_id'] ))->get();
		foreach ($xaseHints as $xaseHint) {
			// Get and delete all Level and associated Points
			$xaseLevels = xaseHintLevel::where(array( 'hint_id' => $xaseHint->getId() ))->get();
			foreach ($xaseLevels as $xaseLevel) {
				/*$xasePoint = xasePoint::where(array( 'id' => $xaseLevel->getPointId() ))->first();
				if ($xasePoint !== NULL) {
					$xasePoint->delete();
				}*/

				$xaseLevel->delete();
			}

			$xaseHint->delete();
		}

		// Delete all Points
		/*$xasePoints = xasePoint::where(array( 'question_id' => $_GET['question_id'] ))->get();p
		foreach ($xasePoints as $xasePoint) {
			$xasePoint->delete();
		}*/

		// Get all Answers and delete all associated Comments, Votings and finally the Answer itself
		$xaseAnswers = xaseAnswer::where(array( 'question_id' => $_GET['question_id'] ))->get();
		foreach ($xaseAnswers as $xaseAnswer) {
			$xaseComments = xaseComment::where(array( 'answer_id' => $xaseAnswer->getId() ))->get();
			foreach ($xaseComments as $xaseComment) {
				$xaseComment->delete();
			}

			$xaseVotings = xaseVoting::where(array( 'answer_id' => $xaseAnswer->getId() ))->get();
			foreach ($xaseVotings as $xaseVoting) {
				$xaseVoting->delete();
			}

			$xaseAnswer->delete();
		}

		// Delete the Item itself
		$xaseQuestion->delete();

		ilUtil::sendSuccess($this->obj_facade->getLanguageValue('successfully_deleted'), true);
		$this->obj_facade->getCtrl()->redirectByClass("xaseQuestionGUI");
	}


	public function canceledDelete() {
		$this->obj_facade->getCtrl()->redirectByClass("xaseQuestionGUI");
	}
}