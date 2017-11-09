<?php

/**
 * Class    ilObjAssistedExerciseGUI
 *
 * @author  Benjamin Seglias <bs@studer-raimann.ch>
 */

require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/AssistedExercise/classes/class.ilObjAssistedExerciseAccess.php');
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/AssistedExercise/classes/ActiveRecords/class.xaseItem.php');
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/AssistedExercise/classes/ActiveRecords/class.xaseSettings.php');
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/AssistedExercise/classes/class.xaseAnswerGUI.php');
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/AssistedExercise/classes/class.xaseItemGUI.php');
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/AssistedExercise/classes/class.xaseItemDeleteGUI.php');
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/AssistedExercise/classes/class.xaseSubmissionGUI.php');
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/AssistedExercise/classes/class.xaseSettingsFormGUI.php');
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/AssistedExercise/classes/class.xaseSettingsFormGUI.php');
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/AssistedExercise/classes/class.xaseSampleSolutionFormGUI.php');
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/AssistedExercise/classes/class.xaseUpvotingsGUI.php');
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/AssistedExercise/classes/class.xaseAnswerListGUI.php');
require_once('./Services/Repository/classes/class.ilObjectPluginGUI.php');
require_once('./Services/Form/classes/class.ilPropertyFormGUI.php');

/**
 * User Interface class for assisted exercise repository object.
 *
 * @author            Benjamin Seglias <bs@studer-raimann.ch>
 *
 * @version           1.0.00
 *
 * Integration into control structure:
 * - The GUI class is called by ilRepositoryGUI
 * - GUI classes used by this class are ilPermissionGUI (provides the rbac
 *   screens) and ilInfoScreenGUI (handles the info screen).
 *
 * The most complicated thing is the control-flow.
 *
 *
 * @ilCtrl_isCalledBy ilObjAssistedExerciseGUI: ilRepositoryGUI
 * @ilCtrl_isCalledBy ilObjAssistedExerciseGUI: ilObjPluginDispatchGUI
 * @ilCtrl_isCalledBy ilObjAssistedExerciseGUI: ilAdministrationGUI
 * @ilCtrl_Calls      ilObjAssistedExerciseGUI: ilPermissionGUI,ilInfoScreenGUI
 * @ilCtrl_Calls      ilObjAssistedExerciseGUI: ilObjectCopyGUI
 * @ilCtrl_Calls      ilObjAssistedExerciseGUI: ilCommonActionDispatcherGUI
 * @ilCtrl_Calls      ilObjAssistedExerciseGUI: xaseItemGUI
 * @ilCtrl_Calls      ilObjAssistedExerciseGUI: xaseItemDeleteGUI
 * @ilCtrl_Calls      ilObjAssistedExerciseGUI: xaseSettingsFormGUI
 * @ilCtrl_Calls      ilObjAssistedExerciseGUI: xaseAnswerGUI
 * @ilCtrl_Calls      ilObjAssistedExerciseGUI: xaseAssessmentGUI
 * @ilCtrl_Calls      ilObjAssistedExerciseGUI: xaseSubmissionGUI
 * @ilCtrl_Calls      ilObjAssistedExerciseGUI: xaseSubmissionTableGUI
 * @ilCtrl_Calls      ilObjAssistedExerciseGUI: xaseSampleSolutionGUI
 * @ilCtrl_Calls      ilObjAssistedExerciseGUI: xaseAnswerListGUI
 * @ilCtrl_Calls      ilObjAssistedExerciseGUI: xaseUpvotingsGUI
 */
class ilObjAssistedExerciseGUI extends ilObjectPluginGUI {

	const XASE = 'xase';
	const CMD_STANDARD = 'index';
	const CMD_EDIT = 'edit';
	const CMD_UPDATE = 'update';
	const M1 = 1;
	const M2 = 2;
	const M3 = 3;
	/**
	 * @var ilObjAssistedExercise
	 */
	public $object;
	/**
	 * @var xaseSettings
	 */
	protected $xase_settings;
	/**
	 * @var xaseSettingsM1|xaseSettingsM2|xaseSettingsM3
	 */
	protected $mode_settings;
	/**
	 * @var ilCtrl
	 */
	protected $ctrl;
	/**
	 * @var ilObjAssistedExerciseAccess
	 */
	protected $access;
	/**
	 * @var ilAssistedExercisePlugin
	 */
	protected $pl;
	/**
	 * @var \ILIAS\DI\Container
	 */
	protected $dic;
	/**
	 * @var ilPropertyFormGUI
	 */
	protected $form;
	/**
	 * @var ilTabsGUI
	 */
	protected $tabs;


	protected function afterConstructor() {
		global $DIC;

		$this->dic = $DIC;
		$this->access = new ilObjAssistedExerciseAccess();
		$this->pl = ilAssistedExercisePlugin::getInstance();
		$this->tabs = $DIC->tabs();
		$this->ctrl = $DIC->ctrl();
		$this->tpl = $DIC['tpl'];
		if (!$this->getCreationMode()) {
			$this->xase_settings = $this->getSettings();
		}
		$this->mode_settings = $this->getModeSettings($this->xase_settings->getModus());
	}


