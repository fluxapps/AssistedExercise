<?php
require_once "./Services/Object/classes/class.ilCommonActionDispatcherGUI.php";
require_once "./Services/Form/classes/class.ilLinkInputGUI.php";
require_once "./Services/Notes/classes/class.ilNoteGUI.php";

/*
require_once("./Customizing/global/plugins/Services/Repository/RepositoryObject/AssistedExercise/classes/class.ilAnswerListInputGUI.php");
require_once("./Customizing/global/plugins/Services/Repository/RepositoryObject/AssistedExercise/classes/ActiveRecords/class.xaseVoting.php");
require_once("./Customizing/global/plugins/Services/Repository/RepositoryObject/AssistedExercise/classes/ActiveRecords/class.xaseComment.php");
*/

/**
 * Class xaseVoteFormGUI
 *
 * @author: Martin Studer  <ms@studer-raimann.ch>
 */
class xaseVoteFormGUI extends ilPropertyFormGUI {

	/**
	 * @var xaseAnswer[]
	 */
	public $arr_answers;
	/**
	 * @var xaseAnswerListGUI
	 */
	protected $parent_gui;
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


	public function __construct($arr_answers, xaseVoteGUI $parent_gui) {
		global $DIC;
		$this->dic = $DIC;
		$this->tpl = $this->dic['tpl'];
		$this->tabs = $DIC->tabs();
		$this->ctrl = $this->dic->ctrl();
		$this->access = new ilObjAssistedExerciseAccess();
		$this->pl = ilAssistedExercisePlugin::getInstance();
		$this->arr_answers = $arr_answers;
		$this->parent_gui = $parent_gui;

		$this->initAnswerList();

		parent::__construct();
	}


	public function fillForm() {
		$array = array(
			'answer_1_id' => $this->arr_answers[0]->getId(),
			'answer_2_id' => $this->arr_answers[1]->getId(),
			'answer_1' => $this->arr_answers[0]->getBody(),
			'answer_2' => $this->arr_answers[1]->getBody(),
			'item_id' => $this->arr_answers[0]->getItemId(),
		);
		//$this->setValuesByArray($array, true);
		$this->setValuesByArray($array);
	}


	/**
	 * @return bool
	 */
	public function updateObject() {
		if(!$this->checkInput()) {
			return false;
		}

		$answer_1 = new xaseAnswer($this->getInput('answer_1_id'));
		$answer_2 = new xaseAnswer($this->getInput('answer_2_id'));

		switch($this->getInput('answer_id')) {
			case xaseVoteGUI::RADIO_OPTION_EQUAL:
				//TODO Refactor Use Timestamp
				if($answer_1->getId() < $answer_2->getId()) {
					$upvote = $answer_1->getId();
					$downvote = $answer_2->getId();
				} else {
					$upvote = $answer_1->getId();
					$downvote = $answer_2->getId();
				}
				break;
			case $answer_1->getId();
				$upvote = $answer_1->getId();
				$downvote = $answer_2->getId();
				break;
			case $this->getInput('answer_2_id'):
				$upvote = $answer_1->getId();
				$downvote = $answer_2->getId();
				break;
		}

		/**
		 * @var xaseVoting $voting
		 */
		$voting = xaseVoting::where(array('user_id' => $this->dic->user()->getId(), 'answer_id' => $upvote))->first();

		if(!is_object($voting)) {
			$voting = new xaseVoting();
		}

		$voting->setUserId($this->dic->user()->getId());
		$voting->setAnswerId($upvote);
		$voting->setItemId($this->getInput('item_id'));
		$voting->setCompAnswerId($downvote);
		$voting->setVotingType(xaseVoting::VOTING_TYPE_UP);
		$voting->store();

		/**
		 * @var xaseVoting $voting_2
		 */
		$voting_2 = xaseVoting::where(array('user_id' => $this->dic->user()->getId(), 'answer_id' => $downvote))->first();

		if(!is_object($voting_2)) {
			$voting_2 = new xaseVoting();
		}

		$voting_2->setUserId($this->dic->user()->getId());
		$voting_2->setAnswerId($downvote);
		$voting_2->setItemId($this->getInput('item_id'));
		$voting_2->setCompAnswerId($upvote);
		$voting_2->setVotingType(xaseVoting::VOTING_TYPE_DOWN);
		$voting_2->store();

		return true;
	}

