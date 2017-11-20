<?php

/**
 * Class xaseUpvotingsGUI
 *
 * @author: Benjamin Seglias   <bs@studer-raimann.ch>
 */

class xaseUpvotingsGUI {

	const CMD_STANDARD = 'show_upvotings';
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
	 * @var xaseAnswer
	 */
	public $xase_answer;
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
		$this->object = ilObjectFactory::getInstanceByRefId($_GET['ref_id']);
		$this->xase_settings = xaseSetting::where([ 'assisted_exercise_object_id' => $this->object->getId() ])->first();
		$this->xase_question = new xaseQuestion($_GET[xaseQuestionGUI::ITEM_IDENTIFIER]);
		$this->xase_answer = new xaseAnswer($_GET[xaseAnswerGUI::ANSWER_IDENTIFIER]);
	}


	public function executeCommand() {

		$nextClass = $this->obj_facade->getCtrl()->getNextClass();
		switch ($nextClass) {
			default:
				$this->obj_facade->getTabsGUI()->activateTab(xaseSubmissionGUI::CMD_STANDARD);
				$this->performCommand();
		}
	}


	protected function performCommand() {
		$cmd = $this->obj_facade->getCtrl()->getCmd(self::CMD_STANDARD);
		switch ($cmd) {
			case self::CMD_STANDARD:
			case self::CMD_CANCEL:
				if ($this->access->hasWriteAccess()) {
					$this->{$cmd}();
					break;
				} else {
					ilUtil::sendFailure(ilAssistedExercisePlugin::getInstance()->txt('permission_denied'), true);
					break;
				}
		}
	}


	public function show_upvotings() {
		$this->obj_facade->getTabsGUI()->activateTab(xaseSubmissionGUI::CMD_STANDARD);
		$submission_table_link = $this->obj_facade->getCtrl()->getLinkTargetByClass(xaseSubmissionGUI::class, xaseSubmissionGUI::CMD_STANDARD);
		$ilLinkButton = ilLinkButton::getInstance();
		$ilLinkButton->setCaption($this->obj_facade->getLanguageValue("back"), false);
		$ilLinkButton->setUrl($submission_table_link);
		/** @var $ilToolbar ilToolbarGUI */
		$this->dic->toolbar()->addButtonInstance($ilLinkButton);
		$this->obj_facade->getTpl()->setContent($this->createListing($this->getUsersWhichVotedForAnswers()));
		$this->obj_facade->getTpl()->show();
	}

	protected function cancel() {
		$this->obj_facade->getCtrl()->redirectByClass(array( 'ilObjAssistedExerciseGUI', 'xasesubmissiongui' ), xaseSubmissionGUI::CMD_STANDARD);
	}

	protected function getUsersWhichVotedForAnswers() {
		$votings_for_answer = xaseVoting::where(array('answer_id' => $this->xase_answer->getId()))->get();
		$users = [];
		foreach($votings_for_answer as $voting) {

			$users[] = xaseilUser::where(array('usr_id' => $voting->getUserId()))->first();
	}
		return $users;
	}


/*	protected function getUserWhoAnsweredItem() {
		return xaseilUser::where(array('id' => $this->xase_answer->getUserId()))->first();
	}*/

	public function createListing($voters_array) {
		$tpl = new ilTemplate("tpl.upvoters.html", true, true, "Customizing/global/plugins/Services/Repository/RepositoryObject/AssistedExercise");

		$user_who_answered_item = xaseilUser::where(array('usr_id' => $this->xase_answer->getUserId()))->first();


		$tpl->setVariable("META_INFO", $this->obj_facade->getLanguageValue("upvoters_for_answer_from") . " " . $user_who_answered_item->getFirstname() . " " . $user_who_answered_item->getLastname() . " " . $this->obj_facade->getLanguageValue("on_the_question") . " " . $this->xase_question->getTitle());

		$tpl->setCurrentBlock("LIST");
		/**
		 * @var $voter xaseilUser
		 */
		foreach($voters_array as $voter) {
			$tpl->setVariable("LIST_ITEM", $voter->getFirstname() . " " . $voter->getLastname());
		}
		$tpl->parseCurrentBlock();
		return $tpl->get();
	}
}