	final function getType() {
		return self::XASE;
	}


	public function executeCommand() {

		$this->setTitleAndDescription();
		if (!$this->getCreationMode()) {
			$this->tpl->setTitleIcon(ilObject::_getIcon($this->object->getId()));
		} else {
			$this->tpl->setTitleIcon(ilObject::_getIcon(ilObject::_lookupObjId($_GET['ref_id']), 'big'), $this->pl->txt('obj_'
				. ilObject::_lookupType($_GET['ref_id'], true)));
		}

		$next_class = $this->dic->ctrl()->getNextClass($this);

		$this->dic->ctrl()->getCmd(self::CMD_STANDARD);

		switch ($next_class) {
			case 'xaseitemgui':
				$this->setTabs();
				$this->setLocator();
				$this->tabs->activateTab(xaseItemGUI::CMD_STANDARD);
				//has to be called because in this case parent::executeCommand is not executed(contains getStandardTempplate and Show)
				//Show Method has to be called in the corresponding methods
				$this->tpl->getStandardTemplate();
				$xaseItemGUI = new xaseItemGUI();
				$this->ctrl->forwardCommand($xaseItemGUI);
				break;

			case 'xaseitemdeletegui':
				$this->setTabs();
				$this->setLocator();
				$this->tabs->activateTab(xaseItemDeleteGUI::CMD_STANDARD);
				$this->tpl->getStandardTemplate();
				$xaseItemDeleteGUI = new xaseItemDeleteGUI();
				$this->ctrl->forwardCommand($xaseItemDeleteGUI);
				break;

			case 'xaseanswergui':
				$this->setTabs();
				$this->setLocator();
				$this->tabs->activateTab(xaseItemGUI::CMD_STANDARD);
				$this->tpl->getStandardTemplate();
				$xaseAnswerGUI = new xaseAnswerGUI($this->object);
				$this->ctrl->forwardCommand($xaseAnswerGUI);
				break;

			case 'xaseassessmentgui':
				$this->setTabs();
				$this->setLocator();
				$this->tabs->activateTab(xaseSubmissionGUI::CMD_STANDARD);
				$this->tpl->getStandardTemplate();
				$xaseAssessmentGUI = new xaseAssessmentGUI($this->object);
				$this->ctrl->forwardCommand($xaseAssessmentGUI);
				break;

			case 'xaseupvotingsgui':
				$this->setTabs();
				$this->setLocator();
				$this->tabs->activateTab(xaseSubmissionGUI::CMD_STANDARD);
				$this->tpl->getStandardTemplate();
				$xaseUpvotingsGUI = new xaseUpvotingsGUI();
				$this->ctrl->forwardCommand($xaseUpvotingsGUI);
				break;

			case 'xasesubmissiongui':
				$this->setTabs();
				$this->setLocator();
				$this->tabs->activateTab(xaseSubmissionGUI::CMD_STANDARD);
				$this->tpl->getStandardTemplate();
				$xaseSubmissionGUI = new xaseSubmissionGUI();
				$this->ctrl->forwardCommand($xaseSubmissionGUI);
				break;

			case 'xasesamplesolutiongui':
				$this->setTabs();
				$this->setLocator();
				$this->tabs->activateTab(xaseItemGUI::CMD_STANDARD);
				$this->tpl->getStandardTemplate();
				$xaseSampleSolution = new xaseSampleSolutionGUI();
				$this->ctrl->forwardCommand($xaseSampleSolution);
				break;

			case 'xaseanswerlistgui':
				$this->setTabs();
				$this->setLocator();
				$this->tabs->activateTab(xaseItemGUI::CMD_STANDARD);
				$this->tpl->getStandardTemplate();
				$xaseAnswerListGUI = new xaseAnswerListGUI($this->object);
				$this->ctrl->forwardCommand($xaseAnswerListGUI);
				break;

			default:
				return parent::executeCommand();
		}
	}


	protected function performCommand() {
		$cmd = $this->ctrl->getCmd(self::CMD_STANDARD);
		switch ($cmd) {
			case self::CMD_STANDARD:
				if ($this->access->hasReadAccess()) {
					$this->ctrl->redirect(new xaseItemGUI(), xaseItemGUI::CMD_STANDARD);
					break;
				} else {
					ilUtil::sendFailure(ilAssistedExercisePlugin::getInstance()->txt('permission_denied'), true);
					break;
				}
			case self::CMD_EDIT:
			case self::CMD_UPDATE:
				if ($this->access->hasWriteAccess()) {
					$this->{$cmd}();
					break;
				} else {
					ilUtil::sendFailure(ilAssistedExercisePlugin::getInstance()->txt('permission_denied'), true);
					break;
				}
		}
	}


	function getAfterCreationCmd() {
		return self::CMD_EDIT;
	}


	function getStandardCmd() {
		return self::CMD_STANDARD;
	}


