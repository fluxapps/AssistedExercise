<?php

/**
 * Class xaseItemFormGUI
 * @author  Benjamin Seglias <bs@studer-raimann.ch>
 */

require_once('./Services/UIComponent/Button/classes/class.ilJsLinkButton.php');
require_once('./Services/Form/classes/class.ilPropertyFormGUI.php');
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/AssistedExercise/classes/class.xaseSampleSolutionGUI.php');
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/AssistedExercise/classes/class.ilHintInputGUI.php');
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/AssistedExercise/classes/ActiveRecords/class.xaseLevel.php');

class xaseItemFormGUI extends ilPropertyFormGUI
{
    const M1 = 1;
    const M2 = 2;
    const M3 = 3;

    /**
     * @var  xaseItem
     */
    protected $object;
    /**
     * @var xaseItemGUI
     */
    protected $parent_gui;

    /**
     * @var ilObjAssistedExercise
     */
    protected $assisted_exercise;

    /**
     * @var xaseSampleSolution
     */
    protected $xase_sample_solution;

    /**
     * @var xasePoint
     */
    protected $xase_point;

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
     * @var xaseSettings
     */
    protected $xase_settings;

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

    /**
     * @var xaseHint[]
     */
    protected $xase_hints = [];

    /**
     * @var ilHintInputGUI
     */
    protected $hint_input_gui;

    public function __construct($parent_gui, xaseItem $xaseItem, xaseSettings $xaseSettings)
    {
        global $DIC;

        $this->dic = $DIC;
        $this->object = $xaseItem;
        $this->pl = ilAssistedExercisePlugin::getInstance();
        $this->tpl = $this->dic['tpl'];
        $this->ctrl = $this->dic->ctrl();
        $this->parent_gui = $parent_gui;
        $this->assisted_exercise = ilObjectFactory::getInstanceByRefId($_GET['ref_id']);
        $this->xase_sample_solution = $this->getXaseSampleSolution($this->object->getSampleSolutionId());
        $this->xase_point = $this->getXasePoint($this->object->getPointId());

        $this->xase_settings = $xaseSettings;
        $this->mode = $xaseSettings->getModus();
        //$this->xase_settings = xaseSettings::where(['assisted_exercise_object_id' => $this->object->getId()])->first();
        if($this->xase_settings->getModus() == self::M1) {
            $this->mode_settings = $this->getModusSettings(xaseSettingsM1::class);//xaseSettingsM1::where(['settings_id' => $this->xase_settings->getId()])->first();
        } elseif($this->xase_settings->getModus() == self::M3) {
            $this->mode_settings = $this->getModusSettings(xaseSettingsM3::class);//xaseSettingsM3::where(['settings_id' => $this->xase_settings->getId()])->first();
        }
        $this->xase_hints = $this->getHintsByItem($this->object->getId());
        parent::__construct();

        $this->tpl->addJavaScript('./Customizing/global/plugins/Services/Repository/RepositoryObject/AssistedExercise/templates/js/hint.js');
        $this->initForm();
    }

    public function initForm()
    {
        $this->setTarget('_top');
        $this->setTitle($this->pl->txt('task_create'));

        $ti = new ilTextInputGUI($this->pl->txt('title'), 'title');
        $ti->setRequired(true);
        $this->addItem($ti);

        $ta = new ilTextAreaInputGUI($this->pl->txt('task'), 'task');
        $ta->setRequired(true);
        $ta->setRows(10);
        if($this->xase_settings->getModus() != self::M2) {
            $ta->setInfo($this->pl->txt('info_hints'));
        }
        $this->addItem($ta);

        if ($this->mode == 1 || $this->mode == 3) {
            $this->initM1AndM3Form();
            $header = new ilFormSectionHeaderGUI();
            $header->setTitle( $this->pl->txt( $this->pl->txt('hints')));
            $this->addItem( $header );

            $this->initHintForm();
        }

        $this->addCommandButton(xaseItemGUI::CMD_UPDATE, $this->pl->txt('save'));
        $this->addCommandButton(xaseItemGUI::CMD_CANCEL, $this->pl->txt("cancel"));

        //$this->ctrl->setParameter($this->parent_gui, xaseItemGUI::ITEM_IDENTIFIER, $this->object->getId());
        $this->ctrl->setParameter($this->parent_gui, xaseItemGUI::ITEM_IDENTIFIER, $_GET['item_id']);
        $this->setFormAction($this->ctrl->getFormAction($this));
    }

