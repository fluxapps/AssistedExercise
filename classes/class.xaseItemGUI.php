<?php
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/AssistedExercise/classes/class.xaseItemTableGUI.php');

/**
 * Class xaseItemGUI
 * @ilCtrl_Calls      xaseItemGUI: xaseItemTableGUI
 * @ilCtrl_isCalledBy xaseItemGUI: ilObjAssistedExerciseGUI
 */


class xaseItemGUI {

    const CMD_STANDARD = 'content';
    const CMD_CREATE = 'create';

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
     * @var int
     */
    protected $assisted_exercise_id;

    public function __construct() {
        global $DIC;
        $this->dic = $DIC;
        $this->tpl = $this->dic['tpl'];
        $this->tabs = $DIC->tabs();
        $this->ctrl = $this->dic->ctrl();
        $this->access = new ilObjAssistedExerciseAccess();
        $this->pl = ilAssistedExercisePlugin::getInstance();
        $this->assisted_exercise_id = $_GET['ref_id'];
    }

    public function executeCommand() {
        $nextClass = $this->ctrl->getNextClass();
        switch ($nextClass) {
            default:
                $cmd = $this->ctrl->getCmd(self::CMD_STANDARD);
                $this->tabs->activateTab(self::CMD_STANDARD);
                $this->{$cmd}();

        }
    }

    public function content() {
        if (! $this->access->hasReadAccess()) {
            ilUtil::sendFailure($this->pl->txt('permission_denied'), true);
        }

/*        global $ilCtrl;
        print_r($ilCtrl->getCallHistory());
        exit;*/

        $xaseItemTableGUI = new xaseItemTableGUI($this, self::CMD_STANDARD);
        $this->tpl->setContent($xaseItemTableGUI->getHTML());
        $this->tpl->show();
    }

    /**
     * @return int
     */
    public function getAssistedExerciseId()
    {
        return $this->assisted_exercise_id;
    }

    protected function applyFilter() {
        $xgeoLocationTableGUI = new xaseItemTableGUI($this, self::CMD_STANDARD);
        $xgeoLocationTableGUI->writeFilterToSession();
        $this->ctrl->redirect($this, self::CMD_STANDARD);
    }


    protected function resetFilter() {
        $xgeoLocationTableGUI = new xaseItemTableGUI($this, self::CMD_STANDARD);
        $xgeoLocationTableGUI->resetFilter();
        $xgeoLocationTableGUI->resetOffset();
        $this->ctrl->redirect($this, self::CMD_STANDARD);
    }

}