	protected function setTabs() {
		if (strtolower($_GET['baseClass']) != 'iladministrationgui') {
			$this->tabs->addTab('content', $this->pl->txt('tasks'), $this->ctrl->getLinkTarget(new xaseItemGUI(), xaseItemGUI::CMD_STANDARD));
			$this->addInfoTab();
			if ($this->access->hasWriteAccess()) {
				if($this->xase_settings->getModus() == self::M1 || $this->xase_settings->getModus() == self::M3) {
					if($this->mode_settings->getRateAnswers()) {
						$this->tabs->addTab(xaseSubmissionGUI::CMD_STANDARD, $this->pl->txt('submissions'), $this->ctrl->getLinkTarget(new xaseSubmissionGUI(), xaseSubmissionGUI::CMD_STANDARD));
					}
				}
				$this->tabs->addTab(self::CMD_EDIT, $this->pl->txt('edit_properties'), $this->ctrl->getLinkTarget($this, self::CMD_EDIT));
			}
			if ($this->checkPermissionBool('edit_permission')) {
				$this->tabs->addTab('perm_settings', $this->pl->txt('perm_settings'), $this->ctrl->getLinkTargetByClass(array(
					'ilObjAssistedExerciseGUI',
					'ilpermissiongui',
				), 'perm'));
			}
		} else {
			$this->addAdminLocatorItems();
			$this->tpl->setLocator();
			$this->setAdminTabs();
		}
	}


	public function edit() {
		$this->tabs->activateTab(self::CMD_EDIT);
		$xaseSettingsFormGUI = new xaseSettingsFormGUI($this, $this->object, $this->xase_settings, $this->xase_settings->getModus());
		$xaseSettingsFormGUI->fillForm();
		$this->tpl->setContent($xaseSettingsFormGUI->getHTML());
	}


	public function update() {
		$this->tabs->activateTab(self::CMD_EDIT);
		$xaseSettingsFormGUI = new xaseSettingsFormGUI($this, $this->object, $this->xase_settings, $this->xase_settings->getModus());
		if ($xaseSettingsFormGUI->updateObject() && $this->object->update()) {
			ilUtil::sendSuccess($this->pl->txt('changes_saved_success'), true);
		}
		$xaseSettingsFormGUI->setValuesByPost();
		$this->tpl->setContent($xaseSettingsFormGUI->getHTML());
	}


	protected function getSettings() {
		if (xaseSettings::where([ 'assisted_exercise_object_id' => intval($this->object->getId()) ])->hasSets()) {
			return xaseSettings::where([ 'assisted_exercise_object_id' => intval($this->object->getId()) ])->first();
		}
		$xaseSettings = new xaseSettings();
		$xaseSettings->setAssistedExerciseObjectId($this->object->getId());
		$xaseSettings->create();

		return $xaseSettings;
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


	/**
	 * show information screen
	 */
	function infoScreen() {
		global $DIC;

		$this->tabs->activateTab("info_short");

		$this->checkPermission("visible");

		include_once("./Services/InfoScreen/classes/class.ilInfoScreenGUI.php");
		$info = new ilInfoScreenGUI($this);
		$info->enablePrivateNotes();

		$this->lng->loadLanguageModule("meta");

		$this->addInfoItems($info);

		require_once('Services/Tracking/classes/class.ilChangeEvent.php');
		ilChangeEvent::_recordReadEvent("webr", $_GET['ref_id'], $this->obj_id, $DIC->user()->getId());

		$this->ctrl->forwardCommand($info);
	}


	public static function _goto($a_target) {
		// TODO: Prüfen, ob eine Variante implementier werden muss, die direkt auf eine Bewertung führt.

		return parent::_goto($a_target);

		// $a_target = {ref_id}_{cmd}_{id}
		/*global $ilCtrl, $ilAccess, $lng;

		$t = explode("_", $a_target[0]);
		$ref_id = (int) $t[0];
		$class_name = $a_target[1];
		$command = $t[1];

		if ($ilAccess->checkAccess("read", "", $ref_id))
		{
			$ilCtrl->initBaseClass("ilObjPluginDispatchGUI");
			$ilCtrl->setTargetScript("ilias.php");
			$ilCtrl->getCallStructure(strtolower("ilObjPluginDispatchGUI"));
			$ilCtrl->setParameterByClass($class_name, "ref_id", $ref_id);
			$ilCtrl->redirectByClass(array("ilobjplugindispatchgui", $class_name), $command);
		}
		else if($ilAccess->checkAccess("visible", "", $ref_id))
		{
			$ilCtrl->initBaseClass("ilObjPluginDispatchGUI");
			$ilCtrl->setTargetScript("ilias.php");
			$ilCtrl->getCallStructure(strtolower("ilObjPluginDispatchGUI"));
			$ilCtrl->setParameterByClass($class_name, "ref_id", $ref_id);
			$ilCtrl->redirectByClass(array("ilobjplugindispatchgui", $class_name), "infoScreen");
		}
		else if ($ilAccess->checkAccess("read", "", ROOT_FOLDER_ID))
		{
			ilUtil::sendFailure(sprintf($lng->txt("msg_no_perm_read_item"),
				ilObject::_lookupTitle(ilObject::_lookupObjId($ref_id))));
			include_once("./Services/Object/classes/class.ilObjectGUI.php");
			ilObjectGUI::_gotoRepositoryRoot();
		}*/
	}
}