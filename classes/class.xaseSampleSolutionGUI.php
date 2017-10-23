<?php

/**
 * Class xaseSampleSolutionGUI
 * @author: Benjamin Seglias   <bs@studer-raimann.ch>
 */

class xaseSampleSolutionGUI
{
    const ITEM_IDENTIFIER = 'item_id';
    const CMD_STANDARD = 'show_sample_solution';
    const CMD_CANCEL = 'cancel';

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
            case 'xaseitemgui':
                //has to be called because in this case parent::executeCommand is not executed(contains getStandardTempplate and Show)
                //Show Method has to be called in the corresponding methods
                $xaseItemGUI = new xaseItemGUI();
                $this->ctrl->forwardCommand($xaseItemGUI);
                break;

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
                if ($this->access->hasReadAccess()) {
                    $this->{$cmd}();
                    break;
                } else {
                    ilUtil::sendFailure(ilAssistedExercisePlugin::getInstance()->txt('permission_denied'), true);
                    break;
                }
        }
    }

    public function show_sample_solution()
    {
        $this->ctrl->saveParameter($this, self::ITEM_IDENTIFIER);
        $this->tabs->activateTab(self::CMD_STANDARD);
        $xaseSampleSolutionFormGUI = new xaseSampleSolutionFormGUI($this, $this->xase_item);
        $xaseSampleSolutionFormGUI->show_sample_solution();
        $this->tpl->setContent($xaseSampleSolutionFormGUI->getHTML());
        $this->tpl->show();
    }

    protected function cancel() {
        $this->ctrl->redirectByClass(array('ilObjAssistedExerciseGUI', 'xaseitemgui'),xaseItemGUI::CMD_STANDARD);
    }
}