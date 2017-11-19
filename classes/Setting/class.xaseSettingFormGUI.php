<?php

/**
 * Class xaseSettingFormGUI
 *
 * @author  Benjamin Seglias <bs@studer-raimann.ch>
 * @author  Martin Studer <ms@studer-raimann.ch>
 */

require_once('./Services/Form/classes/class.ilPropertyFormGUI.php');

class xaseSettingFormGUI extends ilPropertyFormGUI {

	/**
	 * @var ilObjAssistedExerciseFacade
	 */
	protected $obj_facade;
	/**
	 * @var ilObjAssistedExerciseGUI
	 */
	protected $parent_gui;

	/**
	 * xaseSettingFormGUI constructor.
	 *
	 * @param $parent_gui
	 * @param ilObjAssistedExercise $ilObjAssistedExercise
	 */

	public function __construct($parent_gui) {

		$this->obj_facade = ilObjAssistedExerciseFacade::getInstance($_GET['ref_id']);
		$this->parent_gui = $parent_gui;

		parent::__construct();

		$this->initForm();
	}


	public function initForm() {
		$this->setTarget('_top');
		$this->setFormAction($this->obj_facade->getCtrl()->getFormAction($this->parent_gui));
		$this->setTitle($this->obj_facade->getLanguageValue('general_settings'));

		$item = new ilTextInputGUI($this->obj_facade->getLanguageValue('title'), 'title');
		$item->setRequired(true);
		$this->addItem($item);

		$item = new ilTextAreaInputGUI($this->obj_facade->getLanguageValue('description'), 'desc');
		$item->setRows(10);
		$this->addItem($item);

		$item = new ilFormSectionHeaderGUI();
		$item->setTitle($this->obj_facade->getLanguageValue('availability'));
		$this->addItem($item);

		$item = new ilCheckboxInputGUI($this->obj_facade->getLanguageValue('online'), 'online');
		$item->setValue("1");
		$item->setInfo($this->obj_facade->getLanguageValue('assisted_exercise_activation_online_info'));
		$this->addItem($item);

		$item = new ilCheckboxInputGUI($this->obj_facade->getLanguageValue('time_limited'), 'time_limited');

			$this->obj_facade->getTpl()->addJavaScript('./Services/Form/js/date_duration.js');
			include_once "Services/Form/classes/class.ilDateDurationInputGUI.php";

			$sub_item = new ilDateDurationInputGUI($this->obj_facade->getLanguageValue("time_period"), "time_period");
			$sub_item->setShowTime(true);
			$sub_item->setStartText($this->obj_facade->getLanguageValue('start_time'));
			$sub_item->setEndText($this->obj_facade->getLanguageValue('finish_time'));
			$item->addSubItem($sub_item);

		/*	$sub_item = new ilCheckboxInputGUI($this->obj_facade->getLanguageValue('always_visible'), 'always_visible');
			$sub_item->setValue("1");
			$sub_item->setInfo($this->obj_facade->getLanguageValue('always_visibility_info'));
			$item->addSubItem($sub_item);*/

		$this->addItem($item);


		$item = new ilFormSectionHeaderGUI();
		$item->setTitle($this->obj_facade->getLanguageValue('mode_settings'));
		$this->addItem($item);


		$item = new ilRadioGroupInputGUI($this->obj_facade->getLanguageValue('mode'), 'mode');
		$item->setRequired(true);
		$item->setValue(1);

			$radio_option = new ilRadioOption($this->obj_facade->getLanguageValue('completely_teacher_controlled'), xaseSetting::MODUS1);
			$radio_option->setInfo($this->obj_facade->getLanguageValue('m1_info'));
				$this->initRateAnswerForm($radio_option);
				$this->initSolutionVisibleForm($radio_option);

				$sub_item = new ilCheckboxInputGUI($this->obj_facade->getLanguageValue('enable_student_voting'), "voting_enabled");
				$sub_item->setValue("1");
				$radio_option->addSubItem($sub_item);

		$item->addOption($radio_option);


			$radio_option = new ilRadioOption($this->obj_facade->getLanguageValue('student_creates_question'), xaseSetting::MODUS2);
			$radio_option->setInfo($this->obj_facade->getLanguageValue('m2_info'));
		$item->addOption($radio_option);

		$this->addItem($item);



		$this->addCommandButton(ilObjAssistedExerciseGUI::CMD_UPDATE, $this->obj_facade->getLanguageValue('save'));
		$this->addCommandButton(ilObjAssistedExerciseGUI::CMD_STANDARD, $this->obj_facade->getLanguageValue("cancel"));
	}


	protected function initRateAnswerForm(ilRadioOption $radioOption) {
		$item = new ilCheckboxInputGUI($this->obj_facade->getLanguageValue('rate_student_answers'), "rate_answers");
		$item->setValue("1");
		//$item->setChecked(true);
		$radioOption->addSubItem($item);

			$sub_item = new ilDateTimeInputGUI($this->obj_facade->getLanguageValue('disposals_until'), "disposals_until");
			$sub_item->setDate(new ilDateTime(time(), IL_CAL_UNIX));
			$sub_item->setShowTime(true);
			$item->addSubItem($sub_item);

			$sub_item = new ilCheckboxInputGUI($this->obj_facade->getLanguageValue('cb_additional_points_for_voting'), "voting_points_enabled");
			$sub_item->setValue("1");
			//$sub_item->setChecked(true);
			$item->addSubItem($sub_item);

				$sub_sub_item = new ilNumberInputGUI($this->obj_facade->getLanguageValue('number_of_percentage'), 'voting_points_percentage');
				$sub_sub_item->setRequired(true);
				$sub_item->addSubItem($sub_sub_item);
	}


