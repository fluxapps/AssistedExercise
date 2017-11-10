<?php
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/AssistedExercise/classes/class.xaseItemFormGUI.php');
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/AssistedExercise/classes/class.xaseItemTableGUI.php');
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/AssistedExercise/classes/services/xaseItemAccess.php');

/**
 * Class xaseItemGUI
 *
 * @author            Benjamin Seglias <bs@studer-raimann.ch>
 */
class xaseItemGUI {

	const ITEM_IDENTIFIER = 'item_id';
	const CMD_STANDARD = 'content';
	const CMD_CANCEL = 'cancel';
	const CMD_EDIT = 'edit';
	const CMD_UPDATE = 'update';
	const CMD_APPLY_FILTER = 'applyFilter';
	const CMD_RESET_FILTER = 'resetFilter';
	const CMD_SET_ANSWER_STATUS_TO_CAN_BE_VOTED = 'setAnswerStatusToCanBeVoted';
	const M1 = "1";
	const M2 = "2";
	const M3 = "3";
	/**
	 * @var ilObjAssistedExercise
	 */
	public $object;
	/**
	 * @var xaseItem
	 */
	public $xase_item;
	/**
	 * @var xaseSettings
	 */
	public $xase_settings;
	/**
	 * @var xaseSettingsM1|xaseSettingsM2|xaseSettingsM3
	 */
	protected $mode_settings;
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
		$this->mode_settings = $this->getModeSettings($this->xase_settings->getModus());
		$this->xase_item = new xaseItem($_GET[self::ITEM_IDENTIFIER]);
	}


	public function executeCommand() {

		$nextClass = $this->ctrl->getNextClass();
		switch ($nextClass) {
			default:
				$this->tabs->activateTab(self::CMD_STANDARD);
				$this->performCommand();
		}
	}


	protected function performCommand() {
		$cmd = $this->ctrl->getCmd(self::CMD_STANDARD);
		switch ($cmd) {
			case self::CMD_STANDARD:
			case self::CMD_CANCEL:
			case self::CMD_APPLY_FILTER:
			case self::CMD_RESET_FILTER:
			case self::CMD_SET_ANSWER_STATUS_TO_CAN_BE_VOTED:
				if (xaseItemAccess::hasReadAccess($this->xase_settings, $this->xase_item)) {
					$this->{$cmd}();
					break;
				} else {
					ilUtil::sendFailure(ilAssistedExercisePlugin::getInstance()->txt('permission_denied'), true);
					break;
				}
			case self::CMD_EDIT:
			case self::CMD_UPDATE:
				if (xaseItemAccess::hasWriteAccess($this->xase_settings, $this->xase_item)) {
					$this->{$cmd}();
					break;
				} else {
					ilUtil::sendFailure(ilAssistedExercisePlugin::getInstance()->txt('permission_denied'), true);
					break;
				}
		}
	}


	public function edit() {
		$this->ctrl->saveParameter($this, self::ITEM_IDENTIFIER);
		$this->tabs->activateTab(self::CMD_STANDARD);
		$xaseItemFormGUI = new xaseItemFormGUI($this, $this->xase_item, $this->xase_settings);
		$xaseItemFormGUI->fillForm();
		$this->tpl->setContent($xaseItemFormGUI->getHTML());
		$this->tpl->show();
	}


	public function update() {
		$this->ctrl->saveParameter($this, self::ITEM_IDENTIFIER);
		$this->tabs->activateTab(self::CMD_STANDARD);
		$xaseItemFormGUI = new xaseItemFormGUI($this, $this->xase_item, $this->xase_settings);
		if ($xaseItemFormGUI->updateObject()) {
			ilUtil::sendSuccess($this->pl->txt('changes_saved_success'), true);
			$this->ctrl->redirect($this, self::CMD_STANDARD);
		}
		$xaseItemFormGUI->setValuesByPost();
		$this->tpl->setContent($xaseItemFormGUI->getHTML());
		$this->tpl->show();
	}


	public function content() {
		$this->ctrl->saveParameterByClass(xaseItemTableGUI::class, self::ITEM_IDENTIFIER);
		if (!xaseItemAccess::hasReadAccess($this->xase_settings, $this->xase_item)) {
			ilUtil::sendFailure($this->pl->txt('permission_denied'), true);
		}
		$xaseItemTableGUI = new xaseItemTableGUI($this, self::CMD_STANDARD, $this->object);
		if ($this->xase_settings->getModus() != self::M2) {
			$list = $this->createListing();
			$this->tpl->setContent($list . $xaseItemTableGUI->getHTML());
		} else {
			$this->tpl->setContent($xaseItemTableGUI->getHTML());
		}
		$this->tpl->show();
	}


	protected function applyFilter() {
		$xaseItemTableGUI = new xaseItemTableGUI($this, self::CMD_STANDARD, $this->object);
		$xaseItemTableGUI->writeFilterToSession();
		$this->ctrl->redirect($this, self::CMD_STANDARD);
	}


	protected function resetFilter() {
		$xaseItemTableGUI = new xaseItemTableGUI($this, self::CMD_STANDARD, $this->object);
		$xaseItemTableGUI->resetFilter();
		$xaseItemTableGUI->resetOffset();
		$this->ctrl->redirect($this, self::CMD_STANDARD);
	}


	protected function cancel() {
		$this->ctrl->redirect($this, self::CMD_STANDARD);
	}

	protected function getMaxAchievedPoints() {
		$answers_from_user = xaseItemTableGUI::getAnswersFromUser($this->object, $this->dic);

		if (empty($answers_from_user)) {
			return 0;
		}

		$max_achieved_points = 0;
		foreach ($answers_from_user as $answer) {
			$xasePoint = xasePoint::where(array( 'id' => $answer->getPointId() ))->first();
			$max_achieved_points += $xasePoint->getTotalPoints();
		}

		return $max_achieved_points;
	}


	protected function getTotalUsedHints() {
		$answers_from_user = xaseItemTableGUI::getAnswersFromUser($this->object, $this->dic);
		if (empty($answers_from_user)) {
			return 0;
		}
		$total_used_hints = 0;
		foreach ($answers_from_user as $answer) {
			$total_used_hints += $answer->getNumberOfUsedHints();
		}

		return $total_used_hints;
	}


	protected function getModeSettings($mode) {
		if ($mode == self::M1) {
			return xaseSettingsM1::where([ 'settings_id' => $this->xase_settings->getId() ])->first();
		} elseif ($mode == self::M3) {
			return xaseSettingsM3::where([ 'settings_id' => $this->xase_settings->getId() ])->first();
		} else {
			return xaseSettingsM2::where([ 'settings_id' => $this->xase_settings->getId() ])->first();
		}
	}


	protected function getDisposalDate() {
		if ($this->mode_settings->getDisposalDate() == "0000-00-00 00:00:00" || empty($this->mode_settings->getDisposalDate())) {
			return $this->pl->txt('no_disposal_date');
		} else {
			return $this->mode_settings->getDisposalDate();
		}
	}


	public function createListing() {
		$f = $this->dic->ui()->factory();
		$renderer = $this->dic->ui()->renderer();

		$unordered = $f->listing()->descriptive(array(
			$this->pl->txt('max_achievable_points') => strval(xaseItemTableGUI::getMaxAchievablePoints($this->object->getId(), $this->xase_settings->getModus())),
			$this->pl->txt('max_achieved_points') => strval($this->getMaxAchievedPoints()),
			$this->pl->txt('total_used_hints') => strval($this->getTotalUsedHints()),
			$this->pl->txt('disposal_date') => $this->getDisposalDate(),
		));

		return $renderer->render($unordered);
	}
}