	protected function initAnswerList() {


		$a_formaction = $this->ctrl->getFormAction($this->parent_gui);
		$this->setFormAction($a_formaction);
		$this->setTarget('_top');
		$header = new ilFormSectionHeaderGUI();
		$header->setTitle($this->pl->txt('vote'));
		$this->addItem($header);

		$item = new ilHiddenInputGUI('item_id');
		$item->setValue($this->arr_answers[0]->getItemId());
		$this->addItem($item);

		$item = new ilHiddenInputGUI('answer_1_id');
		$item->setValue($this->arr_answers[0]->getId());
		$this->addItem($item);


		$item = new ilHiddenInputGUI('answer_2_id');
		$item->setValue($this->arr_answers[1]->getId());
		$this->addItem($item);

		$item = new ilNonEditableValueGUI($this->pl->txt('answer_1'), "answer_1");
		$item->setValue($this->arr_answers[0]->getBody());
		$this->addItem($item);


		$button = $this->getCommentButton($this->arr_answers[0]->getId());
		$item = new ilCustomInputGUI('');
		$item->setHtml($button->getToolbarHTML());
		$this->addItem($item);

		$item = new ilNonEditableValueGUI($this->pl->txt('answer_2'), "answer_2");
		$item->setValue($this->arr_answers[1]->getBody());
		$this->addItem($item);

		$button = $this->getCommentButton($this->arr_answers[1]->getId());
		$item = new ilCustomInputGUI('');
		$item->setHtml($button->getToolbarHTML());
		$this->addItem($item);

		$item_group = new ilRadioGroupInputGUI($this->pl->txt('vote_for'),"answer_id");
		$item_group->setRequired(true);
			$item = new ilRadioOption($this->pl->txt('answer_1'),$this->arr_answers[0]->getId());
			$item_group->addOption($item);

			$item = new ilRadioOption($this->pl->txt('answer_2'),$this->arr_answers[1]->getId());
			$item_group->addOption($item);

			$item = new ilRadioOption($this->pl->txt('answers_are_equal'),xaseVoteGUI::RADIO_OPTION_EQUAL);
			$item_group->addOption($item);



		$this->addItem($item_group);

		$this->addCommandButton(xaseAnswerListGUI::CMD_UPDATE, $this->pl->txt('save'));
	}


	/**
	 * @param int $answer_id
	 *
	 * @return ilLinkButton
	 */
	protected function getCommentButton($answer_id) {
		ilNoteGUI::initJavascript($this->ctrl->getLinkTargetByClass(array(
			"ilcommonactiondispatchergui",
			"ilnotegui"
		), "", "", true, false));
		ilNote::activateComments($this->parent_gui->assisted_exercise->getId(), $answer_id, 'answer', true);
		$ajaxHash = ilCommonActionDispatcherGUI::buildAjaxHash(ilCommonActionDispatcherGUI::TYPE_REPOSITORY, $this->parent_gui->assisted_exercise->getRefId(), $this->pl->getPrefix(), $this->parent_gui->assisted_exercise->getId(), 'answer', $answer_id);
		$redraw_js = "il.Object.redrawListItem(" . $this->parent_gui->assisted_exercise->getRefId() . ")";
		$on_click_js = "return " . ilNoteGUI::getListCommentsJSCall($ajaxHash, $redraw_js);

		$button = ilLinkButton::getInstance();
		$button->setUrl('#');
		$button->setOnClick($on_click_js);
		$button->setCaption($this->pl->txt('comments'),false);


		return $button;
	}
}