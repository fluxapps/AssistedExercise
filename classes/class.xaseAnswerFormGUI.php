<?php
/**
 * Class xaseAnswerFormGUI
 * @author: Benjamin Seglias   <bs@studer-raimann.ch>
 */

require_once('./Services/Form/classes/class.ilPropertyFormGUI.php');

class xaseAnswerFormGUI extends ilPropertyFormGUI
{
    /**
     * @var ilObjAssistedExercise
     */
    public $assisted_exercise;

    /**
     * @var xaseItem
     */
    public $xase_item;

    /**
     * @var xaseSettings
     */
    public $xase_settings;

    /**
     * @var xaseAnswer
     */
    public $xase_answer;
    /**
     * @var xaseAnswerGUI
     */
    protected $parent_gui;

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
     * @var ilCheckboxInputGUI
     */
    protected $toogle_hint_checkbox;

    /**
     * @var int
     */
    protected $mode;


    public function __construct(xaseAnswerGUI $xase_answer_gui, ilObjAssistedExercise $assisted_exericse, xaseItem $xase_item)
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
        $this->xase_item = $xase_item;
        $this->xase_answer = $this->getAnswer();
        $this->mode = $this->xase_settings->getModus();
        $this->parent_gui = $xase_answer_gui;
        parent::__construct();

