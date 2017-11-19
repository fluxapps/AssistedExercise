<?php

/**
 * Class xaseQuestionFormGUI
 *
 * @author  Benjamin Seglias <bs@studer-raimann.ch>
 */

require_once 'class.xaseQuestion.php';

require_once('./Services/UIComponent/Button/classes/class.ilJsLinkButton.php');
require_once('./Services/Form/classes/class.ilPropertyFormGUI.php');
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/AssistedExercise/classes/SampleSolution/class.xaseSampleSolutionGUI.php');
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/AssistedExercise/classes/Hint/class.ilHintInputGUI.php');
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/AssistedExercise/classes/Hint/class.xaseHintLevel.php');

class xaseQuestionFormGUI extends ilPropertyFormGUI {

	/**
	 * @var xaseQuestionGUI
	 */
	protected $parent_gui;
	/**
	 * @var ilObjAssistedExerciseFacade
	 */
	protected $obj_facade;
	/**
	 * @var xaseQuestion
	 */
	protected $obj_question;

	/**
	 * @var xaseHint[]
	 */
	protected $xase_hints = [];
	/**
	 * @var ilHintInputGUI
	 */
	protected $hint_input_gui;




	/**
	 * xaseQuestionFormGUI constructor.
	 *
	 * @param xaseQuestionGUI $parent_gui
	 */
	public function __construct(xaseQuestionGUI $parent_gui) {

		$this->parent_gui = $parent_gui;

		$this->obj_facade = ilObjAssistedExerciseFacade::getInstance($_GET['ref_id']);
		$this->obj_question = xaseQuestion::findOrGetInstance($_GET['question_id']);


		/*$this->xase_sample_solution = $this->getXaseSampleSolution($this->obj_question->getSampleSolutionId());
		$this->xase_point = $this->getXasePoint($this->obj_question->getPointId());*/

		/*$this->xase_settings = $xaseSetting;
		$this->mode = $xaseSetting->getModus();*/
		//$this->xase_settings = xaseSetting::where(['assisted_exercise_object_id' => $this->obj_question->getId()])->first();
		/*if ($this->xase_settings->getModus() == xaseSettingMODUS1) {
			$this->mode_settings = $this->getModusSetting(xaseSettingM1::class);//xaseSettingM1::where(['settings_id' => $this->xase_settings->getId()])->first();
		} elseif ($this->xase_settings->getModus() == xaseSettingMODUS3) {
			$this->mode_settings = $this->getModusSetting(xaseSettingM3::class);//xaseSettingM3::where(['settings_id' => $this->xase_settings->getId()])->first();
		} else {
			$this->mode_settings = $this->getModusSetting(xaseSettingM2::class);
		}*/
		//$this->xase_hints = $this->getHintsByItem($this->obj_question->getId());
		parent::__construct();

		$this->obj_facade->getTpl()->addJavaScript('./Customizing/global/plugins/Services/Repository/RepositoryObject/AssistedExercise/templates/js/hint.js');
		$this->initForm();
	}


	public function initForm() {
		$this->setTarget('_top');
		$this->setTitle($this->obj_facade->getLanguageValue('task_create'));

		$ti = new ilTextInputGUI($this->obj_facade->getLanguageValue('title'), 'title');
		$ti->setRequired(true);
		$this->addItem($ti);

		$ta = new ilTextAreaInputGUI($this->obj_facade->getLanguageValue('task'), 'question_text');
		$ta->setRequired(true);
		$ta->setRows(10);

		if($this->obj_facade->getSetting()->modusOffersHint()) {
			$ta->setInfo($this->obj_facade->getLanguageValue('info_hints'));
		}
		$this->addItem($ta);

		if($this->obj_facade->getSetting()->modusOffersHint()) {
			$this->initAddHintBtn();
		}

		if($this->obj_facade->getSetting()->modusOffersSampleSolutions()) {
			$sol = new ilTextAreaInputGUI($this->obj_facade->getLanguageValue('sample_solution'), 'sample_solution');
			$sol->setRequired(true);
			$sol->setRows(10);
			$this->addItem($sol);
		}


		$max_points = new ilNumberInputGUI($this->obj_facade->getLanguageValue('specify_max_points'), 'max_points');
		$max_points->setRequired(true);
		$max_points->setSize(4);
		$max_points->setMaxLength(4);
		$this->addItem($max_points);

		if($this->obj_facade->getSetting()->modusOffersHint()) {
			$header = new ilFormSectionHeaderGUI();
			$header->setTitle($this->obj_facade->getLanguageValue($this->obj_facade->getLanguageValue('hints')));
			$this->addItem($header);
			$this->initHintForm();
		}


		$this->addCommandButton(xaseQuestionGUI::CMD_UPDATE, $this->obj_facade->getLanguageValue('save'));
		$this->addCommandButton(xaseQuestionGUI::CMD_CANCEL, $this->obj_facade->getLanguageValue("cancel"));

		$this->obj_facade->getCtrl()->setParameter($this->parent_gui, xaseQuestionGUI::ITEM_IDENTIFIER, $_GET[xaseQuestionGUI::ITEM_IDENTIFIER]);
		$this->setFormAction($this->obj_facade->getCtrl()->getFormAction($this->parent_gui));
	}


