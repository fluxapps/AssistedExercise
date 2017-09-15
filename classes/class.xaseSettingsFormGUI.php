<?php

require_once('./Services/Form/classes/class.ilPropertyFormGUI.php');
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/AssistedExercise/classes/ActiveRecords/class.xaseSettingsM1.php');
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/AssistedExercise/classes/ActiveRecords/class.xaseSettingsM3.php');

class xaseSettingsFormGUI extends ilPropertyFormGUI {

    const M1 = 1;
    const M2 = 2;
    const M3 = 3;

    /**
     * @var  xaseSettings
     */
    protected $object;
    /**
     * @var ilObjAssistedExerciseGUI
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
     * @var ilAssistedExercisePlugin
     */
    protected $mode_settings;

    /**
     * @var \ILIAS\DI\Container
     */
    protected $dic;

    //TODO decide with $mode which xaseSettingsMN Class should be additionally used to xaseSettings
    public function __construct($parent_gui, xaseSettings $xaseSettings, $mode = self::M1) {
        global $DIC;

        $this->dic = $DIC;
        $this->object = $xaseSettings;
        $this->pl = ilAssistedExercisePlugin::getInstance();
        $this->tpl = $this->dic['tpl'];
        $this->ctrl = $this->dic->ctrl();
        $this->parent_gui = $parent_gui;

        if($mode === self::M1) {
            $this->mode_settings = new xaseSettingsM1();
        } elseif($mode === self::M3) {
            $this->mode_settings = new xaseSettingsM3();
        }
        parent::__construct();

        $this->initForm();
    }

    public function initForm() {
        $this->setTarget('_top');
        $this->setFormAction($this->ctrl->getFormAction($this->parent_gui));
        $this->setTitle($this->pl->txt('general_settings'));

        $ti = new ilTextInputGUI($this->pl->txt('title'), 'title');
        $ti->setRequired(true);
        $this->addItem($ti);

        $ta = new ilTextAreaInputGUI($this->pl->txt('description'), 'desc');
        $ta->setRows(10);
        $this->addItem($ta);

        $this->availabilityForm();

        $this->chooseModeForm();

        if($this->mode_settings === self::M1) {
            $this->initM1Form();

        } elseif($this->mode_settings === self::M2) {
            $this->initM3Form();
        }

        $this->addCommandButton(ilObjAssistedExerciseGUI::CMD_UPDATE, $this->pl->txt('save'));
        $this->addCommandButton(ilObjAssistedExerciseGUI::CMD_STANDARD, $this->pl->txt("cancel"));

        $this->setFormAction($this->ctrl->getFormAction($this));
    }

    protected function availabilityForm() {

        $section = new ilFormSectionHeaderGUI();
        $section->setTitle($this->pl->txt('availability'));
        $this->addItem($section);

        $online = new ilCheckboxInputGUI($this->pl->txt('online'), 'online');
        $online->setChecked($this->object->getisOnline());
        $online->setInfo($this->pl->txt('assisted_exercise_activation_online_info'));
        $this->addItem($online);

        $time_limited_checkbox = new ilCheckboxInputGUI($this->pl->txt('time_limited'), 'time_limited');
        $time_limited_checkbox->setChecked($this->object->getisTimeLimited());

        $this->tpl->addJavaScript('./Services/Form/js/date_duration.js');
        include_once "Services/Form/classes/class.ilDateDurationInputGUI.php";
        $dur = new ilDateDurationInputGUI($this->pl->txt("time_period"), "time_period");
        $dur->setShowTime(true);
        $date = $this->object->getStartDate();
        $dur->setStart(new ilDateTime($date ? $date : time(), IL_CAL_UNIX));
        $dur->setStartText($this->pl->txt('start_time'));
        $date = $this->object->getEndDate();
        $dur->setEnd(new ilDateTime($date ? $date : time(), IL_CAL_UNIX));
        $dur->setEndText($this->pl->txt('finish_time'));
        $time_limited_checkbox->addSubItem($dur);

        $always_visible = new ilCheckboxInputGUI($this->pl->txt('always_visible'), 'always_visible');
        $always_visible->setInfo($this->pl->txt('always_visibility_info'));
        $always_visible->setChecked($this->object->getAlwaysVisible());
        $time_limited_checkbox->addSubItem($always_visible);

        $this->addItem($time_limited_checkbox);
    }

    protected function chooseModeForm() {

        $section = new ilFormSectionHeaderGUI();
        $section->setTitle($this->pl->txt('mode_settings'));
        $this->addItem($section);

        $mode = new ilRadioGroupInputGUI($this->pl->txt('mode'), 'mode');
        $mode->setRequired(true);
        $mode->setValue(1);

        $m1 = new ilRadioOption($this->pl->txt('completely_teacher_controlled'), self::M1);
        $m1->setInfo($this->pl->txt('m1_info'));

        //if($mode->getValue() === self::M1) {
            $this->initM1Form($m1);
        //}

        $mode->addOption($m1);

        $m2 = new ilRadioOption($this->pl->txt('student_creates_question'), self::M2);
        $m2->setInfo($this->pl->txt('m2_info'));
        $mode->addOption($m2);

        $m3 = new ilRadioOption($this->pl->txt('partially_teacher_controlled'), self::M3);
        $m3->setInfo($this->pl->txt('m3_info'));

        $this->initM3Form($m3);

        $mode->addOption($m3);

        $this->addItem($mode);
    }

