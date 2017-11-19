<?php

/**
 * Class    ilObjAssistedExerciseGUI
 *
 * @author  Benjamin Seglias <bs@studer-raimann.ch>
 */

require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/AssistedExercise/classes/class.ilObjAssistedExerciseAccess.php');
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/AssistedExercise/classes/Question/class.xaseQuestion.php');
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/AssistedExercise/classes/Setting/class.xaseSetting.php');
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/AssistedExercise/classes/Answer/class.xaseAnswerGUI.php');
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/AssistedExercise/classes/Question/class.xaseQuestionGUI.php');
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/AssistedExercise/classes/Question/class.xaseQuestionDeleteGUI.php');
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/AssistedExercise/classes/Submission/class.xaseSubmissionGUI.php');
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/AssistedExercise/classes/Setting/class.xaseSettingFormGUI.php');
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/AssistedExercise/classes/Setting/class.xaseSettingFormGUI.php');
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/AssistedExercise/classes/SampleSolution/class.xaseSampleSolutionFormGUI.php');
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/AssistedExercise/classes/class.xaseUpvotingsGUI.php');
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/AssistedExercise/classes/Answer/class.xaseAnswerListGUI.php');
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/AssistedExercise/classes/Voting/class.xaseVotingGUI.php');
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/AssistedExercise/classes/Setting/class.xaseSettingFactory.php');
require_once('./Services/Repository/classes/class.ilObjectPluginGUI.php');
require_once('./Services/Form/classes/class.ilPropertyFormGUI.php');

require_once "./Customizing/global/plugins/Services/Repository/RepositoryObject/AssistedExercise/classes/class.ilObjAssistedExerciseFacade.php";

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
 * @ilCtrl_Calls      ilObjAssistedExerciseGUI: xaseQuestionGUI
 * @ilCtrl_Calls      ilObjAssistedExerciseGUI: xaseQuestionDeleteGUI
 * @ilCtrl_Calls      ilObjAssistedExerciseGUI: xaseSettingFormGUI
 * @ilCtrl_Calls      ilObjAssistedExerciseGUI: xaseAnswerGUI
 * @ilCtrl_Calls      ilObjAssistedExerciseGUI: xaseAssessmentGUI
 * @ilCtrl_Calls      ilObjAssistedExerciseGUI: xaseSubmissionGUI
 * @ilCtrl_Calls      ilObjAssistedExerciseGUI: xaseSubmissionTableGUI
 * @ilCtrl_Calls      ilObjAssistedExerciseGUI: xaseSampleSolutionGUI
 * @ilCtrl_Calls      ilObjAssistedExerciseGUI: xaseAnswerListGUI
 * @ilCtrl_Calls      ilObjAssistedExerciseGUI: xaseUpvotingsGUI
 * @ilCtrl_Calls      ilObjAssistedExerciseGUI: xaseVotingGUI
 */
class ilObjAssistedExerciseGUI extends ilObjectPluginGUI {

	const XASE = 'xase';
	const CMD_STANDARD = 'index';
	const CMD_EDIT = 'edit';
	const CMD_UPDATE = 'update';
	/**
	 * @var ilObjAssistedExerciseFacade
	 */
	protected $obj_facade;



	protected function afterConstructor() {
		$this->obj_facade = ilObjAssistedExerciseFacade::getInstance($_GET['ref_id']);

		if (!$this->obj_facade->getAccess()->hasReadAccess()) {
			ilUtil::sendFailure(ilAssistedExercisePlugin::getInstance()->txt('permission_denied'), true);
			ilUtil::redirect('/');
		}
	}


	final function getType() {
		return self::XASE;
	}


	public function executeCommand() {
		global $ilLog;

		//TODO
		$this->setTitleAndDescription();
		if (!$this->getCreationMode()) {
			$this->obj_facade->getTpl()->setTitleIcon(ilObject::_getIcon($this->object->getId()));
		} else {
			$this->obj_facade->getTpl()->setTitleIcon(ilObject::_getIcon(ilObject::_lookupObjId($_GET['ref_id']), 'big'), $this->obj_facade->getLanguageValue('obj_'
				. ilObject::_lookupType($_GET['ref_id'], true)));
		}

		$next_class = $this->obj_facade->getCtrl()->getNextClass($this);
		$cmd = $this->obj_facade->getCtrl()->getCmd(self::CMD_STANDARD);


		switch ($next_class) {
			case 'xasequestiongui':
				$this->setTabs();
				$this->setLocator();
				$this->obj_facade->getTabsGUI()->activateTab('content');
				$this->obj_facade->forwardCommandByClass('xaseQuestionGUI');
				break;

			case 'xaseitemdeletegui':
				$this->setTabs();
				$this->setLocator();
				$this->obj_facade->getTabsGUI()->activateTab('content');
				$this->obj_facade->forwardCommandByClass('xaseQuestionDeleteGUI');
				break;

			case 'xaseanswergui':
				$this->setTabs();
				$this->setLocator();
				$this->obj_facade->getTabsGUI()->activateTab('content');
				$this->obj_facade->forwardCommandByClass('xaseAnswerGUI');
				break;

			case 'xaseassessmentgui':
				$this->setTabs();
				$this->setLocator();
				$this->obj_facade->getTabsGUI()->activateTab(xaseSubmissionGUI::CMD_STANDARD);
				$this->obj_facade->forwardCommandByClass('xaseAssessmentGUI');
				break;

			/*case 'xaseupvotingsgui':
				$this->setTabs();
				$this->setLocator();
				$this->obj_facade->getTabsGUI()->activateTab(xaseSubmissionGUI::CMD_STANDARD);
				$this->obj_facade->forwardCommandByClass('xaseUpvotingsGUI');
				break;*/

			case 'xasesubmissiongui':
				$this->setTabs();
				$this->setLocator();
				$this->obj_facade->getTabsGUI()->activateTab(xaseSubmissionGUI::CMD_STANDARD);
				$this->obj_facade->forwardCommandByClass('xaseSubmissionGUI');
				break;

			case 'xasesamplesolutiongui':
				$this->setTabs();
				$this->setLocator();
				$this->obj_facade->getTabsGUI()->activateTab('content');
				$this->obj_facade->forwardCommandByClass('xaseSampleSolutionGUI');
				break;

			case 'xaseanswerlistgui':
				$this->setTabs();
				$this->setLocator();
				$this->obj_facade->getTabsGUI()->activateTab('content');
				$this->obj_facade->forwardCommandByClass('xaseAnswerListGUI');
				break;

			case 'xasevotinggui':
				$this->obj_facade->getTpl()->getStandardTemplate();
				$this->obj_facade->forwardCommandByClass('xaseVotingGUI');
				$this->obj_facade->getTpl()->show();
				break;

			default:
				parent::executeCommand();
				break;
		}
	}


