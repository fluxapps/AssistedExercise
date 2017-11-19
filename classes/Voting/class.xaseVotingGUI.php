<?php
/**
 * Class xaseVotingGUI
 *
 * @author  Martin Studer <ms@studer-raimann.ch>
 */

require_once './Customizing/global/plugins/Services/Repository/RepositoryObject/AssistedExercise/classes/Voting/class.xaseVotingFormGUI.php';
require_once './Customizing/global/plugins/Services/Repository/RepositoryObject/AssistedExercise/classes/Voting/class.xaseVotings.php';

class xaseVotingGUI {

	const CMD_STANDARD = 'compare';
	const CMD_UPDATE = 'update';
	const CMD_CANCEL = 'cancel';
	const CMD_DELETE_USERS_VOTINGS = 'deleteUsersVotingsOfItem';

	const RADIO_OPTION_EQUAL = -1;


	/**
	 * @var ilObjAssistedExerciseFacade
	 */
	protected $obj_facade;

	/**
	 * @var xaseQuestion
	 */
	protected $xase_question;



	public function __construct() {
		$this->obj_facade = ilObjAssistedExerciseFacade::getInstance($_GET['ref_id']);

		$this->obj_facade->getCtrl()->saveParameter($this, xaseQuestionGUI::ITEM_IDENTIFIER);
		$this->xase_question = new xaseQuestion($_GET[xaseQuestionGUI::ITEM_IDENTIFIER]);
	}


	public function executeCommand() {
		$nextClass = $this->obj_facade->getCtrl()->getNextClass();
		switch ($nextClass) {
			default:
				$this->performCommand();
		}
	}


	protected function performCommand() {
		$cmd = $this->obj_facade->getCtrl()->getCmd(self::CMD_STANDARD);

		switch ($cmd) {
			case self::CMD_STANDARD:
			case self::CMD_UPDATE:
			case self::CMD_DELETE_USERS_VOTINGS:
			case self::CMD_CANCEL:
				//if ($this->access->hasReadAccess()) {
					$this->{$cmd}();
					break;
				//} else {
				//	ilUtil::sendFailure(ilAssistedExercisePlugin::getInstance()->txt('permission_denied'), true);
				//	break;
				//}
		}
	}

	protected function compare() {

		$arr_answers = xaseVotings::getUnvotedAnswersOfUser($this->obj_facade->getIlObjObId(),$this->obj_facade->getUser()->getId(), $this->xase_question->getId());
		$best_answer = xaseVotings::getBestVotedAnswerOfUser($this->obj_facade->getIlObjObId(),$this->obj_facade->getUser()->getId(), $this->xase_question->getId());

		$answer_1 = NULL;
		$answer_2 = NULL;

		if($best_answer) {
			$answer_1 = $best_answer;
			$answer_2 = $arr_answers[0];
		} else {
			$answer_1 = $arr_answers[0];
			$answer_2 = $arr_answers[1];
		}

		if(is_object($answer_1) && is_object($answer_2)) {
			$xaseVotingFormGUI = new xaseVotingFormGUI(array($answer_1,$answer_2), $this);
			$xaseVotingFormGUI->fillForm();
			$this->obj_facade->getTpl()->setContent($xaseVotingFormGUI->getHTML());
		} else {
			ilUtil::sendSuccess($this->obj_facade->getLanguageValue('no_more_answers_to_vote'), true);

			$this->obj_facade->getCtrl()->redirectByClass('xaseQuestionGUI', xaseQuestionGUI::CMD_INDEX);
		}
	}


	public function update() {

		$arr_answers = array();
		$arr_answers[0] = xaseAnswer::findOrGetInstance($_POST['answer_1_id']);
		$arr_answers[1] = xaseAnswer::findOrGetInstance($_POST['answer_2_id']);

		$xaseVotingFormGUI = new xaseVotingFormGUI($arr_answers, $this);

		if ($xaseVotingFormGUI->updateObject()) {
			ilUtil::sendSuccess($this->obj_facade->getLanguageValue('changes_saved_success'), true);
			$this->obj_facade->getCtrl()->redirect($this, xaseVotingGUI::CMD_STANDARD);
		} else {
			$xaseVotingFormGUI->setValuesByPost();
			$xaseVotingFormGUI->fillForm();
			$this->obj_facade->getTpl()->setContent($xaseVotingFormGUI->getHTML());
		}
	}


	public function cancel() {
		$this->obj_facade->getCtrl()->redirectByClass('xaseQuestionGUI', xaseQuestionGUI::CMD_CANCEL);
	}

	public function deleteUsersVotingsOfItem() {

		xaseVotings::deleteVotingsOfUserByItemId($this->obj_facade->getUser()->getId(),$_GET['question_id']);

		$this->obj_facade->getCtrl()->redirectByClass('xaseQuestionGUI', xaseQuestionGUI::CMD_INDEX);
	}
}