        $this->tpl->addJavaScript('./Customizing/global/plugins/Services/Repository/RepositoryObject/AssistedExercise/templates/js/tooltip.js');
        $this->initForm();
    }

    protected function getAnswer()
    {
        $xaseAnswer = xaseAnswer::where(array('item_id' => $this->xase_item->getId(), 'user_id' => $this->dic->user()->getId()), array('item_id' => '=', 'user_id' => '='))->first();
        if (empty($xaseAnswer)) {
            $xaseAnswer = new xaseAnswer();
        }
        return $xaseAnswer;
    }

    public function initForm()
    {
        $this->setTarget('_top');
        $this->ctrl->setParameter($this->parent_gui, xaseItemGUI::ITEM_IDENTIFIER, $_GET['item_id']);
        $this->setFormAction($this->ctrl->getFormAction($this->parent_gui));
        $this->setTitle($this->pl->txt('answer_item') . " " . $this->xase_item->getItemTitle());

        $this->initTaskInput();

        if ($this->mode == 1 || $this->mode == 3) {
            $this->toogle_hint_checkbox = new ilCheckboxInputGUI($this->pl->txt('show_hints'), 'show_hints');
            $this->toogle_hint_checkbox->setChecked(true);
            $this->toogle_hint_checkbox->setValue(1);
            $this->addItem($this->toogle_hint_checkbox);
        }

        $item_max_points = $this->getItemMaxPoints($this->xase_item->getPointId());
        $item = new ilNonEditableValueGUI($this->pl->txt('max_points'));
        $item->setValue($item_max_points['max_points']);
        $this->addItem($item);

        $answer = new ilTextAreaInputGUI($this->pl->txt('answer'), 'answer');
        $answer->setRequired(true);
        $answer->setRows(10);
        $this->addItem($answer);

        $this->initHintData();

        $this->initHiddenUsedHintsInput();

        if($this->xase_answer->getAnswerStatus() != xaseAnswer::ANSWER_STATUS_SUBMITTED || $this->xase_answer->getAnswerStatus() != xaseAnswer::ANSWER_STATUS_RATED) {
            $this->addCommandButton(xaseAnswerGUI::CMD_UPDATE, $this->pl->txt('save'));
        }
        $this->addCommandButton(xaseAnswerGUI::CMD_CANCEL, $this->pl->txt("cancel"));
    }

    protected function replace_hint_identifiers_with_glyphs() {
        preg_match_all('/\[hint (\d+)\]/i',$this->xase_item->getTask(), $hint_matches);

        $hint_numbers = $hint_matches[1];

        $replacement_array = [];
        foreach ($hint_numbers as $hint_number) {
            $replacement_array[] = <<<EOT
 <a href="#" data-hint-id="{$hint_number}" class="hint-popover-link"><span class="glyphicon glyphicon-exclamation-sign"></span></a> 
EOT;
        }
        preg_match_all('/\[\/hint\]/',$this->xase_item->getTask(), $hint_delimiter_matches);

        foreach ($hint_delimiter_matches as &$hint_delimiter) {
            foreach($hint_delimiter as $key => $hint_delimiter_string) {
                $hint_delimiter_string = str_replace("/", "\/",$hint_delimiter_string);
                $hint_delimiter_string = str_replace("[", "/\[",$hint_delimiter_string);
                $hint_delimiter[$key] = str_replace("]", "\]/",$hint_delimiter_string);
            }
        }

        $task_text_with_glyphicons = preg_replace($hint_delimiter_matches[0], $replacement_array, $this->xase_item->getTask(), 1);

        $task_text_with_glyphicons_cleaned = preg_replace('/\[hint (\d+)\]/i',"" , $task_text_with_glyphicons);

        return $task_text_with_glyphicons_cleaned;
    }

    /*
     * //TODO check if this method is necessary
     * this method is used after data is sent via post
     */
    public function replace_gaps_with_glyphs() {
        preg_match_all('/h(\d+)/g',$this->xase_item->getTask(), $hint_matches);

        $hint_numbers = $hint_matches[1];

        $replacement_array = [];
        foreach ($hint_numbers as $hint_number) {
            $replacement_array[] = <<<EOT
 <a href="#" data-hint-id="{$hint_number}" class="hint-popover-link"><span class="glyphicon glyphicon-exclamation-sign"></span></a> 
EOT;
        }
        $task_text_with_glyphicons = preg_replace('[\s]', $replacement_array, $this->xase_item->getTask(), 1);

        return $task_text_with_glyphicons;
    }

    protected function initTaskInput() {
        $ta = new ilNonEditableValueGUI($this->pl->txt('task'), 'task', true);

        $test_text_and_html = $this->replace_hint_identifiers_with_glyphs();

        $ta->setValue($test_text_and_html);
        $this->addItem($ta);
    }

    protected function getHintsByItem($item_id) {
        return xaseHint::where(array('item_id' => $item_id))->get();
    }

    protected function getLevelsByHintId($hint_id) {
        return xaseLevel::where(array('hint_id' => $hint_id))->get();
    }

    public function initHintData()
    {

        if ($this->mode == 1 || $this->mode == 3) {
            $tpl = new ilTemplate('tpl.existing_hint_data.html', true, true, 'Customizing/global/plugins/Services/Repository/RepositoryObject/AssistedExercise');
            /**
             * @var $hint xaseHint
             */
            $hints_array = $this->getHintsByItem($this->xase_item->getId());
            foreach ($hints_array as $hint) {
                $tpl->setCurrentBlock('existing_hint_data');

                $hint_array['id'] = $hint->getId();
                $hint_array['item_id'] = $hint->getItemId();
                $hint_array['hint_number'] = $hint->getHintNumber();
                $hint_array['is_template'] = $hint->getisTemplate();
                $hint_array['label'] = $hint->getLabel();

                $tpl->setVariable("CONTENT", htmlentities(json_encode($hint_array, JSON_UNESCAPED_UNICODE)));
                //$tpl->setVariable("HINT_ID", $hint_array['id']);

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

                    if ($level_array['hint_id'] !== $hint_array['id']) {
                        continue;
                    } else {
                        if ($level_array['hint_level'] == 1) {
                            $tpl->setVariable("CONTENT_LEVEL_1", htmlentities(json_encode($level_array, JSON_UNESCAPED_UNICODE)));
                        } else {
                            $tpl->setVariable("CONTENT_LEVEL_2", htmlentities(json_encode($level_array, JSON_UNESCAPED_UNICODE)));
                        }
                    }

                    $minus_point = xasePoint::where(array('id' => $level->getPointId()))->first();

                    $minus_point_array['id'] = $minus_point->getId();
                    $minus_point_array['minus_points'] = $minus_point->getMinusPoints();
                    if (!empty($minus_point_array)){
                        if ($minus_point_array['id'] !== $level_array['point_id']) {
                            continue;
                        } else {
                            if ($level_array['hint_level'] == 1) {
                                $tpl->setVariable("CONTENT_LEVEL_1_MINUS_POINTS", htmlentities(json_encode($minus_point_array, JSON_UNESCAPED_UNICODE)));
                            } else {
                                $tpl->setVariable("CONTENT_LEVEL_2_MINUS_POINTS", htmlentities(json_encode($minus_point_array, JSON_UNESCAPED_UNICODE)));
                            }
                        }
                    }
                }
                $tpl->parseCurrentBlock();
            }
            $custom_input_gui = new ilCustomInputGUI();
            $custom_input_gui->setHtml($tpl->get());
            $this->addItem($custom_input_gui);
        }
    }

    public function initHiddenUsedHintsInput() {
        $hidden_used_hints = new ilHiddenInputGUI('used_hints');
        $this->addItem($hidden_used_hints);
    }

    public function fillForm()
    {
        $array = array(
            'task' => $this->replace_hint_identifiers_with_glyphs(),
            'show_hints' => $this->xase_answer->getShowHints(),
            'answer' => $this->xase_answer->getBody(),
        );
        $this->setValuesByArray($array);
    }

    /*
     * This method is used to fill the task input if the form was sent with invalid data.
     */
    public function fillTaskInput()
    {
        $array = array(
            'task' => $this->replace_hint_identifiers_with_glyphs(),
        );
        $this->setValuesByArray($array);
    }

    public function getTotalMinusPoints($user_id, $item_id) {
        return xasePoint::where(array( 'item_id' => $item_id, 'user_id' => $user_id ), '=')->first();
    }

    protected function getNumberOfUsedHints($hint_data) {
        $number_of_used_hints = 0;

        foreach($hint_data as $hint => $data) {
            $number_of_used_hints++;
        }
        return $number_of_used_hints;
    }

    protected function getNewTotalMinusPoints($hints_array) {
        $total_minus_points = 0;
        foreach($hints_array as $hint => $data) {
            foreach($data as $k => $v) {
                $total_minus_points += $v;
            }
        }
        return $total_minus_points;
    }

    protected function getItemMaxPoints($item_point_id) {
        $statement = $this->dic->database()->query("SELECT max_points FROM ilias.rep_robj_xase_point where id = ".$this->dic->database()->quote($item_point_id, "integer") . " AND max_points IS NOT NULL");
        $result = $statement->fetchAssoc();
        return $result;
    }

    /**
     * @return bool
     */
    public function fillObject()
    {
        if (!$this->checkInput()) {
            return false;
        }
        $this->xase_answer->setUserId($this->dic->user()->getId());
        $this->xase_answer->setItemId($this->xase_item->getId());
        $this->xase_answer->setShowHints($this->getInput('show_hints'));
        $this->xase_answer->setAnswerStatus(xaseAnswer::ANSWER_STATUS_ANSWERED);

        if (empty($this->xase_answer->getUsedHints())) {
            $used_hints = $this->getInput('used_hints');
            $this->xase_answer->setUsedHints($used_hints);
            if(!empty($used_hints)) {
                $this->xase_answer->setNumberOfUsedHints($this->getNumberOfUsedHints($used_hints));
            }
            $xase_point = $this->getTotalMinusPoints($this->dic->user()->getId(), $this->xase_item->getId());

            //if the user has already answered the item but has not used any hints don't create a new entry in the db
            if(empty($xase_point)) {
                $xase_point = new xasePoint();
                $xase_point->setUserId($this->dic->user()->getId());
                $xase_point->setItemId($this->xase_item->getId());
                $item_max_points = $this->getItemMaxPoints($this->xase_item->getPointId());
                $xase_point->setMaxPoints($item_max_points['max_points']);
            }
            if(!empty($used_hints)) {
                $xase_point->setMinusPoints($this->getNewTotalMinusPoints($used_hints));
            }
            $xase_point->store();
            $this->xase_answer->setPointId($xase_point->getId());
        } else {
            $db_used_hints = json_decode($this->xase_answer->getUsedHints(), true);

            $new_used_hints = json_decode($this->getInput('used_hints'), true);

            if(!empty($new_used_hints)) {
                ksort($new_used_hints);
                foreach($new_used_hints as $new_hint => $data) {
                    ksort($data);
                }
                $difference_new_db_hints = array_map('unserialize',
                    array_diff(array_map('serialize', $new_used_hints), array_map('serialize', $db_used_hints)));

                foreach($difference_new_db_hints as $key => $value) {
                    foreach($value as $k => $v) {
                        $db_used_hints[$key][$k] = $v;
                    }
                }
                ksort($db_used_hints);

                foreach($db_used_hints as $hint => $data) {
                    ksort($data);
                }

                $this->xase_answer->setNumberOfUsedHints($this->getNumberOfUsedHints($db_used_hints));

                /**
                 * @var xasePoint $xase_point
                 */
                $xase_point = $this->getTotalMinusPoints($this->dic->user()->getId(), $this->xase_item->getId());

                if(empty($xase_point)) {
                    $xase_point = new xasePoint();
                    $xase_point->setUserId($this->dic->user()->getId());
                    $xase_point->setItemId($this->xase_item->getId());
                    $item_max_points = $this->getItemMaxPoints($this->xase_item->getId());
                    $xase_point->setMaxPoints($item_max_points['max_points']);
                }
                $xase_point->setMinusPoints($this->getNewTotalMinusPoints($db_used_hints));
                $xase_point->store();
                $this->xase_answer->setPointId($xase_point->getId());

            }
        }

        $this->xase_answer->setBody($this->getInput('answer'));
        $this->xase_answer->store();

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
        return true;
    }
}