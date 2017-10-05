<?php

/**
 * Class    ilObjAssistedExerciseGUI
 * @author  Benjamin Seglias <bs@studer-raimann.ch>
 */

require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/AssistedExercise/classes/class.ilObjAssistedExerciseAccess.php');
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/AssistedExercise/classes/ActiveRecords/class.xaseItem.php');
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/AssistedExercise/classes/ActiveRecords/class.xaseSettings.php');
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/AssistedExercise/classes/class.xaseItemGUI.php');
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/AssistedExercise/classes/class.xaseSettingsFormGUI.php');
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
 * @ilCtrl_Calls      ilObjAssistedExerciseGUI: xaseSettingsFormGUI
 * @ilCtrl_Calls      ilObjAssistedExerciseGUI: xaseAnswerGUI
 * @ilCtrl_Calls      ilObjAssistedExerciseGUI: ilObjAssistedExerciseListGUI
 */
class ilObjAssistedExerciseGUI extends ilObjectPluginGUI
{
    const XASE = 'xase';
    const CMD_STANDARD = 'index';
    const CMD_EDIT = 'edit';
    const CMD_UPDATE = 'update';

    /**
     * @var ilObjAssistedExercise
     */
    public $object;
    /**
     * @var xaseSettings
     */
    protected $xase_settings;

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

    protected function afterConstructor()
    {
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
    }

    final function getType()
    {
        return self::XASE;
    }

    public function executeCommand()
    {

        $this->setTitleAndDescription();

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

            default:
                return parent::executeCommand();
        }
    }

    protected function performCommand()
    {
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

    function getAfterCreationCmd()
    {
        return self::CMD_STANDARD;
    }

    function getStandardCmd()
    {
        return self::CMD_STANDARD;
    }

    protected function setTabs()
    {
        if (strtolower($_GET['baseClass']) != 'iladministrationgui') {
            $this->tabs->addTab('content', $this->pl->txt('content'), $this->ctrl->getLinkTarget(new xaseItemGUI(), xaseItemGUI::CMD_STANDARD));
            $this->addInfoTab();
            if ($this->access->hasWriteAccess()) {
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

    public function edit()
    {
        $this->tabs->activateTab(self::CMD_EDIT);
        $xaseSettingsFormGUI = new xaseSettingsFormGUI($this, $this->object, $this->xase_settings, $this->xase_settings->getModus());
        $xaseSettingsFormGUI->fillForm();
        $this->tpl->setContent($xaseSettingsFormGUI->getHTML());
    }

    public function update()
    {
        $this->tabs->activateTab(self::CMD_EDIT);
        $xaseSettingsFormGUI = new xaseSettingsFormGUI($this, $this->object, $this->xase_settings, $this->xase_settings->getModus());
        if ($xaseSettingsFormGUI->updateObject() && $this->object->update()) {
            ilUtil::sendSuccess($this->pl->txt('changes_saved_success'), true);
        }
        $xaseSettingsFormGUI->setValuesByPost();
        $this->tpl->setContent($xaseSettingsFormGUI->getHTML());
    }

    protected function getSettings()
    {
        if (xaseSettings::where(['assisted_exercise_object_id' => intval($this->object->getId())])->hasSets()) {
            return xaseSettings::where(['assisted_exercise_object_id' => intval($this->object->getId())])->first();
        }
        $xaseSettings = new xaseSettings();
        $xaseSettings->setAssistedExerciseObjectId($this->object->getId());
        $xaseSettings->create();
        return $xaseSettings;
    }

    /**
     * show information screen
     */
    function infoScreen()
    {
        global $DIC;

        $this->tabs->activateTab("info_short");

        $this->checkPermission("visible");

        include_once("./Services/InfoScreen/classes/class.ilInfoScreenGUI.php");
        $info = new ilInfoScreenGUI($this);
        $info->enablePrivateNotes();

        $this->lng->loadLanguageModule("meta");

        $this->addInfoItems($info);

        require_once('Services/Tracking/classes/class.ilChangeEvent.php');
        ilChangeEvent::_recordReadEvent("webr", $_GET['ref_id'], $this->obj_id,
            $DIC->user()->getId());

        $this->ctrl->forwardCommand($info);
    }
}