<?php
require_once 'class.xaseUsedHintLevel.php';
/**
* Class xaseHintGUI
*
* @author  Martin Studer <ms@studer-raimann.ch>
*/

class xaseHintGUI {

	const CMD_INDEX = 'index';
	const CMD_SHOW_HINT = 'showHint';


	const CMD_UPDATE = 'update';
	const CMD_UPDATE_AND_SET_STATUS_TO_VOTE = 'upadteAndSetStatusToVote';
	const CMD_UPDATE_AND_SET_STATUS_TO_SUBMITED = 'upadteAndSetStatusToSubmited';
	const CMD_CANCEL = 'cancel';
	const CMD_SHOW = 'show';
	/**
	 * @var ilObjAssistedExerciseFacade
	 */
	protected $obj_facade;
	/**
	 * @var xaseQuestion
	 */
	protected $obj_question;
	/**
	 * @var xaseHint
	 */
	protected $obj_hint;


	public function __construct() {
		$this->obj_facade = ilObjAssistedExerciseFacade::getInstance($_GET['ref_id']);
	}

	public function executeCommand() {
		$nextClass = $this->obj_facade->getCtrl()->getNextClass();
		switch ($nextClass) {
			default:
				$this->performCommand();
		}
	}


	protected function performCommand() {
		$cmd = $this->obj_facade->getCtrl()->getCmd(self::CMD_INDEX);
		switch ($cmd) {
			case self::CMD_INDEX:
			case self::CMD_SHOW_HINT:

			case self::CMD_SHOW:
			case self::CMD_UPDATE:
			case self::CMD_CANCEL:
			case self::CMD_UPDATE_AND_SET_STATUS_TO_VOTE:
			case self::CMD_UPDATE_AND_SET_STATUS_TO_SUBMITED:
				//if ($this->access->hasReadAccess()) {
				$this->{$cmd}();
				break;
			//} else {
			//	ilUtil::sendFailure(ilAssistedExercisePlugin::getInstance()->txt('permission_denied'), true);
			//	break;
			//}
		}
	}

	public function index() {
		ilUtil::sendInfo($this->obj_facade->getLanguageValue('use_hints_info'));

		$this->obj_question = xaseQuestion::findOrGetInstance($_GET['question_id']);
		$this->obj_hint = xaseHint::where(array('question_id' => $_GET['question_id'], 'hint_number' => $_GET['hint_number']))->first();

		if(!is_object($this->obj_hint)) {
			return false;
		}

		$this->obj_facade->getCtrl()->saveParameter($this,'question_id');
		$this->obj_facade->getCtrl()->saveParameter($this,'hint_number');

		$obj_hint_levels = $this->obj_hint->getHintLevels();



		$form = new ilPropertyFormGUI();

		$item = new ilNonEditableValueGUI($this->obj_facade->getLanguageValue('task_label'));
		$item->setValue($this->obj_hint->getLabel());
		$form->addItem($item);

		foreach($obj_hint_levels as $hint_level) {
			$header = new ilFormSectionHeaderGUI();
			$header->setTitle($this->obj_facade->getLanguageValue('level_'.$hint_level->getHintLevel().'_hint'));
			$form->addItem($header);

			$item = new ilNonEditableValueGUI($this->obj_facade->getLanguageValue('minus_points'));
			$item->setValue($hint_level->getMinusPoints());
			$form->addItem($item);

			if($_GET['hint_level'] == $hint_level->getHintLevel()) {

				$item = new ilNonEditableValueGUI($this->obj_facade->getLanguageValue('hint'));
				$item->setValue($hint_level->getHintText());
				$form->addItem($item);

				$used_level = xaseUsedHintLevel::where(array('hint_level_id' => $hint_level->getId(), 'user_id' => $this->obj_facade->getUser()->getId()))->first();
				if(!is_object($used_level)) {
					$used_level = new xaseUsedHintLevel();
					$used_level->setHintLevelId($hint_level->getId());
					$used_level->setUserId($this->obj_facade->getUser()->getId());
					$used_level->setQuestionId($this->obj_question->getId());
					$used_level->store();
				}


			} else {
				$this->obj_facade->getCtrl()->setParameter($this,'hint_level',$hint_level->getHintLevel());
				$link = $this->obj_facade->getCtrl()->getLinkTarget($this, self::CMD_INDEX);
				$custom_input_gui = new ilCustomInputGUI();

				$custom_input_gui->setHtml('<a href="'.$link.'">'.sprintf($this->obj_facade->getLanguageValue('show_level_'.$hint_level->getHintLevel()), $hint_level->getMinusPoints()).'</a>');
				$form->addItem($custom_input_gui);
			}
		}



		$this->obj_facade->getTpl()->setContent($form->getHTML());
		$this->obj_facade->getTpl()->show();
	}


}