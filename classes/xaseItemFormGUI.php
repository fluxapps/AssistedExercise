<?php

class xaseItemFormGUI extends ilPropertyFormGUI {

    const CMD_ADD_HINT = 'addHint';

    /**
     * @var  xaseItem
     */
    protected $object;
    /**
     * @var xaseItemGUI
     */
    protected $parent_gui;

    /*
    * @var  ilCtrl
    */
    protected $ctrl;

    /**
     * @var ilAssistedExercisePlugin
     */
    protected $pl;

    /**
     * @var ilTemplate
     */
    protected $tpl;

    /**
     * @var xaseSettingsM1|null|xaseSettingsM3
     */
    protected $mode_settings;

    /**
     * @var \ILIAS\DI\Container
     */
    protected $dic;

    /**
     * @var boolean
     */
    protected $is_creation_mode;

    /**
     * @var int
     */
    protected $mode;

    public function __construct($parent_gui, xaseItem $xaseItem, $mode) {
        global $DIC;

        $this->dic = $DIC;
        $this->object = $xaseItem;
        $this->pl = ilAssistedExercisePlugin::getInstance();
        $this->tpl = $this->dic['tpl'];
        $this->ctrl = $this->dic->ctrl();
        $this->parent_gui = $parent_gui;
        $this->mode = $mode;

        parent::__construct();

        $this->initForm();
    }

    public function initForm() {
        $this->setTarget('_top');
        $this->setFormAction($this->ctrl->getFormAction($this->parent_gui));
        $this->setTitle($this->pl->txt('item_create'));

        $ti = new ilTextInputGUI($this->pl->txt('title'), 'title');
        $ti->setRequired(true);
        $this->addItem($ti);

        $ta = new ilTextAreaInputGUI($this->pl->txt('task'), 'task');
        $ta->setRows(10);
        $this->addItem($ta);

        if ($this->mode == 1 || $this->mode == 3) {
            $this->initM1AndM3Form();
        }
        $this->addCommandButton(xaseItemGUI::CMD_UPDATE, $this->pl->txt('save'));
        $this->addCommandButton(xaseItemGUI::CMD_STANDARD, $this->pl->txt("cancel"));

        $this->setFormAction($this->ctrl->getFormAction($this));
    }

    public function initM1andM3Form() {
        $this->addCommandButton(self::CMD_ADD_HINT, $this->pl->txt('save'));

        $ta = new ilTextAreaInputGUI($this->pl->txt('sample_solution'), 'sample_solution');
        $ta->setRows(10);
        $this->addItem($ta);

        $ti = new ilTextInputGUI($this->pl->txt('max_points'), 'max_points');
        $ti->setRequired(true);
        $this->addItem($ti);
    }

    public function addHint() {

    }

    public function fillForm() {

    }

    public function fillObject()
    {
        if (!$this->checkInput()) {
            return false;
        }

        return true;
    }

    /**
     * @return bool|string
     */
    public function updateObject() {
        if (! $this->fillObject()) {
            return false;
        }

        $this->object->store();
        return true;
    }

}