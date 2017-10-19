<?php
/**
 * Class xaseAssessmentFormGUI
 * @author: Benjamin Seglias   <bs@studer-raimann.ch>
 */

class xaseAssessmentFormGUI extends ilPropertyFormGUI
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
     * @var xaseAnswer
     */
    public $xase_answer;

    /**
     * @var xaseAssessment
     */
    public $xase_assessment;

    /**
     * @var xasePoint
     */
    public $xase_point;

    /**
     * @var xaseAssessmentGUI
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

    public function __construct(xaseAssessmentGUI $xase_assessment_gui, ilObjAssistedExercise $assisted_exericse, xaseItem $xase_item)
    {
        global $DIC;
        $this->dic = $DIC;
        $this->tpl = $this->dic['tpl'];
        $this->tabs = $DIC->tabs();
        $this->ctrl = $this->dic->ctrl();
        $this->access = new ilObjAssistedExerciseAccess();
        $this->pl = ilAssistedExercisePlugin::getInstance();
        $this->assisted_exercise = $assisted_exericse;
        $this->xase_item = $xase_item;
        $this->xase_answer = new xaseAnswer($_GET[xaseAnswerGUI::ANSWER_IDENTIFIER]);
        //$this->xase_answer = $this->getAnswer();
        $this->xase_assessment = $this->getAssessment();
        $this->xase_point = $this->getAssessmentPoints();
        $this->parent_gui = $xase_assessment_gui;
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

    protected function getAssessment() {
        $xaseAssessment = xaseAssessment::where(array('answer_id' => $this->xase_answer->getId()), array('answer_id' => '='))->first();
        if (empty($xaseAssessment)) {
            $xaseAssessment = new xaseAssessment();
        }
        return $xaseAssessment;
    }

    protected function getAssessmentPoints() {
        $xasePoints = xasePoint::where(array('id' => $this->xase_assessment->getPointId()), array('id' => '='))->first();
        if (empty($xasePoints)) {
            $xasePoints = new xasePoint();
        }
        return $xasePoints;
    }

    public function initForm()
    {
        $this->setTarget('_top');
        //TODO check if necessary
        $this->ctrl->setParameter($this->parent_gui, xaseItemGUI::ITEM_IDENTIFIER, $_GET['item_id']);
        $this->setFormAction($this->ctrl->getFormAction($this->parent_gui));
        $this->setTitle($this->pl->txt('assessment_for_item') . " " . $this->xase_item->getItemTitle() . " " . $this->pl->txt('submitted_by') . " " . $this->dic->user()->getFullname());

        $this->toogle_hint_checkbox = new ilCheckboxInputGUI($this->pl->txt('show_used_hints'), 'show_used_hints');
        $this->toogle_hint_checkbox->setChecked(true);
        $this->toogle_hint_checkbox->setValue(1);
        $this->addItem($this->toogle_hint_checkbox);

        $item = new ilNonEditableValueGUI($this->pl->txt('item') . " " . $this->xase_item->getItemTitle(), 'item', true);
        $item->setValue($this->xase_item->getTask());
        $this->addItem($item);

        $answer = new ilNonEditableValueGUI($this->pl->txt('answer'), 'answer', true);
        $answer->setValue($this->xase_answer->getBody());
        $this->addItem($answer);

        $comment = new ilTextAreaInputGUI($this->pl->txt('comment'), 'comment');
        $comment->setRequired(true);
        $comment->setRows(10);
        $this->addItem($comment);

        $this->initUsedHintsForm();

        $this->addCommandButton(xaseAssessmentGUI::CMD_UPDATE, $this->pl->txt('save'));
        $this->addCommandButton(xaseAssessmentGUI::CMD_CANCEL, $this->pl->txt("cancel"));
    }

    /*
     * 1) xaseAnswer used_hints holen / alle hints die der Benutzer zur Beantwortung des Items verwendet hat
     * 2) json decode
     * 3) hint ids herauslesen
     * 4) entsprechende hints holen
     * 5) levels mit entsprechenden ids holen
     */