    protected function initM1Form(ilRadioOption $radioOption) {
        $this->initRateAnswerForm($radioOption);

        $cb_sample_solution_visible = new ilCheckboxInputGUI($this->pl->txt('sample_solution_visible'), "sample_solution_visible");
        $cb_sample_solution_visible->setValue("1");
        $cb_sample_solution_visible->setChecked(true);

        $sample_solution_rg = new ilRadioGroupInputGUI('', 'solution_visible_if');
        $sample_solution_rg->setValue(1);

        $rop_after_exercise = new ilRadioOption($this->pl->txt('after_exercise_completion'), 1);
        $rop_after_exercise->setInfo($this->pl->txt('exercise_finished_questions_answered'));
        $sample_solution_rg->addOption($rop_after_exercise);

        $rop_start_date = new ilRadioOption($this->pl->txt('after_definied_date'), 2);
        $sample_solution_rg->addOption($rop_start_date);

        $dt_prop = new ilDateTimeInputGUI("", "start_date");
        $dt_prop->setDate(new ilDateTime(time(),IL_CAL_UNIX));
        $dt_prop->setShowTime(true);
        $rop_start_date->addSubItem($dt_prop);

        $cb_sample_solution_visible->addSubItem($sample_solution_rg);
        $radioOption->addSubItem($cb_sample_solution_visible);
    }

    protected function initM3Form(ilRadioOption $radioOption) {
        $dt_votings_after = new ilDateTimeInputGUI($this->pl->txt('votings_after'), "votings_after");
        $dt_votings_after->setDate(new ilDateTime(time(),IL_CAL_UNIX));
        $dt_votings_after->setShowTime(true);
        $radioOption->addSubItem($dt_votings_after);

        $this->initRateAnswerForm($radioOption, true);
        $this->initSolutionVisibleForm($radioOption, true);
    }

    protected function initRateAnswerForm(ilRadioOption $radioOption, $is_mode_3 = false) {
        /*
         *  the mode number is used to set different values for the a_postvar argument
         *   this makes sure that there are different a_postvar values for the different modes and that the rate answer form is displayed properly
         */
        $mode_number = $is_mode_3 ? 3 : 2;
        $cb_rate_answers2 = new ilCheckboxInputGUI($this->pl->txt('rate_student_answers'), "rate_answers" . $mode_number);
        $cb_rate_answers2->setValue("1");
        $cb_rate_answers2->setChecked(true);
        $radioOption->addSubItem($cb_rate_answers2);

        $dt_prop2 = new ilDateTimeInputGUI($this->pl->txt('disposals_until'), "disposals_until" . $mode_number);
        $dt_prop2->setDate(new ilDateTime(time(),IL_CAL_UNIX));
        $dt_prop2->setShowTime(true);
        $cb_rate_answers2->addSubItem($dt_prop2);

        if($is_mode_3) {
            $cb_additional_points_for_voting = new ilCheckboxInputGUI($this->pl->txt('cb_additional_points_for_voting'), "additional_points_for_voting");
            $cb_additional_points_for_voting->setValue("1");
            $cb_additional_points_for_voting->setChecked(true);
            $cb_rate_answers2->addSubItem($cb_additional_points_for_voting);

            $ci_percentage = new ilCustomInputGUI($this->pl->txt('number_of_percentage'), 'number_of_percentage');
            $ci_percentage->setRequired(true);

            $tpl = new ilTemplate('tpl.m3_settings_percentage.html',true,true, 'Customizing/global/plugins/Services/Repository/RepositoryObject/AssistedExercise');
            $tpl->setVariable('SIZE',2);
            $tpl->setVariable('MAXLENGTH',2);
            $tpl->setVariable('POST_VAR','number_of_percentage');
            //TODO set Value from M3 Settings Object (e.q. $xaseSettingsM3->getPercentage())
            $tpl->setVariable('PROPERTY_VALUE', '10');

            $ci_percentage->setHTML($tpl->get());
            $cb_additional_points_for_voting->addSubItem($ci_percentage);
        }
    }

    protected function initSolutionVisibleForm(ilRadioOption $radioOption, $is_mode_3 = false) {
        $cb_sample_solution_visible = new ilCheckboxInputGUI($this->pl->txt('sample_solution_visible'), "sample_solution_visible");
        $cb_sample_solution_visible->setValue("1");
        $cb_sample_solution_visible->setChecked(true);

        if(!$is_mode_3) {
            $sample_solution_rg = new ilRadioGroupInputGUI('', 'solution_visible_if');
            $sample_solution_rg->setValue(1);

            $rop_after_exercise = new ilRadioOption($this->pl->txt('after_exercise_completion'), 1);
            $rop_after_exercise->setInfo($this->pl->txt('exercise_finished_questions_answered'));
            $sample_solution_rg->addOption($rop_after_exercise);

            $rop_start_date = new ilRadioOption($this->pl->txt('after_definied_date'), 2);
            $sample_solution_rg->addOption($rop_start_date);

            $dt_prop = new ilDateTimeInputGUI("", "start_date");
            $dt_prop->setDate(new ilDateTime(time(),IL_CAL_UNIX));
            $dt_prop->setShowTime(true);
            $rop_start_date->addSubItem($dt_prop);
        }
        $radioOption->addSubItem($cb_sample_solution_visible);
    }

    public function fillForm() {
        $values['title'] = $this->parent_gui->object->getTitle();
        $values['desc'] = $this->parent_gui->object->getDescription();

        $this->setValuesByArray($values);
    }

    public function fillObject() {
        if (! $this->checkInput()) {
            return false;
        }
        $this->parent_gui->object->setTitle($this->getInput('title'));
        $this->parent_gui->object->setDescription($this->getInput('desc'));

        return true;
    }

    /**
     * @return bool|string
     */
    public function updateObject() {
        if (! $this->fillObject()) {
            return false;
        }
        //$this->updateObjectData();
        //$this->object->update();

        return true;
    }

}