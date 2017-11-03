<?php
/**
 * Class xaseSampleSolutionFormGUI
 * @author: Benjamin Seglias   <bs@studer-raimann.ch>
 */

class xaseSampleSolutionFormGUI extends ilPropertyFormGUI
{

    /**
     * @var ilObjAssistedExercise
     */
    public $object;

    /**
     * @var xaseItem
     */
    public $xase_item;

    /**
     * @var xaseSampleSolutionGUI
     */
    protected $parent_gui;

    /**
     * @var xaseSampleSolution
     */
    public $xase_sample_solution;

    /**
     * @var xaseSettings
     */
    public $xase_settings;

    /**
     * @var xaseSettingsM1|xaseSettingsM2|xaseSettingsM3
     */
    protected $mode_settings;

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

    public function __construct($parentGUI, xaseItem $xaseItem)
    {
        global $DIC;
        $this->dic = $DIC;
        $this->tpl = $this->dic['tpl'];
        $this->tabs = $DIC->tabs();
        $this->ctrl = $this->dic->ctrl();
        $this->pl = ilAssistedExercisePlugin::getInstance();
        $this->object = ilObjectFactory::getInstanceByRefId($_GET['ref_id']);
        $this->parent_gui = $parentGUI;
        $this->xase_item = $xaseItem;
        $this->xase_sample_solution = xaseSampleSolution::where(array('id' => $this->xase_item->getSampleSolutionId()))->first();

        parent::__construct();
    }

    public function show_sample_solution() {
        $this->setTarget('_top');
        $this->setFormAction($this->ctrl->getFormAction($this->parent_gui));

        $this->setTitle($this->pl->txt('sample_solution'));

        $item = new ilNonEditableValueGUI($this->pl->txt('task_title'));
        $item->setValue($this->xase_item->getItemTitle());
        $this->addItem($item);

        $sample_solution = new ilNonEditableValueGUI($this->pl->txt('sample_solution'));
        $sample_solution->setValue($this->xase_sample_solution->getSolution());
        $this->addItem($sample_solution);

        $this->addCommandButton(xaseSampleSolutionGUI::CMD_CANCEL, $this->pl->txt("cancel"));
    }
}