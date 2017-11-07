<?php

require_once("./Customizing/global/plugins/Services/Repository/RepositoryObject/AssistedExercise/classes/class.ilAnswerListInputGUI.php");

/**
 * Class xaseAnswerFormListGUI
 *
 * @author: Benjamin Seglias   <bs@studer-raimann.ch>
 */
class xaseAnswerFormListGUI extends ilPropertyFormGUI {

	/**
	 * @var ilObjAssistedExercise
	 */
	public $assisted_exercise;
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


	public function __construct(ilObjAssistedExercise $assisted_exericse, xaseAnswerListGUI $parent_gui, $xase_item) {
		global $DIC;
		$this->dic = $DIC;
		$this->tpl = $this->dic['tpl'];
		$this->tabs = $DIC->tabs();
		$this->ctrl = $this->dic->ctrl();
		$this->access = new ilObjAssistedExerciseAccess();
		$this->pl = ilAssistedExercisePlugin::getInstance();
		$this->assisted_exercise = $assisted_exericse;
		$this->parent_gui = $parent_gui;
		$this->xase_item = $xase_item;

		$this->tpl->addJavaScript('./Customizing/global/plugins/Services/Repository/RepositoryObject/AssistedExercise/templates/js/answerformlist.js');
		$this->initAnswerList();

		parent::__construct();
	}


	protected function getAnswersForVoting() {
		$answer_status[] = xaseAnswer::ANSWER_STATUS_SUBMITTED;
		$answer_status[] = xaseAnswer::ANSWER_STATUS_RATED;
		$answer_status[] = xaseAnswer::ANSWER_STATUS_M2_CAN_BE_VOTED;
		//TODO abfangen dass nur bereits existerende angezeigt werden
		$answers = xaseAnswer::where(array( 'item_id' => $this->xase_item->getId(), 'answer_status' => $answer_status ), array(
			'item_id' => '=',
			'answer_status' => 'IN'
		))->get();

		return $answers;
	}

	//TODO check if necessary
	/*    protected function getCommentsForAnswer($xase_answer) {
			$comments = xaseComment::where(array('answer_id' => $xase_answer->getId()))->get();
			return $comments;
		}*/

	protected function hasUserVoted() {
		$answers_for_current_item = xaseAnswer::where(array( 'item_id' => $this->xase_item->getId() ))->get();
		$votings_from_current_user = xaseVoting::where(array( 'user_id' => $this->dic->user()->getId() ))->get();
		$answers_ids = [];
		foreach ($answers_for_current_item as $answer) {
			$answers_ids[] = $answer->getId();
		}
		if (!empty($votings_from_current_user)) {
			foreach ($votings_from_current_user as $voting) {
				if (array_key_exists($voting->getAnswerId(), $answers_for_current_item)) {
					return true;
				}
			}
		}

		return false;
	}


	public function fillForm() {
		$array = array();
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


	//TODO save comment data
	public function fillObject() {
		if (!$this->checkInput()) {
			return false;
		}
		foreach ($_POST['answer'] as $id => $data) {
			if (is_array($data)) {
				if (array_key_exists('is_voted_by_current_user', $data) && $data['is_voted_by_current_user'] == 1) {
					$answer_id = $data['answer_id'];
					$answer = xaseAnswer::where(array( 'id' => $answer_id ))->first();
					$answer->setNumberOfUpvotings($answer->getNumberOfUpvotings() + 1);
					$answer->store();
					if (!$this->hasVotedForAnswer($answer->getId())) {
						$xase_voting = new xaseVoting();
						$xase_voting->setAnswerId($answer->getId());
						$xase_voting->setUserId($this->dic->user()->getId());
						$xase_voting->store();
					}
					break;
				}
			}
		}
		if (!$this->hasUserVoted()) {
			ilUtil::sendFailure($this->pl->txt("please_vote_for_one_answer"), true);

			return false;
		}

		return true;
	}


	protected function resetPreviousVoting($item_id) {
		$previousVoting = xaseVoting::where(array( 'user_id' => $this->dic->user()->getId() ))->first();
	}


	protected function hasVotedForAnswer($answer_id) {
		$xaseVoting = xaseVoting::where(array( 'answer_id' => $answer_id, 'user_id' => $this->dic->user()->getId() ))->first();
		if (empty($xaseVoting)) {
			return $hasVoted = false;
		}

		return $hasVoted = true;
	}

	/*
	 * Usage in: after answer update to check if the user gets redirected to the item table list gui or to the list of answers
	 * Questions:
	 * -Should the user be redirected to the list of answers in all cases? Also if he answered it for the first time?
	 *  -yes for the voting after the first time
	 *  -afterwards?
	 *      -yes to adopt the voting
	 */
	//TODO check if used
	protected function is_already_answered_by_user() {
		$user_answers = xaseAnswer::where(array( 'item_id' => $this->xase_item->getId(), 'user_id' => $this->dic->user()->getId() ))->get();
		if (count($user_answers) > 0) {
			return true;
		}

		return false;
	}


	protected function initAnswerList() {
		$answers = $this->getAnswersForVoting();
		if (!empty($answers)) {
			$this->setFormAction($this->ctrl->getFormAction($this->parent_gui));
			$this->setTarget('_top');
			$header = new ilFormSectionHeaderGUI();
			$header->setTitle($this->pl->txt('view_answers'));
			$this->addItem($header);

			if (!$this->hasUserVoted()) {
				ilUtil::sendInfo($this->pl->txt("pleas_vote_for_the_best_answer"));
			}

			$answer_list_input_gui = new ilAnswerListInputGUI();
			$answer_list_input_gui->setXaseItem($this->xase_item);
			$answer_list_input_gui->setAnswers($answers);
			$this->addItem($answer_list_input_gui);

			/*            foreach($answers as $answer) {
							$answer_list_input_gui = new ilAnswerListInputGUI();
							$answer_list_input_gui->setXaseItem($this->xase_item);
							$answer_list_input_gui->setXaseAnswer($answer);
							$comments_for_answer = $this->getCommentsForAnswer($answer);
							$answer_list_input_gui->setComments($comments_for_answer);
							$this->addItem($answer_list_input_gui);
						}*/

			$this->addCommandButton(xaseAnswerListGUI::CMD_UPDATE, $this->pl->txt('save'));
			if ($this->hasUserVoted()) {
				$this->addCommandButton(xaseAnswerListGUI::CMD_CANCEL, $this->pl->txt("cancel"));
			}
		}
	}
}