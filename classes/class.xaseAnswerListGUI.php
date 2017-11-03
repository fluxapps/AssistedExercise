<?php
/**
 * Class xaseAnswerListGUI
 * @author: Benjamin Seglias   <bs@studer-raimann.ch>
 */

class xaseAnswerListGUI
{
    const CMD_STANDARD = 'edit';
    const CMD_UPDATE = 'update';
    const CMD_CANCEL = 'cancel';
    const CMD_COMMENT_ID = 'getNexAvailableCommentId';

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
        $this->xase_item = new xaseItem($_GET['item_id']);

        $this->tpl->addJavaScript('./Customizing/global/plugins/Services/Repository/RepositoryObject/AssistedExercise/templates/js/answerformlist.js');
        //$this->initAnswerList();

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
            case self::CMD_COMMENT_ID:
            if ($this->access->hasReadAccess()) {
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
        $this->ctrl->saveParameterByClass(xaseAnswerFormListGUI::class, xaseItemGUI::ITEM_IDENTIFIER);
        $this->tabs->activateTab(xaseItemGUI::CMD_STANDARD);
        $xaseAnswerFormListGUI = new xaseAnswerFormListGUI($this->assisted_exercise, $this);
        $xaseAnswerFormListGUI->fillForm();
        $this->tpl->setContent($xaseAnswerFormListGUI->getHTML());
        $this->tpl->show();
    }

    public function update()
    {
        $this->ctrl->saveParameterByClass(xaseAnswerFormListGUI::class, xaseItemGUI::ITEM_IDENTIFIER);
        $this->tabs->activateTab(xaseItemGUI::CMD_STANDARD);
        $xaseAnswerFormListGUI = new xaseAnswerFormListGUI($this->assisted_exercise, $this);
        if ($xaseAnswerFormListGUI->updateObject()) {
            ilUtil::sendSuccess($this->pl->txt('changes_saved_success'), true);
            //TODO redirect nur ausführen wenn das votings ab Datum in den Modus Settings noch nicht erreicht wurde + wenn mindestens eine Antwort vorhanden ist für das Item und diese eingereicht wurde
            $this->ctrl->redirectByClass(xaseItemGUI::class, xaseItemGUI::CMD_STANDARD);
        }
        $xaseAnswerFormListGUI->setValuesByPost();
        $this->tpl->setContent($xaseAnswerFormListGUI->getHTML());
        $this->tpl->show();
    }

    protected function cancel() {
        $this->ctrl->redirectByClass('xaseitemgui', xaseItemGUI::CMD_STANDARD);
    }

    public function getNextAvailableCommentId() {
        $statement = $this->dic->database()->query("SELECT * FROM ilias.rep_robj_xase_comment ORDER BY id DESC LIMIT 1");

        $results = array();

        while ($record = $this->dic->database()->fetchAssoc($statement))
        {
            $results[] = $record;
        }

        echo ++$results[0]['id'];

        //return json_encode($results, JSON_UNESCAPED_UNICODE);
    }
}