	public function initAddHintBtn() {
		$tpl = new ilTemplate('tpl.add_hint_button_code.html', true, true, 'Customizing/global/plugins/Services/Repository/RepositoryObject/AssistedExercise');
		$btn_add_hint = ilJsLinkButton::getInstance();
		$btn_add_hint->setCaption($this->obj_facade->getLanguageValue("add_hint_btn_caption"), false);
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
		$btn_remove_hint->setCaption($this->obj_facade->getLanguageValue('text_remove_hint_btn'));
		$btn_remove_hint->setName('text_remove_hint_btn');

		$tpl->setCurrentBlock('CODE');
		$tpl->setVariable('BUTTON', $btn_remove_hint->render());
		$tpl->parseCurrentBlock();
		$custom_input_gui = new ilCustomInputGUI();
		$custom_input_gui->setHtml($tpl->get());
		$this->addItem($custom_input_gui);
	}


	public function initHintForm() {
		$this->hint_input_gui = new ilHintInputGUI($this->obj_facade->getLanguageValue('hints'), "");
		$this->addItem($this->hint_input_gui);

		return $this;
	}


	/*
	protected function getXaseSampleSolution($sample_solution_id) {
		$xaseSampleSolution = xaseSampleSolution::where(array( 'id' => $sample_solution_id ))->first();
		if (empty($xaseSampleSolution)) {
			$xaseSampleSolution = new xaseSampleSolution();
		}

		return $xaseSampleSolution;
	}*/

/*
	protected function getXasePoint($point_id) {
		$xasePoint = xasePoint::where(array( 'id' => $point_id ))->first();
		if (empty($xasePoint)) {
			$xasePoint = new xasePoint();
		}

		return $xasePoint;
	}*/

/*
	protected function getModusSetting($modus_settings) {
		$xaseModus = $modus_settings::where(array( 'settings_id' => $this->xase_settings->getId() ))->first();
		if (empty($xaseModus)) {
			if ($this->xase_settings->getModus() == xaseSettingMODUS1) {
				$xaseModus = new xaseSettingM1();
			} elseif ($this->xase_settings->getModus() == xaseSettingMODUS2) {
				$xaseModus = new xaseSettingM2();
			} else {
				$xaseModus = new xaseSettingM3();
			}
		}

		return $xaseModus;
	}*/



	public function fillForm() {
		$values['title'] = $this->obj_question->getTitle();
		$values['task'] = $this->obj_question->getQuestiontext();
		$values["sample_solution"] = $this->obj_question->getSampleSolution();
		$values['max_points'] = $this->obj_question->getMaxPoints();


		$hints = array();
		if(count($this->obj_question->getHints()) > 0) {
			foreach ($this->obj_question->getHints() as $hint) {
				$hint_array['id'] = $hint->getId();
				$hint_array['question_id'] = $hint->getQuestionId();
				$hint_array['hint_number'] = $hint->getHintNumber();
				$hint_array['is_template'] = $hint->getisTemplate();
				$hint_array['label'] = $hint->getLabel();

				$hints[] = $hint_array;

				$levels_array = $this->getLevelsByHintId($hint_array['id']);

				/**
				 * @var $level xaseHintLevel
				 */
				foreach ($levels_array as $level) {
					$level_array['id'] = $level->getId();
					$level_array['hint_id'] = $level->getHintId();
					$level_array['point_id'] = $level->getPointId();
					$level_array['hint_level'] = $level->getHintLevel();
					$level_array['hint'] = $level->getHint();

					                    $json_encoded_level = json_encode($level_array);
										$json_levels[] = $json_encoded_level;

					$levels[] = $level_array;

					$point = xasePoint::where(array( 'id' => $level->getPointId() ))->first();

					if (!empty($point)) {
						/**
						 * @var $point xasePoint
						 */
						$point_array['id'] = $point->getId();
						$point_array['minus_points'] = $point->getMinusPoints();

						//$json_encoded_point = json_encode($point_array);
						$points[] = $point_array;
					}
				}


				$this->hint_input_gui->setExistingLevelData($levels);
				$this->hint_input_gui->setMinusPoints($points);
			}
			$this->hint_input_gui->setExistingHintData($hints);
		}

		$this->setValuesByArray($values);
	}


