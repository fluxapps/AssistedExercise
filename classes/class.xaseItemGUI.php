<?php

/**
 * Class xaseItemGUI
 * @author  Benjamin Seglias <bs@studer-raimann.ch>
 * @ilCtrl_Calls      xaseItemGUI: xaseItemTableGUI
 * @ilCtrl_Calls      xaseItemGUI: xaseItemFormGUI
 * @ilCtrl_isCalledBy xaseItemGUI: ilObjAssistedExerciseGUI
 */

require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/AssistedExercise/classes/class.xaseItemFormGUI.php');
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/AssistedExercise/classes/class.xaseItemTableGUI.php');

class xaseItemGUI
{

    const CMD_STANDARD = 'content';
    const CMD_EDIT = 'edit';
    const CMD_UPDATE = 'update';

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

    public function __construct()
    {
        global $DIC;
        $this->dic = $DIC;
        $this->tpl = $this->dic['tpl'];
        $this->tabs = $DIC->tabs();
        $this->ctrl = $this->dic->ctrl();
        $this->access = new ilObjAssistedExerciseAccess();
        $this->pl = ilAssistedExercisePlugin::getInstance();
        $this->object = ilObjectFactory::getInstanceByRefId($_GET['ref_id']);
        $this->xase_settings = xaseSettings::where(['assisted_exercise_object_id' => $this->object->getId()])->first();
        $this->xase_item = new xaseItem();
    }

    public function executeCommand()
    {
        $nextClass = $this->ctrl->getNextClass();
        switch ($nextClass) {
            default:
                $cmd = $this->ctrl->getCmd(self::CMD_STANDARD);
                $this->tabs->activateTab(self::CMD_STANDARD);
                $this->{$cmd}();
        }
    }

    protected function performCommand()
    {
        $cmd = $this->ctrl->getCmd(self::CMD_STANDARD);
        switch ($cmd) {
            case self::CMD_STANDARD:
                if ($this->access->hasReadAccess()) {
                    $this->{$cmd}();
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

    public function edit()
    {
        $this->tabs->activateTab(self::CMD_STANDARD);
        $xaseItemFormGUI = new xaseItemFormGUI($this, $this->xase_item, $this->xase_settings->getModus());
        $xaseItemFormGUI->fillForm();
        $this->tpl->setContent($xaseItemFormGUI->getHTML());
        $this->tpl->show();
    }

    public function update()
    {
        $this->tabs->activateTab(self::CMD_STANDARD);
        $xaseItemFormGUI = new xaseItemFormGUI($this, $this->xase_item, $this->xase_settings->getModus());
        if ($xaseItemFormGUI->updateObject()) {
            ilUtil::sendSuccess($this->pl->txt('changes_saved_success'), true);
        }
        $xaseItemFormGUI->setValuesByPost();
        $this->tpl->setContent($xaseItemFormGUI->getHTML());
    }

    public function content()
    {
        if (!$this->access->hasReadAccess()) {
            ilUtil::sendFailure($this->pl->txt('permission_denied'), true);
        }

        $xaseItemTableGUI = new xaseItemTableGUI($this, self::CMD_STANDARD);
        $this->tpl->setContent($xaseItemTableGUI->getHTML());
        $this->tpl->show();
    }

    protected function applyFilter()
    {
        $xgeoLocationTableGUI = new xaseItemTableGUI($this, self::CMD_STANDARD);
        $xgeoLocationTableGUI->writeFilterToSession();
        $this->ctrl->redirect($this, self::CMD_STANDARD);
    }


    protected function resetFilter()
    {
        $xgeoLocationTableGUI = new xaseItemTableGUI($this, self::CMD_STANDARD);
        $xgeoLocationTableGUI->resetFilter();
        $xgeoLocationTableGUI->resetOffset();
        $this->ctrl->redirect($this, self::CMD_STANDARD);
    }

}