	protected function initSolutionVisibleForm(ilRadioOption $radioOption) {
		/*
		 *  the mode number is used to set different values for the a_postvar argument
		 *   this makes sure that there are different a_postvar values for the different modes and that the rate answer form is displayed properly
		 */
		$item = new ilCheckboxInputGUI($this->obj_facade->getLanguageValue('sample_solution_visible'), "sample_solution_visible");
		$item->setValue("1");

		$sub_item = new ilRadioGroupInputGUI('', 'solution_visible_if');

			// $sample_solution_rg->setValue($this->mode_settings->getVisibleIfExerciseFinished() ? 1 : 2);
			$ropt = new ilRadioOption($this->obj_facade->getLanguageValue('after_exercise_completion'), 1);
			$ropt->setInfo($this->obj_facade->getLanguageValue('exercise_finished_questions_answered'));
			$sub_item->addOption($ropt);

			$ropt = new ilRadioOption($this->obj_facade->getLanguageValue('after_definied_date'), 2);
			$sub_item->addOption($ropt);

				$sub_sub_item = new ilDateTimeInputGUI("", "solution_start_date");
				$sub_sub_item->setDate(new ilDateTime(time(), IL_CAL_UNIX));
				$sub_sub_item->setShowTime(true);
				$ropt->addSubItem($sub_sub_item);

		$item->addSubItem($sub_item);

		$radioOption->addSubItem($item);
	}


	public function fillForm() {
		$values['title'] = $this->obj_facade->getIlObjTitle();
		$values['desc'] = $this->obj_facade->getIlObjDescription();
		$values['online'] = $this->obj_facade->getSetting()->getIsOnline();
		$values['time_limited'] = $this->obj_facade->getSetting()->getIsTimeLimited();
		$values['time_period']['start'] =  $this->obj_facade->getSetting()->getStartDate();
		$values['time_period']['end'] = $this->obj_facade->getSetting()->getEndDate();
		$values['always_visible'] = $this->obj_facade->getSetting()->getAlwaysVisible();
		$values['mode'] = $this->obj_facade->getSetting()->getModus();
		$values['rate_answers'] = $this->obj_facade->getSetting()->getRateAnswers();
		$values['disposals_until'] = $this->obj_facade->getSetting()->getDisposalDate();
		$values['voting_points_enabled'] = $this->obj_facade->getSetting()->getVotingPointsEnabled();
		$values['voting_points_percentage'] = $this->obj_facade->getSetting()->getVotingPointsPercentage();
		$values['sample_solution_visible'] = $this->obj_facade->getSetting()->getSampleSolutionVisible();
		$values['solution_visible_if'] = $this->obj_facade->getSetting()->getVisibleIfExerciseFinished();


		$values['solution_start_date'] = $this->obj_facade->getSetting()->getSolutionVisibleDate();
		$values['voting_enabled'] = $this->obj_facade->getSetting()->getVotingEnabled();

		$this->setValuesByArray($values);
	}


	public function fillObject() {

		$this->obj_facade->setIlObjTitle($this->getInput('title'));
		$this->obj_facade->setIlObjDescription($this->getInput('desc'));
		$this->obj_facade->getSetting()->setIsOnline((int)$this->getInput('online'));
		$this->obj_facade->getSetting()->setIsTimeLimited($this->getInput('time_limited'));

		/**
		 * @var array $time_period
		 */
		$time_period = $this->getInput('time_period');
		$this->obj_facade->getSetting()->setStartDate($time_period['start']);
		$this->obj_facade->getSetting()->setEndDate($time_period['end']);

		$this->obj_facade->getSetting()->setAlwaysVisible((int)$this->getInput('always_visible'));
		$this->obj_facade->getSetting()->setModus((int)$this->getInput('mode'));
		$this->obj_facade->getSetting()->setRateAnswers((int)$this->getInput('rate_answers'));
		$this->obj_facade->getSetting()->setDisposalDate($this->getInput('disposals_until'));
		$this->obj_facade->getSetting()->setVotingPointsEnabled((int)$this->getInput('voting_points_enabled'));
		$this->obj_facade->getSetting()->setVotingPointsPercentage((int)$this->getInput('voting_points_percentage'));

		$this->obj_facade->getSetting()->setSampleSolutionVisible((int)$this->getInput('sample_solution_visible'));
		$this->obj_facade->getSetting()->setVisibleIfExerciseFinished((int)$this->getInput('solution_visible_if'));

		$this->obj_facade->getSetting()->setSolutionVisibleDate($this->getInput('solution_start_date'));
		$this->obj_facade->getSetting()->setVotingEnabled((int)$this->getInput('voting_enabled'));

		return true;
	}



/*
	protected function resetVotingSpecificStatusToAnswerd() {
		$all_answers = xaseAnswer::get();
		foreach ($all_answers as $answer) {
			if ($answer->getAnswerStatus() == xaseAnswer::ANSWER_STATUS_CAN_BE_VOTED) {
				$answer->setAnswerStatus(xaseAnswer::ANSWER_STATUS_ANSWERED);
			}
		}
	}*/


	/**
	 * @return bool|string
	 */
	public function update() {
		$this->fillObject();
		$this->obj_facade->store();

		return true;
	}
}