    public function initM1andM3Form()
    {
        $this->initAddHintBtn();

        //TODO Decide based on Modus wether this input is required or not
        $sol = new ilTextAreaInputGUI($this->pl->txt('sample_solution'), 'sample_solution');

        if($this->xase_settings->getModus() === '1') {
            if($this->mode_settings->getSampleSolutionVisible()) {
                $sol->setRequired(true);
            } else {
                $sol->setRequired(false);
            }
        }

        $sol->setRows(10);
        $this->addItem($sol);

        $max_points = new ilNumberInputGUI($this->pl->txt('specify_max_points'), 'max_points');
        $max_points->setRequired(true);
        $max_points->setSize(4);
        $max_points->setMaxLength(4);
        $this->addItem($max_points);

    }

    public function initAddHintBtn() {
        $tpl = new ilTemplate('tpl.add_hint_button_code.html', true, true, 'Customizing/global/plugins/Services/Repository/RepositoryObject/AssistedExercise');
        $btn_add_hint = ilJsLinkButton::getInstance();
        $btn_add_hint->setCaption($this->pl->txt("add_hint_btn_caption"), false);
        $btn_add_hint->setName('hint_btn');
        $btn_add_hint->setId('hint_trigger_text');
        $tpl->setCurrentBlock('CODE');
        $tpl->setVariable('BUTTON', $btn_add_hint->render());
        $tpl->parseCurrentBlock();
        $custom_input_gui = new ilCustomInputGUI();
        $custom_input_gui->setHtml($tpl->get());
        $this->addItem($custom_input_gui);
    }

    public function initRemoveHintBtn() {
        $tpl = new ilTemplate('tpl.remove_hint_button_code.html', true, true, 'Customizing/global/plugins/Services/Repository/RepositoryObject/AssistedExercise');
        $btn_remove_hint = ilJsLinkButton::getInstance();
        $btn_remove_hint->setCaption($this->pl->txt('text_remove_hint_btn'));
        $btn_remove_hint->setName('text_remove_hint_btn');

        $tpl->setCurrentBlock('CODE');
        $tpl->setVariable('BUTTON', $btn_remove_hint->render());
        $tpl->parseCurrentBlock();
        $custom_input_gui = new ilCustomInputGUI();
        $custom_input_gui->setHtml($tpl->get());
        $this->addItem($custom_input_gui);
    }

    public function initHintForm() {
        $this->hint_input_gui = new ilHintInputGUI($this->pl->txt('hints'), "");
        $this->addItem($this->hint_input_gui);
        return $this;
    }

    protected function getXaseSampleSolution($sample_solution_id) {
        $xaseSampleSolution = xaseSampleSolution::where(array('id' => $sample_solution_id))->first();
        if (empty($xaseSampleSolution)) {
            $xaseSampleSolution = new xaseSampleSolution();
        }
        return $xaseSampleSolution;
    }

    protected function getXasePoint($point_id) {
        $xasePoint = xasePoint::where(array('id' => $point_id))->first();
        if (empty($xasePoint)) {
            $xasePoint = new xasePoint();
        }
        return $xasePoint;
    }

    protected function getModusSettings($modus_settings) {
        $xaseModus = $modus_settings::where(array('settings_id' => $this->xase_settings->getId()))->first();
        if (empty($xaseModus)) {
            if($this->xase_settings->getModus() == self::M1) {
                $xaseModus = new xaseSettingsM1();
            } else {
                $xaseModus = new xaseSettingsM3();
            }
        }
        return $xaseModus;
    }

