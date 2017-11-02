<?php

/**
 * Class xaseAnswerGUI
 * @author  Benjamin Seglias <bs@studer-raimann.ch>
 */

require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/AssistedExercise/classes/class.xaseAnswerFormGUI.php');
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/AssistedExercise/classes/class.xaseAnswerFormListGUI.php');



class xaseAnswerGUI
{
    const ANSWER_IDENTIFIER = 'answer_id';
    const CMD_STANDARD = 'edit';
    const CMD_UPDATE = 'update';
    const CMD_CANCEL = 'cancel';

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

    public function __construct(ilObjAssistedExercise $assisted_exericse)
    {
        global $DIC;
        $this->dic = $DIC;
        $this->tpl = $this->dic['tpl'];
        $this->tabs = $DIC->tabs();
        $this->ctrl = $this->dic->ctrl();
        $this->access = new ilObjAssistedExerciseAccess();
        $this->pl = ilAssistedExercisePlugin::getInstance();
        $this->assisted_exercise = $assisted_exericse;
        $this->xase_settings = xaseSettings::where(['assisted_exercise_object_id' => $this->assisted_exercise->getId()])->first();
        $this->xase_item = new xaseItem($_GET['item_id']);
        $this->xase_answer = $this->getAnswer();
        //parent::__construct();
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
            case self::CMD_UPDATE:
            case self::CMD_CANCEL:
                if ($this->access->hasWriteAccess()) {
                    $this->{$cmd}();
                    break;
                } else {
                    ilUtil::sendFailure(ilAssistedExercisePlugin::getInstance()->txt('permission_denied'), true);
                    break;
                }
        }
    }

    protected function getAnswer() {
        $xaseAnswer = xaseAnswer::where(array( 'item_id' => $this->xase_item->getId(), 'user_id' => $this->dic->user()->getId() ), array( 'item_id' => '=', 'user_id' => '=' ))->first();
        if (empty($xaseAnswer)) {
            $xaseAnswer = new xaseAnswer();
        }
        return $xaseAnswer;
    }

    public function edit()
    {
        $this->tabs->activateTab(xaseItemGUI::CMD_STANDARD);
        $xaseAnswerFormGUI = new xaseAnswerFormGUI($this, $this->assisted_exercise, $this->xase_item);
        $xaseAnswerFormGUI->fillForm();
        $this->tpl->setContent($xaseAnswerFormGUI->getHTML());
        $this->tpl->show();
    }

    public function update()
    {
        $this->tabs->activateTab(xaseItemGUI::CMD_STANDARD);
        $xaseAnswerFormGUI = new xaseAnswerFormGUI($this, $this->assisted_exercise, $this->xase_item);
        if ($xaseAnswerFormGUI->updateObject()) {
            ilUtil::sendSuccess($this->pl->txt('changes_saved_success'), true);
            if($this->xase_settings->getModus() == 2) {
                $this->ctrl->saveParameterByClass(xaseAnswerListGUI::class, xaseItemGUI::ITEM_IDENTIFIER);
                $this->ctrl->redirectByClass(xaseAnswerListGUI::class, xaseAnswerListGUI::CMD_STANDARD);
            } else {
                //$this->ctrl->redirect($this, self::CMD_STANDARD);
                $this->ctrl->redirectByClass(xaseItemGUI::class, xaseItemGUI::CMD_STANDARD);
            }
        } else {
            $xaseAnswerFormGUI->setValuesByPost();
            $xaseAnswerFormGUI->fillTaskInput();
            $this->tpl->setContent($xaseAnswerFormGUI->getHTML());
            $this->tpl->show();
        }
    }

    public function cancel() {
        $this->ctrl->redirectByClass('xaseItemGUI', xaseItemGUI::CMD_CANCEL);
    }
}