	protected function performCommand() {
		$cmd = $this->obj_facade->getCtrl()->getCmd(self::CMD_STANDARD);

		switch ($cmd) {
			case self::CMD_STANDARD:
					$this->obj_facade->getCtrl()->redirect(new xaseQuestionGUI(), xaseQuestionGUI::CMD_INDEX);
					break;
			case self::CMD_EDIT:
			case self::CMD_UPDATE:
				if ($this->obj_facade->getAccess()->hasWriteAccess()) {
					$this->obj_facade->getTabsGUI()->activateTab(self::CMD_EDIT);
					$this->{$cmd}();
					break;
				} else {
					ilUtil::sendFailure(ilAssistedExercisePlugin::getInstance()->txt('permission_denied'), true);
					break;
				}
		}
	}

	/**
	 * After saving
	 * @access	public
	 */
	function afterSave(ilObject $newObj)
	{

		if(ilObject::_lookupType($newObj->getRefId(),true) == 'xase') {
			$obj_facade = ilObjAssistedExerciseFacade::getInstance($newObj->getRefId());
			$obj_facade->store();
		}


		parent::afterSave($newObj);
	}


	/**
	 * Cmd that will be redirected to after creation of a new object.
	 */
	function getAfterCreationCmd() {
		//$this->store();
	}

	function store() {
		//$this->obj_facade->store();
	}

	function getStandardCmd() {
		return self::CMD_STANDARD;
	}


	protected function setTabs() {
		//ToDO
		if (strtolower($_GET['baseClass']) != 'iladministrationgui') {
			$this->obj_facade->addTab('content', 'tasks','xaseQuestionGUI', xaseQuestionGUI::CMD_INDEX);
			$this->addInfoTab();
			if ($this->obj_facade->getAccess()->hasWriteAccess()) {

				//TODO
				if($this->obj_facade->getSetting()->getModus() == xaseSetting::MODUS1) {
					if($this->obj_facade->getSetting()->getRateAnswers()) {
						$this->obj_facade->getTabsGUI()->addTab(xaseSubmissionGUI::CMD_STANDARD, $this->obj_facade->getLanguageValue('submissions'), $this->obj_facade->getCtrl()->getLinkTarget(new xaseSubmissionGUI(), xaseSubmissionGUI::CMD_STANDARD));
					}
				}

				$this->obj_facade->addTab(self::CMD_EDIT, 'edit_properties','ilObjAssistedExerciseGUI', self::CMD_EDIT);
			}

			if ($this->obj_facade->getAccess()->hasWriteAccess()) {
				$this->obj_facade->getTabsGUI()
					->addTab('perm_settings', $this->obj_facade->getLanguageValue('perm_settings'), $this->obj_facade->getCtrl()
						->getLinkTargetByClass(array(
							'ilObjAssistedExerciseGUI',
							'ilpermissiongui'
						), 'perm'));
			}

		} else {
			$this->addAdminLocatorItems();
			$this->obj_facade->getTpl()->setLocator();
			$this->setAdminTabs();
		}
	}


	public function edit() {
		$form = new xaseSettingFormGUI($this);
		$form->fillForm();
		$this->obj_facade->getTpl()->setContent($form->getHTML());
	}


	public function update() {

		$form = new xaseSettingFormGUI($this);
		$form->setValuesByPost();
		if($form->checkInput()) {
			$form->update();
			ilUtil::sendSuccess($this->obj_facade->getLanguageValue('changes_saved_success'), true);
			$this->obj_facade->getCtrl()->redirect($this,self::CMD_EDIT);
		}

		$this->obj_facade->getTpl()->setContent($form->getHTML());
	}


	/**
	 * show information screen
	 */
	function infoScreen() {
		global $DIC;

		$this->obj_facade->getTabsGUI()->activateTab("info_short");

		$this->checkPermission("visible");

		include_once("./Services/InfoScreen/classes/class.ilInfoScreenGUI.php");
		$info = new ilInfoScreenGUI($this);
		$info->enablePrivateNotes();

		$this->lng->loadLanguageModule("meta");

		$this->addInfoItems($info);

		require_once('Services/Tracking/classes/class.ilChangeEvent.php');
		ilChangeEvent::_recordReadEvent("webr", $_GET['ref_id'], $this->obj_id, $DIC->user()->getId());

		$this->obj_facade->getCtrl()->forwardCommand($info);
	}


	public static function _goto($a_target) {
		// TODO: Prüfen, ob eine Variante implementier werden muss, die direkt auf eine Bewertung führt.
		return parent::_goto($a_target);
	}
}