    public function fillForm()
    {
        $array = array (
            'title' => $this->object->getItemTitle(),
            'task' => $this->object->getTask()
        );
        if ($this->mode == 1 || $this->mode == 3) {
            /**
             * @var xaseSampleSolution $xaseSampleSolution
             */
/*            $xaseSampleSolution = xaseSampleSolution::where(array('id' => $this->object->getSampleSolutionId()))->get();*/
            if($this->xase_sample_solution) {
                $array["sample_solution"] = $this->xase_sample_solution->getSolution();
            }
            /**
             * @var xasePoint $xasePoints
             */
            /*$xasePoints = xasePoint::where(array('id' => $this->object->getPointId()))->get();*/

            if ($this->xase_point) {
                $array["max_points"] = $this->xase_point->getMaxPoints();
            }
            //TODO finish fillForm
            /**
             * @var $hint xaseHint
             */
            $hints_array = $this->getHintsByItem($this->object->getId());
            foreach ($hints_array as $hint) {
                $hint_array['id'] = $hint->getId();
                $hint_array['item_id'] = $hint->getItemId();
                $hint_array['hint_number'] = $hint->getHintNumber();
                $hint_array['is_template'] = $hint->getisTemplate();
                $hint_array['label'] = $hint->getLabel();

/*                $json_encoded_hint = json_encode($hint_array);
                $json_hints[] = $json_encoded_hint;*/

                $hints[] = $hint_array;

                $levels_array = $this->getLevelsByHintId($hint_array['id']);

                /**
                 * @var $level xaseLevel
                 */
                foreach ($levels_array as $level) {
                    $level_array['id'] = $level->getId();
                    $level_array['hint_id'] = $level->getHintId();
                    $level_array['point_id'] = $level->getPointId();
                    $level_array['hint_level'] = $level->getHintLevel();
                    $level_array['hint'] = $level->getHint();

/*                    $json_encoded_level = json_encode($level_array);
                    $json_levels[] = $json_encoded_level;*/

                    $levels[] = $level_array;

                    $point = xasePoint::where(array('id' => $level->getPointId()))->first();

                    if(!empty($point)) {
                        /**
                         * @var $point xasePoint
                         */
                        $point_array['id'] = $point->getId();
                        $point_array['minus_points'] = $point->getMinusPoints();

                        //$json_encoded_point = json_encode($point_array);
                        $points[] = $point_array;
                    }
                }
            }
            $this->hint_input_gui->setExistingHintData($hints);
            $this->hint_input_gui->setExistingLevelData($levels);
            $this->hint_input_gui->setMinusPoints($points);

        }
            $this->setValuesByArray($array);
    }

    public function fillObject()
    {
        if (!$this->checkInput()) {
            return false;
        }
        $this->object->setAssistedExerciseId($this->assisted_exercise->getId());
        $this->object->setItemTitle($this->getInput('title'));
        $this->object->setTask($this->getInput('task'));
        $this->object->store();
        $this->ctrl->setParameterByClass(xaseItemGUI::class, xaseItemGUI::ITEM_IDENTIFIER, $this->object->getId());
        if($this->xase_settings->getModus() != self::M2) {
            $this->xase_sample_solution->setSolution($this->getInput('sample_solution'));
            $this->xase_sample_solution->store();
            $this->object->setSampleSolutionId($this->xase_sample_solution->getId());

            //xase_points has to be persisted twice in order to get the id for the object and set the item id from the object
            $this->xase_point->setMaxPoints($this->getInput('max_points'));
            $this->xase_point->store();
            $this->object->setPointId($this->xase_point->getId());
            $this->object->store();
            $this->xase_point->setItemId($this->object->getId());
            $this->xase_point->store();
        }
        return true;
    }

    protected function getHintsByItem($item_id) {
        return xaseHint::where(array('item_id' => $item_id))->get();
    }

    protected function getLevelsByHintId($hint_id) {
        return xaseLevel::where(array('hint_id' => $hint_id))->get();
    }

