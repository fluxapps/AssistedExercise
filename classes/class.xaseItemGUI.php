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

require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/AssistedExercise/classes/services/xaseItemAccess.php');

class xaseItemGUI
{

    const ITEM_IDENTIFIER = 'item_id';
    const CMD_STANDARD = 'content';
    const CMD_CANCEL = 'cancel';
    const CMD_EDIT = 'edit';
    const CMD_UPDATE = 'update';
    const CMD_APPLY_FILTER = 'applyFilter';
    const CMD_RESET_FILTER = 'resetFilter';
    const CMD_SET_ANSWER_STATUS_TO_CAN_BE_VOTED = 'setAnswerStatusToCanBeVoted';

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
        //TODO set item_id Parameter
        $this->xase_item = new xaseItem($_GET[self::ITEM_IDENTIFIER]);
    }

    public function executeCommand()
    {

        $nextClass = $this->ctrl->getNextClass();
        switch ($nextClass) {
            default:
                $this->tabs->activateTab(self::CMD_STANDARD);
                $this->performCommand();
        }
    }

    protected function performCommand()
    {
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

    public function edit()
    {
        $this->ctrl->saveParameter($this, self::ITEM_IDENTIFIER);
        $this->tabs->activateTab(self::CMD_STANDARD);
        $xaseItemFormGUI = new xaseItemFormGUI($this, $this->xase_item, $this->xase_settings);
        $xaseItemFormGUI->fillForm();
        //echo $xaseItemFormGUI->getHTML(); exit();
        $this->tpl->setContent($xaseItemFormGUI->getHTML());
        $this->tpl->show();
    }

    public function update()
    {
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

    public function content()
    {
        $this->ctrl->saveParameterByClass(xaseItemTableGUI::class, self::ITEM_IDENTIFIER);
        if (!xaseItemAccess::hasReadAccess($this->xase_settings, $this->xase_item)) {
            ilUtil::sendFailure($this->pl->txt('permission_denied'), true);
        }
        $xaseItemTableGUI = new xaseItemTableGUI($this, self::CMD_STANDARD, $this->object);
        $this->tpl->setContent($xaseItemTableGUI->getHTML());
        $this->tpl->show();
    }

    protected function applyFilter()
    {
        $xaseItemTableGUI = new xaseItemTableGUI($this, self::CMD_STANDARD, $this->object);
        $xaseItemTableGUI->writeFilterToSession();
        $this->ctrl->redirect($this, self::CMD_STANDARD);
    }

    protected function resetFilter()
    {
        $xaseItemTableGUI = new xaseItemTableGUI($this, self::CMD_STANDARD, $this->object);
        $xaseItemTableGUI->resetFilter();
        $xaseItemTableGUI->resetOffset();
        $this->ctrl->redirect($this, self::CMD_STANDARD);
    }

    protected function cancel() {
        $this->ctrl->redirect($this, self::CMD_STANDARD);
    }

    protected function setAnswerStatusToCanBeVoted() {
        $answers_from_user = xaseItemTableGUI::getAnswersFromUser($this, $this->dic);
        if(!empty($answers_from_user)) {
            foreach($answers_from_user as $answer) {
                if($answer->getAnswerStatus() == xaseAnswer::ANSWER_STATUS_ANSWERED) {
                    $answer->setAnswerStatus(xaseAnswer::ANSWER_STATUS_M2_CAN_BE_VOTED);
                }
                $answer->store();
            }
        }
        $this->ctrl->redirect($this, self::CMD_STANDARD);
    }

}