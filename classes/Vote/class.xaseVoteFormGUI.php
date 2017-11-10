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
		//$this->xase_item = new xaseItem($_GET['item_id']);
		//$this->xase_settings = xaseSettings::where([ 'assisted_exercise_object_id' => $this->assisted_exercise->getId() ])->first();

		//$this->tpl->addJavaScript('./Customizing/global/plugins/Services/Repository/RepositoryObject/AssistedExercise/templates/js/answerformlist.js');
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
	 * @return bool|string
	 */
	public function updateObject() {
		if (!$this->fillObject()) {
			return false;
		}

		return true;
	}

	/*
	protected function has_current_user_voted_for_answer($answer) {
		if(!empty(xaseVoting::where(array('answer_id' => $answer->getId(), 'user_id' => $this->dic->user()->getId())))) {
			return true;
		}
		return false;
	}*/

	public function fillObject() {

		if(!$this->checkInput()) {
			return false;
		}


		if(empty($this->getInput('answer_id'))) {
			ilUtil::sendFailure($this->pl->txt("please_choose_an_answer"), true);
			return false;
		}


		//Up Vote / Down Vote
		if($this->getInput('answer_id') == $this->getInput('answer_1_id')) {
			$upvote = $this->getInput('answer_1_id');
			$downvote = $this->getInput('answer_2_id');
		}

		if($this->getInput('answer_id') == $this->getInput('answer_2_id')) {
			$upvote = $this->getInput('answer_2_id');
			$downvote = $this->getInput('answer_1_id');
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
		//print_r($voting);

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

	/*
	protected function resetPreviousVoting($item_id) {
		$previousVoting = xaseVoting::where(array( 'item_id' => $item_id, 'user_id' => $this->dic->user()->getId() ))->first();
		if(!empty($previousVoting)) {
			$votedAnswer = xaseAnswer::where(array('id' => $previousVoting->getAnswerId()))->first();
			$current_number_of_upvotings = $previousVoting->getNumberOfUpvotings();
			$votedAnswer->setNumberOfUpvotings(--$current_number_of_upvotings);
			$previousVoting->delete();
		}
		return;
	}*/

	/*
	protected function hasVotedForAnswer($answer_id) {
		$xaseVoting = xaseVoting::where(array( 'answer_id' => $answer_id, 'user_id' => $this->dic->user()->getId() ))->first();
		if (empty($xaseVoting)) {
			return $hasVoted = false;
		}

		return $hasVoted = true;
	}*/

	//TODO check if used
	/*
	protected function is_already_answered_by_user() {
		$user_answers = xaseAnswer::where(array( 'item_id' => $this->xase_item->getId(), 'user_id' => $this->dic->user()->getId() ))->get();
		if (count($user_answers) > 0) {
			return true;
		}

		return false;
	}*/

	protected function initAnswerList() {

		$a_formaction = $this->ctrl->getFormAction($this->parent_gui);
		$this->setFormAction($a_formaction);
		$this->setTarget('_top');
		$header = new ilFormSectionHeaderGUI();
		$header->setTitle($this->pl->txt('view_answers'));
		$this->addItem($header);

		$item = new ilHiddenInputGUI('item_id');
		$item->setValue($this->arr_answers[0]->getItemId());
		$this->addItem($item);

		$item = new ilHiddenInputGUI('answer_1_id');
		$item->setValue($this->arr_answers[0]->getId());
		$this->addItem($item);


		//$notesGUI = new ilNoteGUI($this->parent_gui->assisted_exercise->getId(), $this->arr_answers[0]->getId());
		//$notesGUI->enablePublicNotes(true);
		//$notesGUI->enablePublicNotesDeletion(true);
		//$notesGUI->activateComments();

		//$this->ctrl->setParameterByClass("ilnotegui", "record_id", $this->parent_gui->assisted_exercise->getId());
		//$this->ctrl->setParameterByClass("ilnotegui", "rep_id", $this->arr_answers[0]->getId());

		ilNote::activateComments($this->parent_gui->assisted_exercise->getId(), $this->arr_answers[0]->getId(), 'answer', true);

		//echo $notesGUI->getOnlyCommentsHtml(); exit;


		$ajaxHash = ilCommonActionDispatcherGUI::buildAjaxHash(ilCommonActionDispatcherGUI::TYPE_REPOSITORY, $this->parent_gui->assisted_exercise->getRefId(), $this->pl->getPrefix(), $this->parent_gui->assisted_exercise->getId(),'answer',$this->arr_answers[0]->getId());

		//echo $ajaxHash; exit;

		$redraw_js = "il.Object.redrawListItem(".$this->parent_gui->assisted_exercise->getRefId().")";



		ilNoteGUI::initJavascript($this->ctrl->getLinkTargetByClass(array(
			"ilcommonactiondispatchergui",
			"ilnotegui"
		), "", "", true, false));

		//$item = new ilLinkInputGUI('comment', $ajaxHash);
		//$this->addItem($item);

		//$link = "<a href='#' onclick=\\">comment</a>";

		//http://172.28.128.4/ilias.php?ref_id=1&cmd=frameset&cmdClass=ilrepositorygui&cmdNode=1b&baseClass=ilRepositoryGUI#



		$button = ilLinkButton::getInstance();
		$button->setUrl('#');
		$button->setOnClick("return ".ilNoteGUI::getListCommentsJSCall($ajaxHash, $redraw_js));
		$button->setCaption('asdfsdf');

		$item = new ilCustomInputGUI('');
		$item->setHtml($button->getToolbarHTML());

		$this->addItem($item);

			/*


		$item->setli

$props[] = array("alert" => false,
			"property" => 'lng comment',
			"value" =>
			"newline" => $nl);*/


		$item = new ilHiddenInputGUI('answer_2_id');
		$item->setValue($this->arr_answers[1]->getId());
		$this->addItem($item);

		$item = new ilNonEditableValueGUI($this->pl->txt('answer_1'), "answer_1");
		$item->setValue($this->arr_answers[0]->getBody());
		$this->addItem($item);

		$item = new ilNonEditableValueGUI($this->pl->txt('answer_2'), "answer_2");
		$item->setValue($this->arr_answers[1]->getBody());
		$this->addItem($item);

		$item_group = new ilRadioGroupInputGUI($this->pl->txt('vote_for'),"answer_id");
		//$item_group->setValue(1);
			$item = new ilRadioOption($this->pl->txt('answer_1'),$this->arr_answers[0]->getId());
			//$item->setValue($this->arr_answers[0]->getId());
			$item_group->addOption($item);

			$item = new ilRadioOption($this->pl->txt('answer_2'),$this->arr_answers[1]->getId());
			//$item->setValue(2);
			$item_group->addOption($item);

		$this->addItem($item_group);

		$this->addCommandButton(xaseAnswerListGUI::CMD_UPDATE, $this->pl->txt('save'));
	}
}