	public function fillObject() {

		$this->obj_question->setCreatedBy($this->obj_facade->getUser()->getId());
		$this->obj_question->setAssistedExerciseId($this->obj_facade->getIlObjObId());
		$this->obj_question->setTitle($this->getInput('title'));
		$this->obj_question->setQuestiontext($this->getInput('question_text'));
		$this->obj_question->setSampleSolution($this->getInput('sample_solution'));
		$this->obj_question->setMaxPoints($this->getInput('max_points'));

		$arr_hints = array();
		if(is_array($this->getInput('hint'))) {
			foreach($this->getInput('hint') as $key => $arr_hint_post) {

				if($arr_hint_post['is_template'] == 0) {
					continue;
				}

				/**
				 * @var xaseHint $hint
				 */
				$hint = xaseHint::findOrGetInstance($arr_hint_post['hint_id']);
				$hint->setIsTemplate($arr_hint_post['is_template']);
				$hint->setLabel($arr_hint_post['label']);
				$hint->setHintNumber($key);


				$arr_hint_levels = array();



				/**
				 * @var xaseHintLevel $hint_level
				 */
				if($arr_hint_post['lvl_1_hint']) {
					$hint_level = new xaseHintLevel();
					$hint_level->setHintText($arr_hint_post['lvl_1_hint']);
					$hint_level->setMinusPoints($arr_hint_post['lvl_1_minus_points']);
					$hint_level->setHintLevel(1);
					$arr_hint_levels[] = $hint_level;
				}

				/**
				 * @var xaseHintLevel $hint_level
				 */
				if($arr_hint_post['lvl_2_hint']) {
					$hint_level =  new xaseHintLevel();
					$hint_level->setHintText($arr_hint_post['lvl_2_hint']);
					$hint_level->setMinusPoints($arr_hint_post['lvl_2_minus_points']);
					$hint_level->setHintLevel(2);
					$arr_hint_levels[] = $hint_level;
				}


				$hint->setHintLevels($arr_hint_levels);

				$arr_hints[] = $hint;
			}

		}
		$this->obj_question->setHints($arr_hints);


		return true;
	}


	protected function getHintsByItem($question_id) {
		return xaseHint::where(array( 'question_id' => $question_id ))->get();
	}


	protected function getLevelsByHintId($hint_id) {
		return xaseHintLevel::where(array( 'hint_id' => $hint_id ))->get();
	}


	protected function getMaxHintNumber($question_id) {
		$this->dic->database()->query("SELECT max(hint_number) FROM xase_hint where question_id = " . $this->dic->database()
				->quote($question_id, "integer"));
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
		$task = $this->obj_question->getQuestiontext();
		/*        preg_match_all('(\d+)', $task, $matches);
				$matches = array_unique($matches);
				for ($i = 0; $i < count($matches); $i++) {

				}*/

		$max_hint_number = $this->getMaxHintNumber($this->obj_question->getId());

		if (is_array($_POST['hint'])) {
			foreach ($_POST['hint'] as $id => $data) {
				if (!empty($this->xase_hints)) {
					foreach ($this->xase_hints as $xase_hint) {
						if ($data['hint_id'] == $xase_hint->getId()) {
							$hint = $xase_hint;
						}
					}
				}
				if (empty($hint) || empty($this->xase_hints) || $data['hint_id'] !== $hint->getId()) {
					$hint = new xaseHint();
				}

				if ($data["is_template"] == 0) {
					continue;
				}

				$hint->setQuestionId($this->obj_question->getId());
				if (empty($max_hint_number)) {
					$hint->setHintNumber($id);
				} else {
					$max_hint_number ++;
					$hint->setHintNumber($max_hint_number);
				}

				$hint->setIsTemplate($data["is_template"]);
				$hint->setLabel($data["label"]);

				$hint->store();

				$levels = $this->getLevelsByHintId($hint->getId());

				if (empty($levels)) {
					$level_1 = new xaseHintLevel();
					$level_1->setHintId($hint->getId());
					$level_1_point = new xasePoint();
					$level_1_point->setMinusPoints($data["lvl_1_minus_points"]);
					$level_1_point->store();
					$level_1->setPointId($level_1_point->getId());
					$level_1->setHintLevel(1);
					$level_1->setHint($data["lvl_1_hint"]);
					$level_1->store();

					$level_2 = new xaseHintLevel();
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
					 * @var xaseHintLevel $level
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
					 * @var xaseHintLevel $level
					 */
					foreach ($levels as $level) {

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
	public function update() {

		$this->fillObject();
		$this->obj_question->store();


		return true;
	}
}