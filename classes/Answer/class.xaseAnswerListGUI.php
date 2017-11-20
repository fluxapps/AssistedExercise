<?php
/**
 * Class xaseAnswerListGUI
 *
 * @author: Benjamin Seglias   <bs@studer-raimann.ch>
 */

class xaseAnswerListGUI {

	const CMD_VIEW = 'view';
	const CMD_STANDARD = 'edit';
	const CMD_UPDATE = 'update';
	const CMD_CANCEL = 'cancel';
	const CMD_COMMENT_ID = 'getNexAvailableCommentId';
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


	public function __construct(ilObjAssistedExercise $assisted_exericse) {
		global $DIC;
		$this->dic = $DIC;
		$this->tpl = $this->dic['tpl'];
		$this->tabs = $DIC->tabs();
		$this->ctrl = $this->dic->ctrl();
		$this->access = new ilObjAssistedExerciseAccess();
		$this->pl = ilAssistedExercisePlugin::getInstance();
		$this->assisted_exercise = $assisted_exericse;
		$this->xase_question = new xaseQuestion($_GET['question_id']);


		$this->obj_facade->getTpl()->addJavaScript('./Customizing/global/plugins/Services/Repository/RepositoryObject/AssistedExercise/templates/js/answerformlist.js');
		//parent::__construct();
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
			case self::CMD_CANCEL:
			case self::CMD_UPDATE:
			case self::CMD_STANDARD:
			case self::CMD_COMMENT_ID:
				if ($this->access->hasReadAccess()) {
					$this->{$cmd}();
					break;
				} else {
					ilUtil::sendFailure(ilAssistedExercisePlugin::getInstance()->txt('permission_denied'), true);
					break;
				}
		}
	}


	public function edit() {
		$this->obj_facade->getCtrl()->saveParameterByClass(xaseAnswerListGUI::class, xaseQuestionGUI::ITEM_IDENTIFIER);
		$callHistory = $this->obj_facade->getCtrl()->getCallHistory();
		$this->obj_facade->getTabsGUI()->activateTab(xaseQuestionGUI::CMD_INDEX);
		$xaseAnswerFormListGUI = new xaseAnswerFormListGUI($this->assisted_exercise, $this);
		$xaseAnswerFormListGUI->fillForm();
		$this->obj_facade->getTpl()->setContent($xaseAnswerFormListGUI->getHTML());
		$this->obj_facade->getTpl()->show();
	}


	public function update() {
		$this->obj_facade->getCtrl()->saveParameterByClass(xaseAnswerListGUI::class, xaseQuestionGUI::ITEM_IDENTIFIER);
		$this->obj_facade->getTabsGUI()->activateTab(xaseQuestionGUI::CMD_INDEX);
		$xaseAnswerFormListGUI = new xaseAnswerFormListGUI($this->assisted_exercise, $this);
		if ($xaseAnswerFormListGUI->updateObject()) {

			ilUtil::sendSuccess($this->obj_facade->getLanguageValue('changes_saved_success'), true);
			//TODO redirect nur ausführen wenn das votings ab Datum in den Modus Setting noch nicht erreicht wurde + wenn mindestens eine Antwort vorhanden ist für das Item und diese eingereicht wurde
			$this->obj_facade->getCtrl()->redirectByClass(xaseQuestionGUI::class, xaseQuestionGUI::CMD_INDEX);
		}
		$xaseAnswerFormListGUI->setValuesByPost();
		$this->obj_facade->getTpl()->setContent($xaseAnswerFormListGUI->getHTML());
		$this->obj_facade->getTpl()->show();
	}


	protected function cancel() {
		$this->obj_facade->getCtrl()->redirectByClass('xaseitemgui', xaseQuestionGUI::CMD_INDEX);
	}


	public function getNextAvailableCommentId() {
		$statement = $this->dic->database()->query("SELECT * FROM xase_comment ORDER BY id DESC LIMIT 1");

		$results = array();

		while ($record = $this->dic->database()->fetchAssoc($statement)) {
			$results[] = $record;
		}

		echo ++ $results[0]['id'];
	}
}