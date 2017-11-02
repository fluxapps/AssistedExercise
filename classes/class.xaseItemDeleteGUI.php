<?php

/**
 * Class xaseItemDeleteGUI
 * @ilCtrl_isCalledBy xaseItemDeleteGUI: ilObjAssistedExerciseGUI
 */

require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/AssistedExercise/classes/class.xaseItemDeleteFormGUI.php');

class xaseItemDeleteGUI
{

    const ITEM_IDENTIFIER = 'item_id';
    const CMD_STANDARD = 'delete';
    const CMD_CONFIRM_DELETE = 'confirmedDelete';
    const CMD_CANCEL_DELETE = 'canceledDelete';

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
        $this->xase_item = new xaseItem($_GET[self::ITEM_IDENTIFIER]);
    }

    public function executeCommand()
    {

        $nextClass = $this->ctrl->getNextClass();
        switch ($nextClass) {
            default:
                $this->tabs->activateTab(xaseItemGUI::CMD_STANDARD);
                $this->performCommand();
        }
    }

    protected function performCommand()
    {
        $cmd = $this->ctrl->getCmd(self::CMD_STANDARD);
        switch ($cmd) {
            case self::CMD_STANDARD:
            case self::CMD_CONFIRM_DELETE:
            case self::CMD_CANCEL_DELETE:
                if ($this->access->hasDeleteAccess()) {
                    $this->{$cmd}();
                    break;
                } else {
                    ilUtil::sendFailure(ilAssistedExercisePlugin::getInstance()->txt('permission_denied'), true);
                    break;
                }
        }
    }

    public function delete()
    {
        $this->ctrl->saveParameter($this, self::ITEM_IDENTIFIER);

        include_once("./Services/Utilities/classes/class.ilConfirmationGUI.php");
        $cgui = new ilConfirmationGUI();

        $cgui->setHeaderText($this->pl->txt('confirm_delete_question'));
        $cgui->setFormAction($this->ctrl->getFormAction($this));
        $cgui->setCancel($this->dic->language()->txt('cancel'), "canceledDelete");
        $cgui->setConfirm($this->dic->language()->txt('confirm'), "confirmedDelete");

        $this->tpl->setContent($cgui->getHTML());
        $this->tpl->show();
    }

    public function confirmedDelete()
    {
        //TODO remove data
        ilUtil::sendSuccess($this->pl->txt('successfully_deleted'), true);
        $this->ctrl->redirectByClass("xaseItemGUI");
    }

    public function canceledDelete()
    {
        $this->ctrl->redirectByClass("xaseItemGUI");
    }
}