/*    protected function getHints() {
        $used_hints = json_decode($this->xase_answer->getUsedHints(), true);
        $hint_ids = [];
        foreach($used_hints as $used_hint) {

        }
    }*/

    protected function getLevels($hints) {

    }

    protected function checkLevel($hint_array) {
        $is_level_1 = false;
        $is_level_2 = false;
        $array_keys = array_keys($hint_array);
        foreach($array_keys as $array_key) {
            if(is_array($array_key)) {
                if(in_array('1', $array_key)) {
                    $is_level_1 = true;
                } elseif(in_array('2', $array_key)) {
                    $is_level_2 = true;
                }
            } else {
                if(strpos($array_key, '1') !== false) {
                    $is_level_1 = true;
                } elseif(strpos($array_key, '2') !== false) {
                    $is_level_2  = true;
                }
            }
        }
        return array(
            'is_level_1' => $is_level_1,
            'is_level_2' => $is_level_2
        );
    }

    protected function getListingArray($hint_object, $check_level_array, $listing_array) {
        if($check_level_array['is_level_1']) {
            $level_1_object = xaseLevel::where(array('hint_id' => $hint_object->getId(), 'hint_level' => 1))->first();
            $level_1_hint_data = $level_1_object->getHint();
            $level_1_minus_points = xasePoint::where(array('id'=> $level_1_object->getPointId()))->first();
            $level_1_minus_points_data = $level_1_minus_points->getMinusPoints();
        }
        if($check_level_array['is_level_2']) {
            $level_2_object = xaseLevel::where(array( 'hint_id' => $hint_object->getId(), 'hint_level' => 2))->first();
            $level_2_hint_data = $level_2_object->getHint();
            $level_2_minus_points = xasePoint::where(array('id' => $level_2_object->getPointId()))->first();
            $level_2_minus_points_data = $level_2_minus_points->getMinusPoints();
        }
        if(!empty($level_1_hint_data) || !empty($level_2_hint_data)) {
            if(!empty($level_1_hint_data) && !empty($level_2_hint_data)) {
                $listing_array[$hint_object->getLabel()] = $level_1_hint_data . " Minus Points: " . $level_1_minus_points_data . " " . $level_2_hint_data . " Minus Points: " . $level_2_minus_points_data;
                return $listing_array;
            }
            if(!empty($level_1_hint_data)) {
                $listing_array[$hint_object->getLabel()] = $level_1_hint_data . " Minus Points: " . $level_1_minus_points_data;
            }
            if(!empty($level_2_hint_data)) {
                $listing_array[$hint_object->getLabel()] = $level_2_hint_data . " Minus Points: " . $level_2_minus_points_data;
            }
        }
        return $listing_array;
    }

    public function createListing()
    {
        $f = $this->dic->ui()->factory();
        $renderer = $this->dic->ui()->renderer();

        $used_hints = json_decode($this->xase_answer->getUsedHints(), true);
        $hint_ids = array_keys($used_hints);
        $hint_objects = [];
        foreach($hint_ids as $hint_id) {
            $hint_object = xaseHint::where(['id' => $hint_id])->first();
            $hint_objects[] = $hint_object;
        }
        /*TODO save all data in a key value pair array. The value should contain all the necessary data, set the corresponding data
         * 1) loop through hint objects
         * 2) set the hint label as text for the listing
         * 3) get the hint array in the used_hints array with the id of the hint object of the current iteration
         * 4) check which data level the hint is
         * 5) save in a variable if it is level 1 or level 2 hint
         * 6) retrieve the corresponding level db record with the hint id and the level number
         * 7) set the actual hint content as text for the listing
         * 8) retrieve the corresponding points db entry
         * 9) set the minus points, with a short text, next to the corresponding level hint
         * TODO after this method: Points Input, noneditablevaluegui max points (without minus points), calculate max-points minus minus points and show the max possible points in a non editable value input gui
         */

        $listing_array = [];
        //TODO finish the case when hint_objects is an array
        if(is_array($hint_objects)) {
            foreach($hint_objects as $hint_object) {
                $hint_array = $used_hints[$hint_object->getId()];

                $check_level_array = $this->checkLevel($hint_array);

                $listing_array = $this->getListingArray($hint_object, $check_level_array, $listing_array);

/*                $array_keys = array_keys($hint_array);
                foreach($array_keys as $array_key) {
                    if(in_array('1', $array_key)) {
                        $is_level_1 = true;
                    } elseif(in_array('2', $array_key)) {
                        $is_level_2 = true;
                    }
                }*/
 /*               if($check_level_array['is_level_1']) {
                    $level_1_data = xaseLevel::where(array('hint_id' => $hint_object->getId(), 'hint_level' => 1))->first();
                    $level_1_data_hint = "hint";
                } elseif($check_level_array['is_level_2']) {
                    $level_2_data = xaseLevel::where(array('hint_id' => $hint_object->getId(), 'hint_level' => 2))->first();
                }*/
            }
        } else{
            $hint_array = $used_hints[$hint_objects->getId()];
            $check_level_array = $this->checkLevel($hint_array);
            $listing_array = $this->getListingArray($hint_objects, $check_level_array, $listing_array);
/*            if($check_level_array['is_level_1']) {
                $level_1_object = xaseLevel::where(array('hint_id' => $hint_objects->getId(), 'hint_level' => 1))->first();
                $level_1_hint_data = $level_1_object->getHint();
                $level_1_minus_points = xasePoint::where(array('id', $level_1_object->getPointId()))->first();
                $level_1_minus_points_data = $level_1_minus_points->getMinusPoints();
            } elseif($check_level_array['is_level_2']) {
                $level_2_object = xaseLevel::where(array( 'hint_id' => $hint_objects->getId(), 'hint_level' => 2))->first();
                $level_2_hint_data = $level_2_object->getHint();
                $level_2_minus_points = xasePoint::where(array('id' => $level_2_object->getPointId()))->first();
                $level_2_minus_points_data = $level_2_minus_points->getMinusPoints();
            }
            if(!empty($level_1_hint_data) || !empty($level_2_hint_data)) {
                if(!empty($level_1_hint_data) && !empty($level_2_hint_data)) {
                    $listing_array[$hint_objects->getLabel()] = $level_1_hint_data . " Minus Points: " . $level_1_minus_points_data . $level_2_hint_data . " Minus Points: " . $level_2_minus_points_data;
                }
                elseif(!empty($level_1_hint_data)) {
                    $listing_array[$hint_objects->getLabel()] = $level_1_hint_data . " Minus Points: " . $level_1_minus_points_data;
                }
                elseif(!empty($level_2_hint_data)) {
                    $listing_array[$hint_objects->getLabel()] = $level_2_hint_data . " Minus Points: " . $level_2_minus_points_data;
                }
            }*/
        }

        $unordered = $f->listing()->descriptive($listing_array);

        return $renderer->render($unordered);
    }

    public function initUsedHintsForm() {
        /*
         * 1) retrieve the hint data for the listing
         * 2) loop through the data
         *  a) call createListing for each hint_object
         *      a) IN CREATELISTING retrieve level and point data
         *      b) set the data
         *      c) return the rendered list
         *  b) save, in the loop through the hint data, the returned listing in a custom input gui
         *  c) add the custominputgui, also in the loop, to the form
         */

/*        $used_hints = json_decode($this->xase_answer->getUsedHints(), true);
        $hint_ids = array_keys($used_hints);
        $hint_objects = [];
        foreach($hint_ids as $hint_id) {
            $hint_object = xaseHint::where(['id' => $hint_id])->first();
            $hint_objects = $hint_object;
        }
        //TODO check if template is necessary for each hint object
        foreach($hint_objects as $hint_object) {
            $list = $this->createListing($hint_object);
            $custom_input = new ilCustomInputGUI($list);
            $this->addItem($custom_input);
        }*/

        $custom_input_gui = new ilCustomInputGUI($this->pl->txt('used_hints'));
        $custom_input_gui->setHtml($this->createListing());
        $this->addItem($custom_input_gui);
    }
    //TODO implement method
    public function fillForm() {

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