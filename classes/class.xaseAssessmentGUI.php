<?php
/**
 * Class xaseAssessmentGUI
 * @author: Benjamin Seglias   <bs@studer-raimann.ch>
 */

require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/AssistedExercise/classes/class.xaseAssessmentFormGUI.php');

class xaseAssessmentGUI
{
    const   CMD_STANDARD = 'edit';
    const   CMD_UPDATE = 'update';
    const   CMD_CANCEL = 'cancel';
    const   CMD_VIEW_ASSESSMENT = 'view_assessment';

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
     * @var int
     */
    protected $is_student;

    public function __construct(ilObjAssistedExercise $assisted_exercise)
    {
        global $DIC;
        $this->dic = $DIC;
        $this->tpl = $this->dic['tpl'];
        $this->tabs = $DIC->tabs();
        $this->ctrl = $this->dic->ctrl();
        $this->access = new ilObjAssistedExerciseAccess();
        $this->pl = ilAssistedExercisePlugin::getInstance();
        $this->xase_item = new xaseItem($_GET['item_id']);
        $this->assisted_exercise = $assisted_exercise;
    }

    public function executeCommand()
    {
        $nextClass = $this->ctrl->getNextClass();
        switch ($nextClass) {
            default:
                $cmd = $this->ctrl->getCmd(self::CMD_STANDARD);
                $this->tabs->activateTab(xaseItemGUI::CMD_STANDARD);
                $this->{$cmd}();
        }
    }

    protected function performCommand()
    {
        $cmd = $this->ctrl->getCmd(self::CMD_STANDARD);
        switch ($cmd) {
            case self::CMD_STANDARD:
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

    protected function isStudent() {
        if ($this->access->hasWriteAccess($_GET['ref_id'], $this->dic->user()->getId())) {
           $this->is_student = false;
        } else {
            $this->is_student = true;
        }
    }

    public function view_assessment() {
        $this->ctrl->saveParameter($this, xaseAnswerGUI::ANSWER_IDENTIFIER);
        $this->tabs->activateTab(xaseSubmissionGUI::CMD_STANDARD);
        $xaseAssessmentFormGUI = new xaseAssessmentFormGUI($this, $this->assisted_exercise, true);
        $xaseAssessmentFormGUI->fillForm();
        $this->tpl->setContent($xaseAssessmentFormGUI->getHTML());
        $this->tpl->show();
    }

    public function edit()
    {
        $this->ctrl->saveParameter($this, xaseAnswerGUI::ANSWER_IDENTIFIER);
        $this->tabs->activateTab(xaseSubmissionGUI::CMD_STANDARD);
        $xaseAssessmentFormGUI = new xaseAssessmentFormGUI($this, $this->assisted_exercise);
        $xaseAssessmentFormGUI->fillForm();
        $this->tpl->setContent($xaseAssessmentFormGUI->getHTML());
        $this->tpl->show();
    }

    public function update()
    {
        $this->ctrl->saveParameter($this, xaseAnswerGUI::ANSWER_IDENTIFIER);
        $this->tabs->activateTab(xaseSubmissionGUI::CMD_STANDARD);
        $xaseAssessmentFormGUI = new xaseAssessmentFormGUI($this, $this->assisted_exercise);
        if ($xaseAssessmentFormGUI->updateObject()) {
            ilUtil::sendSuccess($this->pl->txt('changes_saved_success'), true);
            $this->ctrl->redirect($this, self::CMD_STANDARD);
        }

        $xaseAssessmentFormGUI->setValuesByPost();
        $this->tpl->setContent($xaseAssessmentFormGUI->getHTML());
        $this->tpl->show();
    }

    public function cancel() {
        if (!ilObjAssistedExerciseAccess::hasWriteAccess($_GET['ref_id'], $this->dic->user()->getId())) {
            $this->ctrl->redirectByClass('xaseitemgui', xaseItemGUI::CMD_STANDARD);
        } else {
            $this->ctrl->redirectByClass('xasesubmissiongui', xaseSubmissionGUI::CMD_STANDARD);
        }
    }
}