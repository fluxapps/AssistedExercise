<?php
/**
 * Class xaseVoteGUI
 *
 * @author  Martin Studer <ms@studer-raimann.ch>
 */

require_once './Customizing/global/plugins/Services/Repository/RepositoryObject/AssistedExercise/classes/Vote/class.xaseVoteFormGUI.php';
require_once './Customizing/global/plugins/Services/Repository/RepositoryObject/AssistedExercise/classes/ActiveRecords/class.xaseVotings.php';

class xaseVoteGUI {

	const CMD_STANDARD = 'compare';
	const CMD_UPDATE = 'update';
	const CMD_CANCEL = 'cancel';
	const CMD_DELETE_USERS_VOTINGS = 'deleteUsersVotingsOfItem';

	const RADIO_OPTION_EQUAL = -1;


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
	 * @var xaseItem
	 */
	protected $xase_item;
	/**
	 * @var xaseSettings
	 */
	public $xase_settings;



	public function __construct(ilObjAssistedExercise $assisted_exericse) {
		global $DIC;


		$this->dic = $DIC;
		$this->tpl = $this->dic['tpl'];
		$this->ctrl = $this->dic->ctrl();
		$this->access = new ilObjAssistedExerciseAccess();
		$this->pl = ilAssistedExercisePlugin::getInstance();
		$this->assisted_exercise = $assisted_exericse;
		$this->xase_settings = xaseSettings::where([ 'assisted_exercise_object_id' => $this->assisted_exercise->getId() ])->first();


		$this->ctrl->saveParameter($this, xaseItemGUI::ITEM_IDENTIFIER);
		$this->xase_item = new xaseItem($_GET[xaseItemGUI::ITEM_IDENTIFIER]);
	}


	public function executeCommand() {
		$nextClass = $this->ctrl->getNextClass();
		switch ($nextClass) {
			default:
				$this->performCommand();
		}
	}


	protected function performCommand() {
		$cmd = $this->ctrl->getCmd(self::CMD_STANDARD);
		switch ($cmd) {
			case self::CMD_STANDARD:
			case self::CMD_UPDATE:
			case self::CMD_DELETE_USERS_VOTINGS:
			case self::CMD_CANCEL:
				if ($this->access->hasReadAccess()) {
					$this->{$cmd}();
					break;
				} else {
					ilUtil::sendFailure(ilAssistedExercisePlugin::getInstance()->txt('permission_denied'), true);
					break;
				}
		}
	}

	protected function compare() {

		$arr_answers = xaseVotings::getUnvotedAnswersOfUser($this->assisted_exercise->getId(),$this->dic->user()->getId(), $this->xase_item->getId());
		$best_answer = xaseVotings::getBestVotedAnswerOfUser($this->assisted_exercise->getId(),$this->dic->user()->getId(), $this->xase_item->getId());

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
			$xaseVoteFormGUI = new xaseVoteFormGUI(array($answer_1,$answer_2), $this);
			$xaseVoteFormGUI->fillForm();
			$this->tpl->setContent($xaseVoteFormGUI->getHTML());
		} else {
			ilUtil::sendSuccess($this->pl->txt('no_more_answers_to_vote'), true);

			$this->ctrl->redirectByClass('xaseItemGUI', xaseItemGUI::CMD_STANDARD);
		}
	}


	public function update() {

		$arr_answers = array();
		$arr_answers[0] = xaseAnswer::findOrGetInstance($_POST['answer_1_id']);
		$arr_answers[1] = xaseAnswer::findOrGetInstance($_POST['answer_2_id']);

		$xaseVoteFormGUI = new xaseVoteFormGUI($arr_answers, $this);

		if ($xaseVoteFormGUI->updateObject()) {
			ilUtil::sendSuccess($this->pl->txt('changes_saved_success'), true);
			$this->ctrl->redirect($this, xaseVoteGUI::CMD_STANDARD);
		} else {
			$xaseVoteFormGUI->setValuesByPost();
			$xaseVoteFormGUI->fillForm();
			$this->tpl->setContent($xaseVoteFormGUI->getHTML());
		}
	}


	public function cancel() {
		$this->ctrl->redirectByClass('xaseItemGUI', xaseItemGUI::CMD_CANCEL);
	}

	public function deleteUsersVotingsOfItem() {

		xaseVotings::deleteVotingsOfUserByItemId($this->dic->user()->getId(),$_GET['item_id']);

		$this->ctrl->redirectByClass(array( 'ilObjAssistedExerciseGUI', 'xaseitemgui' ), xaseItemGUI::CMD_STANDARD);
	}
}