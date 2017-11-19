<?php
require_once('./Services/Form/classes/class.ilPropertyFormGUI.php');
require_once "./Services/Object/classes/class.ilCommonActionDispatcherGUI.php";
require_once "./Services/Form/classes/class.ilLinkInputGUI.php";
require_once "./Services/Notes/classes/class.ilNoteGUI.php";

/**
 * Class xaseVotingFormGUI
 *
 * @author: Martin Studer  <ms@studer-raimann.ch>
 */
class xaseVotingFormGUI extends ilPropertyFormGUI {

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
	 * @var xaseQuestion
	 */
	protected $xase_question;
	/**
	 * @var xaseSetting
	 */
	public $xase_settings;
	/**
	 * @var ilObjAssistedExerciseFacade
	 */
	protected $obj_facade;


	public function __construct($arr_answers, xaseVotingGUI $parent_gui) {
		$this->obj_facade = ilObjAssistedExerciseFacade::getInstance($_GET['ref_id']);


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
			'answer_1' => $this->arr_answers[0]->getAnswertext(),
			'answer_2' => $this->arr_answers[1]->getAnswertext(),
			'question_id' => $this->arr_answers[0]->getQuestionId(),
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
			case xaseVotingGUI::RADIO_OPTION_EQUAL:
				//TODO Refactor Use Timestamp
				if($answer_1->getId() < $answer_2->getId()) {
					$upvote = $answer_1->getId();
					$downvote = $answer_2->getId();
				} else {
					$upvote = $answer_1->getId();
					$downvote = $answer_2->getId();
				}
				break;
			case $this->getInput('answer_1_id'):
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
		$voting->setQuestionId($this->getInput('question_id'));
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
		$voting_2->setQuestionId($this->getInput('question_id'));
		$voting_2->setCompAnswerId($upvote);
		$voting_2->setVotingType(xaseVoting::VOTING_TYPE_DOWN);
		$voting_2->store();

		return true;
	}

	protected function initAnswerList() {


		$a_formaction = $this->obj_facade->getCtrl()->getFormAction($this->parent_gui);
		$this->setFormAction($a_formaction);
		$this->setTarget('_top');
		$header = new ilFormSectionHeaderGUI();
		$header->setTitle($this->obj_facade->getLanguageValue('vote'));
		$this->addItem($header);

		$item = new ilHiddenInputGUI('question_id');
		$item->setValue($this->arr_answers[0]->getQuestionId());
		$this->addItem($item);

		$item = new ilHiddenInputGUI('answer_1_id');
		$item->setValue($this->arr_answers[0]->getId());
		$this->addItem($item);


		$item = new ilHiddenInputGUI('answer_2_id');
		$item->setValue($this->arr_answers[1]->getId());
		$this->addItem($item);

		$item = new ilNonEditableValueGUI($this->obj_facade->getLanguageValue('answer_1'), "answer_1");
		$item->setValue($this->arr_answers[0]->getAnswertext());
		$this->addItem($item);


		$button = $this->getCommentButton($this->arr_answers[0]->getId());
		$item = new ilCustomInputGUI('');
		$item->setHtml($button->getToolbarHTML());
		$this->addItem($item);

		$item = new ilNonEditableValueGUI($this->obj_facade->getLanguageValue('answer_2'), "answer_2");
		$item->setValue($this->arr_answers[1]->getAnswertext());
		$this->addItem($item);

		$button = $this->getCommentButton($this->arr_answers[1]->getId());
		$item = new ilCustomInputGUI('');
		$item->setHtml($button->getToolbarHTML());
		$this->addItem($item);

		$item_group = new ilRadioGroupInputGUI($this->obj_facade->getLanguageValue('vote_for'),"answer_id");
		$item_group->setRequired(true);
			$item = new ilRadioOption($this->obj_facade->getLanguageValue('answer_1'),$this->arr_answers[0]->getId());
			$item_group->addOption($item);

			$item = new ilRadioOption($this->obj_facade->getLanguageValue('answer_2'),$this->arr_answers[1]->getId());
			$item_group->addOption($item);

			$item = new ilRadioOption($this->obj_facade->getLanguageValue('answers_are_equal'),xaseVotingGUI::RADIO_OPTION_EQUAL);
			$item_group->addOption($item);



		$this->addItem($item_group);

		$this->addCommandButton(xaseAnswerListGUI::CMD_UPDATE, $this->obj_facade->getLanguageValue('save'));
	}


	/**
	 * @param int $answer_id
	 *
	 * @return ilLinkButton
	 */
	protected function getCommentButton($answer_id) {
		ilNoteGUI::initJavascript($this->obj_facade->getCtrl()->getLinkTargetByClass(array(
			"ilcommonactiondispatchergui",
			"ilnotegui"
		), "", "", true, false));
		ilNote::activateComments($this->obj_facade->getIlObjObId(), $answer_id, 'answer', true);
		$ajaxHash = ilCommonActionDispatcherGUI::buildAjaxHash(ilCommonActionDispatcherGUI::TYPE_REPOSITORY, $this->obj_facade->getIlObjRefId(), $this->pl->getPrefix(), $this->obj_facade->getIlObjObId(), 'answer', $answer_id);
		$redraw_js = "il.Object.redrawListItem(" . $this->obj_facade->getIlObjRefId() . ")";
		$on_click_js = "return " . ilNoteGUI::getListCommentsJSCall($ajaxHash, $redraw_js);

		$button = ilLinkButton::getInstance();
		$button->setUrl('#');
		$button->setOnClick($on_click_js);
		$button->setCaption($this->obj_facade->getLanguageValue('comments'),false);


		return $button;
	}
}