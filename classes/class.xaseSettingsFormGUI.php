<?php

/**
 * Class xaseSettingsFormGUI
 *
 * @author  Benjamin Seglias <bs@studer-raimann.ch>
 */

require_once('./Services/Form/classes/class.ilPropertyFormGUI.php');
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/AssistedExercise/classes/ActiveRecords/class.xaseSettingsM1.php');
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/AssistedExercise/classes/ActiveRecords/class.xaseSettingsM2.php');
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/AssistedExercise/classes/ActiveRecords/class.xaseSettingsM3.php');

class xaseSettingsFormGUI extends ilPropertyFormGUI {

	const M1 = "1";
	const M2 = "2";
	const M3 = "3";
	/**
	 * @var  xaseSettings
	 */
	protected $object;
	/**
	 * @var ilObjAssistedExerciseGUI
	 */
	protected $parent_gui;
	/**
	 * @var ilObjAssistedExercise
	 */
	protected $assisted_exercise;
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
	 * @var xaseSettingsM1|xaseSettingsM2|xaseSettingsM3
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
	 * xaseSettingsFormGUI constructor.
	 *
	 * @param              $parent_gui
	 * @param xaseSettings $xaseSettings
	 * @param              $mode
	 */

	//TODO decide with $mode which xaseSettingsMN Class should be additionally used to xaseSettings
	public function __construct($parent_gui, ilObjAssistedExercise $ilObjAssistedExercise, xaseSettings $xaseSettings, $mode) {
		global $DIC;

		$this->dic = $DIC;
		$this->object = $xaseSettings;
		$this->pl = ilAssistedExercisePlugin::getInstance();
		$this->tpl = $this->dic['tpl'];
		$this->ctrl = $this->dic->ctrl();
		$this->parent_gui = $parent_gui;
		$this->assisted_exercise = $ilObjAssistedExercise;
		$this->mode = $mode;
		$this->mode_settings = $this->getModeSettings($this->mode);
		//stores the default mode settings if the user previously created the object in the repository
		if ($this->is_creation_mode) {
			$this->mode_settings->setSettingsId($this->object->getId());
			$this->mode_settings->setRateAnswers(1);
			$this->mode_settings->setSampleSolutionVisible(1);
			$this->mode_settings->setVisibleIfExerciseFinished(1);
			$this->mode_settings->store();
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

		$this->addCommandButton(ilObjAssistedExerciseGUI::CMD_UPDATE, $this->pl->txt('save'));
		$this->addCommandButton(ilObjAssistedExerciseGUI::CMD_STANDARD, $this->pl->txt("cancel"));
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
		//$date = $this->object->getStartDate();
		if ($this->object->getStartDate()) {
			$dur->setStart(new ilDateTime($this->object->getStartDate(), IL_CAL_DATETIME));
		} else {
			$dur->setStart(new ilDateTime(time(), IL_CAL_UNIX));
		}
		//$dur->setStart(new ilDateTime($date ? $date : time(), IL_CAL_UNIX));
		$dur->setStartText($this->pl->txt('start_time'));
		if ($this->object->getEndDate()) {
			$dur->setEnd(new ilDateTime($this->object->getStartDate(), IL_CAL_DATETIME));
		} else {
			$dur->setEnd(new ilDateTime(time(), IL_CAL_UNIX));
		}
		/*        $date = $this->object->getEndDate();
				$dur->setEnd(new ilDateTime($date ? $date : time(), IL_CAL_UNIX));*/
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
		$this->initVotingsForm($m2);
		$mode->addOption($m2);

		$m3 = new ilRadioOption($this->pl->txt('partially_teacher_controlled'), self::M3);
		$m3->setInfo($this->pl->txt('m3_info'));

		$this->initM3Form($m3);

		$mode->addOption($m3);

		$this->addItem($mode);
	}


	protected function initM1Form(ilRadioOption $radioOption) {
		$this->initRateAnswerForm($radioOption);
		$this->initSolutionVisibleForm($radioOption);
	}


	protected function initVotingsForm(ilRadioOption $radioOption, $is_mode_3 = false) {
		$mode_number = $is_mode_3 ? 3 : 2;
		$dt_votings_after = new ilDateTimeInputGUI($this->pl->txt('votings_after'), "votings_after" . $mode_number);
		$dt_votings_after->setDate(new ilDateTime(time(), IL_CAL_UNIX));
		$dt_votings_after->setShowTime(true);
		$radioOption->addSubItem($dt_votings_after);
	}


	protected function initM3Form(ilRadioOption $radioOption) {
		$this->initVotingsForm($radioOption, true);
		$this->initRateAnswerForm($radioOption, true);
		$this->initSolutionVisibleForm($radioOption, true);
	}


	protected function initRateAnswerForm(ilRadioOption $radioOption, $is_mode_3 = false) {
		/*
		 *  the mode number is used to set different values for the a_postvar argument
		 *   this makes sure that there are different a_postvar values for the different modes and that the rate answer form is displayed properly
		 */
		$mode_number = $is_mode_3 ? 3 : 1;
		$cb_rate_answers2 = new ilCheckboxInputGUI($this->pl->txt('rate_student_answers'), "rate_answers" . $mode_number);
		$cb_rate_answers2->setValue("1");
		$cb_rate_answers2->setChecked(true);
		$radioOption->addSubItem($cb_rate_answers2);

		$dt_prop2 = new ilDateTimeInputGUI($this->pl->txt('disposals_until'), "disposals_until" . $mode_number);
		$dt_prop2->setDate(new ilDateTime(time(), IL_CAL_UNIX));
		$dt_prop2->setShowTime(true);
		$cb_rate_answers2->addSubItem($dt_prop2);

		if ($is_mode_3) {
			$cb_additional_points_for_voting = new ilCheckboxInputGUI($this->pl->txt('cb_additional_points_for_voting'), "additional_points_for_voting");
			$cb_additional_points_for_voting->setValue("1");
			$cb_additional_points_for_voting->setChecked(true);
			$cb_rate_answers2->addSubItem($cb_additional_points_for_voting);

			$ci_percentage = new ilCustomInputGUI($this->pl->txt('number_of_percentage'), 'number_of_percentage');
			$ci_percentage->setRequired(true);

			$tpl = new ilTemplate('tpl.small_input.html', true, true, 'Customizing/global/plugins/Services/Repository/RepositoryObject/AssistedExercise');
			$tpl->setVariable('SIZE', 2);
			$tpl->setVariable('MAXLENGTH', 2);
			$tpl->setVariable('POST_VAR', 'number_of_percentage');
			if ($this->mode_settings instanceof xaseSettingsM3 && !empty($this->mode_settings)) {
				$tpl->setVariable('PROPERTY_VALUE', $this->mode_settings->getVotingPointsPercentage());
			} else {
				$tpl->setVariable('PROPERTY_VALUE', '10');
			}

			$ci_percentage->setHTML($tpl->get());
			$cb_additional_points_for_voting->addSubItem($ci_percentage);
		}
	}


	protected function initSolutionVisibleForm(ilRadioOption $radioOption, $is_mode_3 = false) {
		/*
		 *  the mode number is used to set different values for the a_postvar argument
		 *   this makes sure that there are different a_postvar values for the different modes and that the rate answer form is displayed properly
		 */
		$mode_number = $is_mode_3 ? 3 : 1;
		$cb_sample_solution_visible = new ilCheckboxInputGUI($this->pl->txt('sample_solution_visible'), "sample_solution_visible" . $mode_number);
		$cb_sample_solution_visible->setValue("1");
		$cb_sample_solution_visible->setChecked(true);

		if (!$is_mode_3) {
			$sample_solution_rg = new ilRadioGroupInputGUI('', 'solution_visible_if');
			// $sample_solution_rg->setValue($this->mode_settings->getVisibleIfExerciseFinished() ? 1 : 2);
			$rop_after_exercise = new ilRadioOption($this->pl->txt('after_exercise_completion'), 1);
			$rop_after_exercise->setInfo($this->pl->txt('exercise_finished_questions_answered'));
			$sample_solution_rg->addOption($rop_after_exercise);

			$rop_start_date = new ilRadioOption($this->pl->txt('after_definied_date'), 2);
			$sample_solution_rg->addOption($rop_start_date);

			$dt_prop = new ilDateTimeInputGUI("", "solution_start_date");
			$dt_prop->setDate(new ilDateTime(time(), IL_CAL_UNIX));
			$dt_prop->setShowTime(true);
			$rop_start_date->addSubItem($dt_prop);

			$cb_sample_solution_visible->addSubItem($sample_solution_rg);
		}
		$radioOption->addSubItem($cb_sample_solution_visible);
	}


	public function fillForm() {
		$values['title'] = $this->assisted_exercise->getTitle();
		$values['desc'] = $this->assisted_exercise->getDescription();
		$values['online'] = $this->object->getisOnline();
		$values['time_limited'] = $this->object->getisTimeLimited();
		$values['time_period']['start'] = $this->object->getStartDate();
		$values['time_period']['end'] = $this->object->getEndDate();
		$values['always_visible'] = $this->object->getAlwaysVisible();
		$values['mode'] = $this->object->getModus();

		if ((int)$this->object->getModus() === 1 || (int)$this->object->getModus() === 3) {
			foreach ($this->fillM1AndM3Form((int)$this->object->getModus()) as $key => $value) {
				$values[$key] = $value;
			}
			if ((int)$this->object->getModus() === 1) {
				foreach ($this->fillM1Form() as $key => $value) {
					$values[$key] = $value;
				}
			} elseif ((int)$this->object->getModus() === 3) {
				foreach ($this->fillM3Form() as $key => $value) {
					$values[$key] = $value;
				}
			} else {
				foreach ($this->fillM2Form() as $key => $value) {
					$values[$key] = $value;
				}
			}
		}

		/*        if((int) $this->object->getModus() === 1) {
					foreach($this->fillM1Form() as $key => $value) {
						$values[$key] = $value;
					}
				}if((int) $this->object->getModus() === 3) {
					foreach($this->fillM3Form() as $key => $value) {
						$values[$key] = $value;
					}
				}*/
		$this->setValuesByArray($values);
	}


	public function fillM1AndM3Form($mode) {
		$values['rate_answers' . $mode] = $this->mode_settings->getRateAnswers();
		$values['disposals_until' . $mode] = $this->mode_settings->getDisposalDate();
		$values['sample_solution_visible' . $mode] = $this->mode_settings->getSampleSolutionVisible();

		return $values;
	}


	public function fillM1Form() {
		/*        $values['rate_answers1'] = $this->mode_settings->getRateAnswers();
				$values['disposals_until1'] = $this->mode_settings->getDisposalDate();
				$values['sample_solution_visible1'] = $this->mode_settings->getSampleSolutionVisible();*/
		if ($this->mode_settings->getVisibleIfExerciseFinished()) {
			$values['solution_visible_if'] = 1;
		} else {
			$values['solution_visible_if'] = 2;
			$values['solution_start_date'] = $this->mode_settings->getSolutionVisibleDate();
		}

		return $values;
	}


	public function fillM2Form() {
		$values['votings_after2'] = $this->mode_settings->getStartVotingDate();

		return $values;
	}


	public function fillM3Form() {
		/*        $values['rate_answers3'] = $this->mode_settings->getRateAnswers();
				$values['disposals_until3'] = $this->mode_settings->getDisposalDate();
				$values['sample_solution_visible3'] = $this->mode_settings->getSampleSolutionVisible();*/
		$values['votings_after3'] = $this->mode_settings->getStartVotingDate();
		$values['additional_points_for_voting'] = $this->mode_settings->getVotingPoints();
		$values['number_of_percentage'] = $this->mode_settings->getVotingPointsPercentage();

		return $values;
	}


	public function fillObject() {
		if (!$this->checkInput()) {
			return false;
		}
		if ($this->getInput('rate_answers' . $this->object->getModus()) && empty($this->getInput('disposals_until' . (int)$this->object->getModus()))) {
			ilUtil::sendFailure($this->pl->txt('msg_input_please_chose_disposal_date'));
			return false;
		}
		$this->assisted_exercise->setTitle($this->getInput('title'));
		$this->assisted_exercise->setDescription($this->getInput('desc'));
		$this->object->setAssistedExerciseObjectId($this->assisted_exercise->getId());
		$this->object->setIsOnline($this->getInput('online'));
		$this->object->setIsTimeLimited($this->getInput('time_limited'));
		/**
		 * @var array $time_period
		 */
		$time_period = $this->getInput('time_period');
		foreach ($time_period as $key => $value) {

			$date_time = new ilDateTime($value, IL_CAL_DATETIME);
			/* $timestamp = $date_time->get(IL_CAL_UNIX);*/
			$time_period[$key] = $date_time;
		}

		$this->object->setStartDate($time_period['start']);
		$this->object->setEndDate($time_period['end']);
		$this->object->setAlwaysVisible($this->getInput('always_visible'));

		if ($this->getInput('mode') === '1' || $this->getInput('mode') === '3') {
			$this->fillM1AndM3Objects((int)$this->getInput('mode'));
		}
		if ($this->getInput('mode') === '1') {
			$this->object->setModus(1);
			$this->fillObjectM1();
		} elseif ($this->getInput('mode') === self::M2) {
			$this->object->setModus(2);
			$this->fillObjectM2();
		} elseif ($this->getInput('mode') === '3') {
			$this->object->setModus(3);
			$this->fillObjectM3();
		} else {
			ilUtil::sendFailure($this->pl->txt('please_choose_mode'));
		}

		return true;
	}


	public function fillObjectM1() {
		/*        $this->object->setModus(1);
				$this->mode_settings = $this->getModeSettings($this->object->getModus());
				$this->mode_settings->setSettingsId($this->object->getId());
				$this->mode_settings->setRateAnswers($this->getInput('rate_answers' . $this->object->getModus()));
				$disposal_until = new ilDateTime($this->getInput('disposals_until' . $this->object->getModus()), IL_CAL_DATETIME);
				//$timestamp_disposal_until = $disposal_until->get(IL_CAL_UNIX);
				$this->mode_settings->setDisposalDate($disposal_until);
				$this->mode_settings->setSampleSolutionVisible($this->getInput('sample_solution_visible'. $this->object->getModus()) === '1' ? 1 : 0);*/

		// if the radio group option "After exercise completion" was chosen
		if ($this->getInput('solution_visible_if') === "1") {
			$this->mode_settings->setVisibleIfExerciseFinished(1);
			// in conditions empty string is converted to false
			$this->mode_settings->setSolutionVisibleDate("");
		} else {
			$this->mode_settings->setVisibleIfExerciseFinished(0);
			$sample_solution_visible_date_time = new ilDateTime($this->getInput('solution_start_date'), IL_CAL_DATETIME);
			//$sample_solution_visible_timestamp = $sample_solution_visible_date_time->get(IL_CAL_UNIX);
			$this->mode_settings->setSolutionVisibleDate($sample_solution_visible_date_time);
		}
	}


	public function fillObjectM2() {
		$this->mode_settings->setStartVotingDate(new ilDateTime($this->getInput('votings_after2'), IL_CAL_DATETIME));
	}


	public function fillObjectM3() {
		/*        $this->object->setModus(3);
				$this->mode_settings = $this->getModeSettings($this->object->getModus());
				$this->mode_settings->setSettingsId($this->object->getId());
				$this->mode_settings->setRateAnswers($this->getInput('rate_answers' . $this->object->getModus()));
				$disposal_until = new ilDateTime($this->getInput('disposals_until' . $this->object->getModus()), IL_CAL_DATETIME);
				$this->mode_settings->setDisposalDate($disposal_until);
				$this->mode_settings->setSampleSolutionVisible($this->getInput('sample_solution_visible' . $this->object->getModus()) === '1' ? 1 : 0);*/

		$this->mode_settings->setStartVotingDate(new ilDateTime($this->getInput('votings_after3'), IL_CAL_DATETIME));
		//$additional_points_for_voting = $this->getInput('additional_points_for_voting');
		$this->mode_settings->setVotingPoints($this->getInput('additional_points_for_voting'));
		//$number_of_percentage = $this->getInput('number_of_percentage');
		$this->mode_settings->setVotingPointsPercentage($this->getInput('number_of_percentage'));
	}


	public function fillM1AndM3Objects($modus) {
		$this->object->setModus($modus);
		$this->mode_settings = $this->getModeSettings($this->object->getModus());
		$this->mode_settings->setSettingsId($this->object->getId());
		$this->mode_settings->setRateAnswers($this->getInput('rate_answers' . $this->object->getModus()));
		$disposal_until = new ilDateTime($this->getInput('disposals_until' . $this->object->getModus()), IL_CAL_DATETIME);
		$this->mode_settings->setDisposalDate($disposal_until);
		$this->mode_settings->setSampleSolutionVisible($this->getInput('sample_solution_visible' . $this->object->getModus()) === '1' ? 1 : 0);
	}


	public function deletePreviousModeSettings($chosen_mode) {

		if ($chosen_mode != self::M1 && xaseSettingsM1::where([ 'settings_id' => $this->object->getId() ])->hasSets()) {
			$xaseSettingsM1 = xaseSettingsM1::where([ 'settings_id' => $this->object->getId() ])->first();
			$xaseSettingsM1->delete();
		} elseif ($chosen_mode != self::M3 && xaseSettingsM3::where([ 'settings_id' => $this->object->getId() ])->hasSets()) {
			$xaseSettingsM3 = xaseSettingsM3::where([ 'settings_id' => $this->object->getId() ])->first();
			$xaseSettingsM3->delete();
			if($chosen_mode != self::M2) {
				$this->resetVotingSpecificStatusToAnswerd();
			}
		} elseif ($chosen_mode != self::M2 && xaseSettingsM2::where([ 'settings_id' => $this->object->getId() ])->hasSets()) {
			$xaseSettingsM2 = xaseSettingsM2::where([ 'settings_id' => $this->object->getId() ])->first();
			$xaseSettingsM2->delete();
			if($chosen_mode != self::M3) {
				$this->resetVotingSpecificStatusToAnswerd();
			}
		}

		return;
	}


	protected function resetVotingSpecificStatusToAnswerd() {
		$all_answers = xaseAnswer::get();
		foreach ($all_answers as $answer) {
			if ($answer->getAnswerStatus() == xaseAnswer::ANSWER_STATUS_CAN_BE_VOTED) {
				$answer->setAnswerStatus(xaseAnswer::ANSWER_STATUS_ANSWERED);
			}
		}
	}

	/*protected function has_user_answered_all_items() {
		$item_ids = $this->getItemIds();
		$answers = $this->getAnswersFromUser();
		$answer_item_ids = [];
		foreach($answers as $answer) {
			$answer_item_ids[] = $answer->getItemId();
		}
		if(!empty(array_diff($item_ids, $answer_item_ids))) {
			foreach($answers as $answer) {
				$answer->setAnswerStatus(xaseAnswer::ANSWER_STATUS_ANSWERED);
				$answer->store();
			}
			return false;
		}
		return true;
	}

	protected function getItemIds() {
		$items = xaseItem::where(array('assisted_exercise_id' => $this->assisted_exercise->getId()))->get();
		$item_ids = [];
		foreach($items as $item) {
			$item_ids[] = $item->getId();
		}
		return $item_ids;
	}

	protected function getAnswersFromUser() {
		$item_ids = $this->getItemIds();
		if(empty($item_ids)) {
			return null;
		} else {
			return xaseAnswer::where(array('user_id' => $this->dic->user()->getId(), 'item_id' => $item_ids), array('user_id' => '=', 'item_id' => 'IN'))->get();
		}
	}*/

	/**
	 * @return bool|string
	 */
	public function updateObject() {
		if (!$this->fillObject()) {
			return false;
		}
		$this->deletePreviousModeSettings($this->object->getModus());

		$this->object->store();
		$this->mode_settings->store();

		return true;
	}


	protected function getModeSettings($mode) {
		if ($mode == self::M1) {
			if (xaseSettingsM1::where([ 'settings_id' => $this->object->getId() ])->hasSets()) {
				$this->is_creation_mode = false;

				return xaseSettingsM1::where([ 'settings_id' => $this->object->getId() ])->first();
			} elseif (!xaseSettingsM1::where([ 'settings_id' => $this->object->getId() ])->hasSets()) {
				$this->is_creation_mode = true;

				return new xaseSettingsM1();
			}
		}
		if ($mode == self::M2) {
			if (xaseSettingsM2::where([ 'settings_id' => $this->object->getId() ])->hasSets()) {
				$this->is_creation_mode = false;

				return xaseSettingsM2::where([ 'settings_id' => $this->object->getId() ])->first();
			} elseif (!xaseSettingsM2::where([ 'settings_id' => $this->object->getId() ])->hasSets()) {
				$this->is_creation_mode = true;

				return new xaseSettingsM2();
			}
		}
		if ($mode == self::M3) {
			if (xaseSettingsM3::where([ 'settings_id' => $this->object->getId() ])->hasSets()) {
				$this->is_creation_mode = false;

				return xaseSettingsM3::where([ 'settings_id' => $this->object->getId() ])->first();
			} elseif (!xaseSettingsM3::where([ 'settings_id' => $this->object->getId() ])->hasSets()) {
				$this->is_creation_mode = true;

				return new xaseSettingsM3();
			}
		}
	}
}