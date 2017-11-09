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
	 * @var xaseItem
	 */
	public $xase_item;
	/**
	 * @var xaseAnswer
	 */
	public $xase_answer;
	/**
	 * @var xaseSettings
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
		$this->xase_settings = xaseSettings::where([ 'assisted_exercise_object_id' => $this->object->getId() ])->first();
		$this->xase_item = new xaseItem($_GET[xaseItemGUI::ITEM_IDENTIFIER]);
		$this->xase_answer = new xaseAnswer($_GET[xaseAnswerGUI::ANSWER_IDENTIFIER]);
	}


	public function executeCommand() {

		$nextClass = $this->ctrl->getNextClass();
		switch ($nextClass) {
			default:
				$this->tabs->activateTab(xaseSubmissionGUI::CMD_STANDARD);
				$this->performCommand();
		}
	}


	protected function performCommand() {
		$cmd = $this->ctrl->getCmd(self::CMD_STANDARD);
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
		$this->tabs->activateTab(xaseSubmissionGUI::CMD_STANDARD);
		$submission_table_link = $this->ctrl->getLinkTargetByClass(xaseSubmissionGUI::class, xaseSubmissionGUI::CMD_STANDARD);
		$ilLinkButton = ilLinkButton::getInstance();
		$ilLinkButton->setCaption($this->pl->txt("back"), false);
		$ilLinkButton->setUrl($submission_table_link);
		/** @var $ilToolbar ilToolbarGUI */
		$this->dic->toolbar()->addButtonInstance($ilLinkButton);
		$this->tpl->setContent($this->createListing($this->getUsersWhichVotedForAnswers()));
		$this->tpl->show();
	}

	protected function cancel() {
		$this->ctrl->redirectByClass(array( 'ilObjAssistedExerciseGUI', 'xasesubmissiongui' ), xaseSubmissionGUI::CMD_STANDARD);
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

		$tpl->setVariable("META_INFO", $this->pl->txt("upvoters_for_answer_from") . " " . $user_who_answered_item->getFirstname() . " " . $user_who_answered_item->getLastname() . " " . $this->pl->txt("on_the_question") . " " . $this->xase_item->getItemTitle());

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