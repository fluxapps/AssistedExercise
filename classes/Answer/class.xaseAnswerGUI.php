<?php

/**
 * Class xaseAnswerGUI
 *
 * @author  Benjamin Seglias <bs@studer-raimann.ch>
 */

require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/AssistedExercise/classes/Answer/class.xaseAnswerFormGUI.php');
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/AssistedExercise/classes/Answer/class.xaseAnswerFormListGUI.php');
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/AssistedExercise/classes/Hint/class.xaseHintGUI.php');
/**
 *
 * @ilCtrl_Calls  xaseAnswerGUI: xaseHintGUI
 *
 */
class xaseAnswerGUI {

	const CMD_STANDARD = 'edit';
	const CMD_UPDATE = 'update';
	const CMD_UPDATE_AND_SET_STATUS_TO_VOTE = 'upadteAndSetStatusToVote';
	const CMD_UPDATE_AND_SET_STATUS_TO_SUBMITED = 'upadteAndSetStatusToSubmited';
	const CMD_CANCEL = 'cancel';
	const CMD_SHOW = 'show';
	const CMD_QUESTION_ROUND = 'showQuestionRound';

	 /**
	 * @var ilObjAssistedExerciseFacade
	 */
	protected $obj_facade;
	/**
	 * @var xaseAnswerAccess
	 */
	protected $answer_access;


	public function __construct() {
		$this->obj_facade = ilObjAssistedExerciseFacade::getInstance($_GET['ref_id']);

		$answer = xaseAnswer::findOrGetInstance($_GET['answer_id']);

		$this->answer_access = new xaseAnswerAccess($answer,$this->obj_facade->getUser()->getId());


		$this->obj_facade->getCtrl()->setParameter($this,'mode',$_GET['mode']);
	}
/*
	public function __construct(ilObjAssistedExercise $assisted_exericse) {
		global $DIC;
		$this->dic = $DIC;
		$this->tpl = $this->dic['tpl'];
		$this->tabs = $DIC->tabs();
		$this->ctrl = $this->dic->ctrl();
		$this->access = new ilObjAssistedExerciseAccess();
		$this->pl = ilAssistedExercisePlugin::getInstance();
		$this->assisted_exercise = $assisted_exericse;
		$this->xase_settings = xaseSetting::where([ 'assisted_exercise_object_id' => $this->assisted_exercise->getId() ])->first();
		//$this->mode_settings = $this->getModeSetting($this->xase_settings->getModus());
		$this->xase_question = new xaseQuestion($_GET['question_id']);

		$this->xase_answer = xaseAnswer::findOrGetInstance($_GET['answer_id']);
		$this->obj_facade->getCtrl()->saveParameter($this,self::ANSWER_IDENTIFIER);



		//parent::__construct();
	}*/


	public function executeCommand() {
		$nextClass = $this->obj_facade->getCtrl()->getNextClass();
		switch ($nextClass) {
			case 'xasehintgui':
				$this->obj_facade->forwardCommandByClass('xaseHintGUI','index');
				break;
			default:
				$this->obj_facade->getTabsGUI()->activateTab(xaseQuestionGUI::CMD_INDEX);
				$this->performCommand();
		}
	}


	protected function performCommand() {
		$cmd = $this->obj_facade->getCtrl()->getCmd(self::CMD_STANDARD);
		switch ($cmd) {
			case self::CMD_STANDARD:
			case self::CMD_SHOW:
			case self::CMD_UPDATE:
			case self::CMD_CANCEL:
			case self::CMD_UPDATE_AND_SET_STATUS_TO_VOTE:
			case self::CMD_UPDATE_AND_SET_STATUS_TO_SUBMITED:
			case self::CMD_QUESTION_ROUND:
				//if ($this->access->hasReadAccess()) {
					$this->{$cmd}();
					break;
				//} else {
				//	ilUtil::sendFailure(ilAssistedExercisePlugin::getInstance()->txt('permission_denied'), true);
				//	break;
				//}
		}
	}

/*
	protected function getAnswer() {
		$xaseAnswer = xaseAnswer::where(array(
			'question_id' => $this->xase_question->getId(),
			'user_id' => $this->dic->user()->getId()
		), array( 'question_id' => '=', 'user_id' => '=' ))->first();
		if (empty($xaseAnswer)) {
			$xaseAnswer = new xaseAnswer();
		}

		return $xaseAnswer;
	}
*/

	protected function canVote() {
		$current_date = date('Y-m-d h:i:s', time());
		$current_date_datetime = DateTime::createFromFormat('Y-m-d H:i:s', $current_date);
		/*/$start_voting_date_datetime = DateTime::createFromFormat('Y-m-d H:i:s', $this->mode_settings->getStartVotingDate());
		if ($start_voting_date_datetime->getTimestamp() <= $current_date_datetime->getTimestamp()
			|| $this->mode_settings->getStartVotingDate() == "0000-00-00 00:00:00") {
			return true;
		} else {
			return true;
		}*/
	}