    protected function getMaxHintNumber($item_id) {
        $this->dic->database()->query("SELECT max(hint_number) FROM ilias.rep_robj_xase_hint where item_id = ".$this->dic->database()->quote($item_id, "integer"));
    }

    /*
     * store hint number in hint table
     * 1) get hint numbers from task text
     * 2) check if a hint for this item with the corresponding hint number already exists
     *  a) yes
     *      update hint
     *  b) no
     *      create new hint
     * store the hint information from post with the right index in the corresponding hint
     */

    /**
     * wenn hint bereits existiert id von hint geben statt 0, 1, 2, 3...
     */
    protected function fillHintObjects() {
        $task = $this->object->getTask();
        /*        preg_match_all('(\d+)', $task, $matches);
                $matches = array_unique($matches);
                for ($i = 0; $i < count($matches); $i++) {

                }*/

        $max_hint_number = $this->getMaxHintNumber($this->object->getId());

        if (is_array($_POST['hint'])) {
            foreach ($_POST['hint'] as $id => $data) {
                if(!empty($this->xase_hints)) {
                    foreach ($this->xase_hints as $xase_hint) {
                        if ($data['hint_id'] == $xase_hint->getId()) {
                            $hint = $xase_hint;
                        }
                    }
                }
                if(empty($hint) || empty($this->xase_hints) ||  $data['hint_id'] !== $hint->getId()) {
                    $hint = new xaseHint();
                }

                if ($data["is_template"] == 0) {
                    continue;
                }

                $hint->setItemId($this->object->getId());
                if(empty($max_hint_number)) {
                    $hint->setHintNumber($id);
                } else {
                    $max_hint_number++;
                    $hint->setHintNumber($max_hint_number);
                }

                $hint->setIsTemplate($data["is_template"]);
                $hint->setLabel($data["label"]);

                $hint->store();

                $levels = $this->getLevelsByHintId($hint->getId());

                if (empty($levels)) {
                    $level_1 = new xaseLevel();
                    $level_1->setHintId($hint->getId());
                    $level_1_point = new xasePoint();
                    $level_1_point->setMinusPoints($data["lvl_1_minus_points"]);
                    $level_1_point->store();
                    $level_1->setPointId($level_1_point->getId());
                    $level_1->setHintLevel(1);
                    $level_1->setHint($data["lvl_1_hint"]);
                    $level_1->store();

                    $level_2 = new xaseLevel();
                    $level_2->setHintId($hint->getId());
                    $level_2_point = new xasePoint();
                    $level_2_point->setMinusPoints($data["lvl_2_minus_points"]);
                    $level_2_point->store();
                    $level_2->setPointId($level_2_point->getId());
                    $level_2->setHintLevel(2);
                    $level_2->setHint($data["lvl_2_hint"]);
                    $level_2->store();
                } else {

                    /**
                     * @var xaseLevel $level
                     */
/*                    foreach($levels as $level) {
                        $level->setHintLevel(1);
                        $level->setHint($data["lvl_1_hint"]);
                        $level->set
                    }*/
                    //TODO store points in points table and save hint_id
/*                    $levels[0]['hint_level'] = 1;
                    $levels[0]['lvl_1_hint'] = $data["lvl_1_hint"];
                    $levels[0]['lvl_1_minus_points'] = $data["lvl_1_minus_points"];

                    $levels[1]['hint_level'] = 2;
                    $levels[1]['lvl_2_hint'] = $data["lvl_2_hint"];
                    $levels[1]['lvl_2_minus_points'] = $data["lvl_2_minus_points"];*/

                    /**
                     * @var xaseLevel $level
                     */
                    foreach($levels as $level) {

                        $level->store();
                    }
                }
            }
        }
        return true;
    }

    /**
     * @return bool|string
     */
    public function updateObject()
    {
        if (!$this->fillObject()) {
            return false;
        }
//        $this->object->store();

        if($this->xase_settings->getModus() != self::M2) {
            if(!$this->fillHintObjects()) {
                return false;
            }
        }
        return true;
    }

}