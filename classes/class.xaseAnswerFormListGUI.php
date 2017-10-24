<?php
/**
 * Class xaseAnswerFormListGUI
 * @author: Benjamin Seglias   <bs@studer-raimann.ch>
 */

class xaseAnswerFormListGUI extends ilPropertyFormGUI
{
    const CMD_STANDARD = 'show_answers';

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
        $this->initAnswerList();

        parent::__construct();
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
                if ($this->access->hasWriteAccess()) {
                    $this->{$cmd}();
                    break;
                } else {
                    ilUtil::sendFailure(ilAssistedExercisePlugin::getInstance()->txt('permission_denied'), true);
                    break;
                }
        }
    }

    public function initAddHintBtn() {
        $tpl = new ilTemplate('tpl.add_hint_button_code.html', true, true, 'Customizing/global/plugins/Services/Repository/RepositoryObject/AssistedExercise');
        $btn_add_hint = ilJsLinkButton::getInstance();
        $btn_add_hint->setCaption('text_hint_btn');
        $btn_add_hint->setName('hint_btn');
        $btn_add_hint->setId('hint_trigger_text');
        $tpl->setCurrentBlock('CODE');
        $tpl->setVariable('BUTTON', $btn_add_hint->render());
        $tpl->parseCurrentBlock();
        $custom_input_gui = new ilCustomInputGUI();
        $custom_input_gui->setHtml($tpl->get());
        $this->addItem($custom_input_gui);
    }

    /*
     *      Template
     *          Block für jede Antwort mit
     *              non editable value gui html code
     *              upvoting code
     *              Link Kommentar hinzufügen
     *              Hidden Input für Kommentar
     *                  wenn erneut geklickt wird analog vorgehen zu hints
     *              Speichern Button
     *                  bei einem Klick entsprechende Aktion auswählen
     *                      analog zu remove hint btn
     *
     */
    protected function initAnswerList() {

    }
}