	public function showQuestionRound() {
		$arr_questions = xaseQuestions::getUnansweredQuestionsOfUser($this->obj_facade->getIlObjObId(),$this->obj_facade->getUser()->getId());

		if(count($arr_questions) == 0) {
			ilUtil::sendInfo($this->obj_facade->getLanguageValue('all_questions_answered'),true);
			$this->obj_facade->getCtrl()->redirectByClass(xaseQuestionGUI::class, xaseQuestionGUI::CMD_INDEX);
		}

		ilUtil::sendInfo(sprintf($this->obj_facade->getLanguageValue('question_open'),count($arr_questions)),true);

		$this->obj_facade->getCtrl()->setParameter($this,'question_id',$arr_questions[0]->getId());
		$this->obj_facade->getCtrl()->setParameter($this,'mode','questionnaire');
		$this->obj_facade->getCtrl()->redirect($this,self::CMD_STANDARD);

	}


	public function edit() {
		

		$this->obj_facade->getTabsGUI()->activateTab(xaseQuestionGUI::CMD_INDEX);
		$form = new xaseAnswerFormGUI($this);
		$form->fillForm();

		if(xaseAnswerAccess::hasCreateAccess($this->obj_facade,$this->obj_facade->getUser()->getId(),$_GET['question_id'])
		|| $this->answer_access->hasWriteAccess()) {
			$form->addCommandButton(xaseAnswerGUI::CMD_UPDATE_AND_SET_STATUS_TO_VOTE, $this->obj_facade->getLanguageValue('submit_for_assessment'));
			$form->addCommandButton(xaseAnswerGUI::CMD_UPDATE, $this->obj_facade->getLanguageValue('save'));
		}

		$this->obj_facade->getTpl()->setContent($form->getHTML());
		$this->obj_facade->getTpl()->show();
	}

	//TODO Refactor
	public function show() {
		if($answer_id = $_GET['answer_id']) {

			$answer = new xaseAnswer($_GET['answer_id']);


			if(($answer->getAnswerStatus() == xaseAnswer::ANSWER_STATUS_CAN_BE_VOTED
				&& $this->obj_facade->getSetting()->getVotingEnabled())
				||  $answer->getUserId() == $this->obj_facade->getUser()->getId()) {
				$this->obj_facade->getTabsGUI()->activateTab(xaseQuestionGUI::CMD_INDEX);
				$xaseAnswerFormGUI = new xaseAnswerFormGUI($this);
				$xaseAnswerFormGUI->fillForm();
				$this->obj_facade->getTpl()->setContent($xaseAnswerFormGUI->getHTML());
				$this->obj_facade->getTpl()->show();
			} else {
				ilUtil::sendFailure(ilAssistedExercisePlugin::getInstance()->txt('permission_denied'), true);
				$this->obj_facade->getCtrl()->redirectByClass('xaseQuestionGUI', xaseQuestionGUI::CMD_INDEX);
			}
		} else {

			ilUtil::sendFailure(ilAssistedExercisePlugin::getInstance()->txt('permission_denied'), true);
			$this->obj_facade->getCtrl()->redirectByClass('xaseQuestionGUI', xaseQuestionGUI::CMD_INDEX);
		}
	}

	public function upadteAndSetStatusToVote() {
		$this->update(xaseAnswer::ANSWER_STATUS_CAN_BE_VOTED);
	}

	public function upadteAndSetStatusToSubmited() {
		$this->update(xaseAnswer::ANSWER_STATUS_CAN_BE_VOTED);
	}


	public function update($status = xaseAnswer::ANSWER_STATUS_ANSWERED) {
		$this->obj_facade->getTabsGUI()->activateTab(xaseQuestionGUI::CMD_INDEX);
		$xaseAnswerFormGUI = new xaseAnswerFormGUI($this, $this->assisted_exercise, $this->xase_question);
		if ($xaseAnswerFormGUI->updateObject($status)) {
			ilUtil::sendSuccess($this->obj_facade->getLanguageValue('changes_saved_success'), true);

			if($_GET['mode'] == 'questionnaire'){
				$this->obj_facade->getCtrl()->redirectByClass(xaseAnswerGUI::class, xaseAnswerGUI::CMD_QUESTION_ROUND);
			}
			$this->obj_facade->getCtrl()->redirectByClass(xaseQuestionGUI::class, xaseQuestionGUI::CMD_INDEX);


		} else {
			$xaseAnswerFormGUI->setValuesByPost();
			$xaseAnswerFormGUI->fillTaskInput();
			$this->obj_facade->getTpl()->setContent($xaseAnswerFormGUI->getHTML());
			$this->obj_facade->getTpl()->show();
		}
	}


	public function cancel() {
		$this->obj_facade->getCtrl()->redirectByClass('xaseQuestionGUI', xaseQuestionGUI::CMD_CANCEL);
	}


	protected function isDisposalDateExpired() {
		$current_date = date('Y-m-d h:i:s', time());

		$current_date_datetime = DateTime::createFromFormat('Y-m-d H:i:s', $current_date);
		/*$disposal_date_datetime = DateTime::createFromFormat('Y-m-d H:i:s', $this->mode_settings->getDisposalDate());

		if (($disposal_date_datetime->getTimestamp() > $current_date_datetime->getTimestamp())
			|| $this->mode_settings->getDisposalDate() == "0000-00-00 00:00:00") {
			return false;
		} else {
			return true;
		}*/
	}
}