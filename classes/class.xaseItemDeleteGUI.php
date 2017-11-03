<?php

/**
 * Class xaseItemDeleteGUI
 * @ilCtrl_isCalledBy xaseItemDeleteGUI: ilObjAssistedExerciseGUI
 */

require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/AssistedExercise/classes/class.xaseItemDeleteFormGUI.php');

require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/AssistedExercise/classes/services/xaseItemAccess.php');

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
                if (xaseItemAccess::hasDeleteAccess($this->xase_settings, $this->xase_item)) {
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

        $cgui->addItem('', '', xaseItem::where(array('id' => $_GET['item_id']))->first()->getItemTitle(), ilObject::_getIcon($this->object->getId()));


        $this->tpl->setContent($cgui->getHTML());
        $this->tpl->show();
    }

    public function confirmedDelete()
    {
        // Get Item
        $xaseItem = xaseItem::where(array('id' => $_GET['item_id']))->first();

        // Delete SampleSolution
        xaseSampleSolution::where(array('id' => $xaseItem->getSampleSolutionId()))->first()->delete();

        // Get all Hints and delete all associated Level and finally the Hint itself
        $xaseHints = xaseHint::where(array('item_id' => $_GET['item_id']))->get();
        var_dump($xaseHints);
        foreach ($xaseHints as $xaseHint) {
            // Get and delete all Level and associated Points
            $xaseLevels = xaseLevel::where(array('hint_id' => $xaseHint->getId()))->get();
            foreach ($xaseLevels as $xaseLevel) {
                $xasePoint = xasePoint::where(array('id' => $xaseLevel->getPointId()))->first();
                if($xasePoint !== null) $xasePoint->delete();

                $xaseLevel->delete();
            }

            // Delete Hint
            $xaseHint->delete();
        }

        // Delete all Points
        $xasePoints = xasePoint::where(array('item_id' => $_GET['item_id']))->get();
        foreach ($xasePoints as $xasePoint) {
            $xasePoint->delete();
        }

        // Get all Answers and delete all associated Comments, Votings and finally the Answer itself
        $xaseAnswers = xaseAnswer::where(array('item_id' => $_GET['item_id']))->get();
        foreach ($xaseAnswers as $xaseAnswer) {
            // Get and delete all Comments
            $xaseComments = xaseComment::where(array('answer_id' => $xaseAnswer->getId()))->get();
            foreach ($xaseComments as $xaseComment) {
                $xaseComment->delete();
            }

            // Get and delete all Votings
            $xaseVotings = xaseVoting::where(array('answer_id' => $xaseAnswer->getId()))->get();
            foreach ($xaseVotings as $xaseVoting) {
                $xaseVoting->delete();
            }

            // Delete Answer
            $xaseAnswer->delete();
        }

        // Delete the Item itself
        $xaseItem->delete();

        ilUtil::sendSuccess($this->pl->txt('successfully_deleted'), true);
        $this->ctrl->redirectByClass("xaseItemGUI");
    }

    public function canceledDelete()
    {
        $this->ctrl->redirectByClass("xaseItemGUI");
    }
}