<?php
/**
 * Class xaseQuestionTableGUI
 *
 * @author Martin Studer <ms@studer-raimann.ch>
 */

require_once './Customizing/global/plugins/Services/Repository/RepositoryObject/AssistedExercise/classes/Question/class.xaseQuestions.php';
require_once './Customizing/global/plugins/Services/Repository/RepositoryObject/AssistedExercise/classes/Answer/class.xaseAnserAccess.php';
require_once './Customizing/global/plugins/Services/Repository/RepositoryObject/AssistedExercise/classes/Question/class.xaseQuestionAccess.php';

require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/AssistedExercise/classes/Point/class.xasePoint.php');
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/AssistedExercise/classes/Hint/class.xaseHint.php');
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/AssistedExercise/classes/Answer/class.xaseAnswer.php');
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/AssistedExercise/classes/SampleSolution/class.xaseSampleSolution.php');
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/AssistedExercise/classes/Answer/class.xaseAnswerGUI.php');
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/AssistedExercise/classes/Assessment/class.xaseAssessmentGUI.php');
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/AssistedExercise/classes/SampleSolution/class.xaseSampleSolutionGUI.php');
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/AssistedExercise/classes/Voting/class.xaseVotingGUI.php');
require_once('./Services/UIComponent/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php');
require_once('./Services/Table/classes/class.ilTable2GUI.php');
require_once('./Services/Form/classes/class.ilTextInputGUI.php');

require_once 'class.xaseQuestionAccess.php';

class xaseQuestionTableGUI extends ilTable2GUI {

	const TBL_ID = 'xase_m1q';

	protected $parent_obj;

	/**
	 * @var ilObjAssistedExerciseFacade
	 */
	protected $obj_facade;
	/** @var  array $filter */
	protected $filter = array();


	/**
	 * ilLocationDataTableGUI constructor.
	 *
	 * @param xaseQuestionGUI $a_parent_obj
	 * @param string      $a_parent_cmd
	 */
	function __construct($a_parent_obj, $a_parent_cmd,  $a_template_context = "") {
		$this->obj_facade = ilObjAssistedExerciseFacade::getInstance($_GET['ref_id']);

		$this->setId(self::TBL_ID);
		$this->setPrefix(self::TBL_ID);
		$this->setFormName(self::TBL_ID);
		$this->obj_facade->getctrl()->saveParameter($a_parent_obj, $this->getNavParameter());

		parent::__construct($a_parent_obj, $a_parent_cmd);
		$this->parent_obj = $a_parent_obj;
		$this->setRowTemplate("tpl.default_row.html", "Customizing/global/plugins/Services/Repository/RepositoryObject/AssistedExercise");

		$this->setFormAction($this->obj_facade->getctrl()->getFormAction($a_parent_obj));
		$this->setExternalSorting(true);

		$this->setDefaultOrderField("question_title");
		$this->setDefaultOrderDirection("asc");
		$this->setExternalSegmentation(true);
		$this->setEnableHeader(true);
		
		$this->addColumns();
		$this->initFilter();
		$this->parseData();
	}





	//TODO evtl. In Antwort Assisted Exercise Id speichern und mit count Function arbeiten
	protected function has_answers() {
		$all_items = xaseQuestion::where(array( 'assisted_exercise_id' => $this->obj_facade->getIlObjObId() ))->get();
		foreach ($all_items as $item) {
			$answer_array = xaseAnswer::where(array( 'question_id' => $item->getId() ))->get();
			if (!empty($answer_array)) {
				return true;
			}
		}
		return false;
	}


	public function initFilter() {
		$title = new ilTextInputGUI($this->obj_facade->getLanguageValue('title'), 'question_title');
		$this->addFilterItemWithValue($title);

		include_once("./Services/Form/classes/class.ilSelectInputGUI.php");
		$option[0] = $this->obj_facade->getLanguageValue('open');
		$option[1] = $this->obj_facade->getLanguageValue('answered');
		$option[2] = $this->obj_facade->getLanguageValue('submitted');
		$option[3] = $this->obj_facade->getLanguageValue('rated');
		/*if ($this->xase_settings->getModus() == xaseSettingMODUS2 || $this->xase_settings->getModus() == xaseSettingMODUS3) {
			$option[4] = $this->obj_facade->getLanguageValue('can_be_voted');
		}*/
		$status = new ilSelectInputGUI($this->obj_facade->getLanguageValue("status"), "answer_status");
		$status->setOptions($option);
		$this->addFilterItemWithValue($status);
	}

	protected function addFilterItemWithValue($item) {
		$this->addFilterItem($item);
		$item->readFromSession();

		switch (get_class($item)) {
			case 'ilSelectInputGUI':
				$value = $item->getValue();
				break;
			case 'ilCheckboxInputGUI':
				$value = $item->getChecked();
				break;
			case 'ilDateTimeInputGUI':
				$value = $item->getDate();
				break;
			default:
				$value = $item->getValue();
				break;
		}

		if ($value) {
			$this->filter[$item->getPostVar()] = $value;
		}
	}


	

	protected function getAnswer($question_id) {
		$xaseAnswer = xaseAnswer::where(array( 'question_id' => $question_id, 'user_id' => $this->dic->user()->getId() ), array(
			'question_id' => '=',
			'user_id' => '='
		))->first();
		if (empty($xaseAnswer)) {
			$xaseAnswer = new xaseAnswer();
		}

		return $xaseAnswer;
	}


	/**
	 * @param array $a_set
	 */
	public function fillRow($a_set) {
		foreach ($this->getSelectableColumns() as $k => $v) {

			if($k == 'created_by') {
				$a_set[$k] = ilObjUser::_lookupFullname($a_set[$k]);
			}

			//TODO Refactor - Status Object with status to lang
			if($k == 'answer_status') {
				switch($a_set[$k]) {
					case xaseAnswer::ANSWER_STATUS_OPEN:
						$a_set[$k] = $this->obj_facade->getLanguageValue('open');
						break;
					case xaseAnswer::ANSWER_STATUS_ANSWERED:
						$a_set[$k] = $this->obj_facade->getLanguageValue('answered');
						break;
					case xaseAnswer::ANSWER_STATUS_OPEN:
						$a_set[$k] = $this->obj_facade->getLanguageValue('can_be_voted');
						break;
				}
			}

			//TODO
			if($k == 'highest_ratet_answer') {

				if($a_set[$k] > 0) {
					$this->obj_facade->getCtrl()->setParameterByClass('xaseAnswerGUI','question_id',$a_set['question_id']);
					$this->obj_facade->getCtrl()->setParameterByClass('xaseAnswerGUI','answer_id',$a_set[$k]);
					$link = $this->obj_facade->getCtrl()->getLinkTargetByClass(array('ilObjPluginDispatchGUI','ilObjAssistedExerciseGUI','xaseAnswerGUI'),xaseAnswerGUI::CMD_SHOW);

					$answer = new xaseAnswer($a_set[$k]);

					$best_votet_answer_html = ilObjUser::_lookupFullname($answer->getUserId());
					$best_votet_answer_html .= '<br/>'.$this->obj_facade->getLanguageValue('number_of_upvotings').': '.$answer->returnNumberOfUpvotings();
					$best_votet_answer_html .= '<br/><a href="'.$link.'">'.$this->obj_facade->getLanguageValue('show').'</a>';

					$a_set[$k] = $best_votet_answer_html;
				}


			}

			if ($this->isColumnSelected($k)) {
				if ($a_set[$k]) {
					$this->tpl->setCurrentBlock('td');
					$this->tpl->setVariable('VALUE', (is_array($a_set[$k]) ? implode(", ", $a_set[$k]) : $a_set[$k]));
					$this->tpl->parseCurrentBlock();
				} else {
					$this->tpl->setCurrentBlock('td');
					$this->tpl->setVariable('VALUE', '&nbsp;');
					$this->tpl->parseCurrentBlock();
				}
			}
		}

		$this->addActionMenu($a_set);
	}

	private function addColumns() {
		global $ilLog, $ilCtrl;

		foreach ($this->getSelectableColumns() as $k => $v) {
			if ($this->isColumnSelected($k)) {
				if ($v['sort_field']) {
					$sort = $v['sort_field'];
				} else {
					$sort = $k;
				}
				$this->addColumn($v['txt'], $sort, $v['width']);
			}
		}
		if(!$this->getExportMode()) {
			$this->addColumn($this->obj_facade->getLanguageValue('common_actions'));
		}
	}


	/**
	 * @param $question_id
	 *
	 * @return xaseAnswer
	 */
	protected function getUserAnswerByItemId($question_id) {
		$xase_answer = xaseAnswer::where(array( 'question_id' => $question_id, 'user_id' => $this->dic->user()->getId() ))->first();

		return $xase_answer;
	}


	/**
	 * @param $a_set
	 */
	protected function addActionMenu($a_set) {

		if($a_set['answer_id']) {
			$xase_answer = new xaseAnswer($a_set['answer_id']);
			$answer_access = new xaseAnswerAccess($xase_answer,$this->obj_facade->getUser()->getId());
		}

		$xase_answer = new xaseQuestion($a_set['question_id']);
		$question_access = new xaseQuestionAccess($xase_answer,$this->obj_facade->getUser()->getId());


		//$xase_answer = $this->getUserAnswerByItemId($a_set['question_id']);

		$current_selection_list = new ilAdvancedSelectionListGUI();
		$current_selection_list->setListTitle($this->obj_facade->getLanguageValue('common_actions'));
		$current_selection_list->setId('item_actions_' . $a_set['question_id']);
		$current_selection_list->setUseImages(false);

		$this->obj_facade->getctrl()->setParameter($this->parent_obj, xaseQuestionGUI::ITEM_IDENTIFIER, $a_set['question_id']);
		$this->obj_facade->getctrl()->setParameterByClass(xaseAnswerGUI::class, xaseQuestionGUI::ITEM_IDENTIFIER, $a_set['question_id']);
		if(!empty($a_set['answer_id']) && $answer_access->hasReadAccess()) {
			$this->obj_facade->getctrl()->setParameterByClass(xaseAnswerGUI::class,'answer_id',$a_set['answer_id']);
			$current_selection_list->addItem($this->obj_facade->getLanguageValue('my_answer'), xaseAnswerGUI::CMD_STANDARD, $this->obj_facade->getctrl()->getLinkTargetByClass('xaseanswergui', xaseAnswerGUI::CMD_STANDARD));
		} elseif(xaseAnswerAccess::hasCreateAccess($this->obj_facade,$this->obj_facade->getUser()->getId())) {
			$this->obj_facade->getctrl()->setParameterByClass(xaseAnswerGUI::class,'answer_id',0);
			$current_selection_list->addItem($this->obj_facade->getLanguageValue('my_answer'), xaseAnswerGUI::CMD_STANDARD, $this->obj_facade->getctrl()->getLinkTargetByClass('xaseanswergui', xaseAnswerGUI::CMD_STANDARD));
		}
		$this->obj_facade->getctrl()->setParameterByClass(xaseSampleSolutionGUI::class, xaseQuestionGUI::ITEM_IDENTIFIER, $a_set['question_id']);
		$this->obj_facade->getctrl()->setParameterByClass(xaseQuestionDeleteGUI::class, xaseQuestionGUI::ITEM_IDENTIFIER,$a_set['question_id']);
		$this->obj_facade->getctrl()->setParameterByClass(xaseAnswerListGUI::class, xaseQuestionGUI::ITEM_IDENTIFIER, $a_set['question_id']);
		$this->obj_facade->getctrl()->setParameterByClass(xaseVotingGUI::class, xaseQuestionGUI::ITEM_IDENTIFIER, $a_set['question_id']);

		if(!empty($a_set['answer_id'])) {
			//if($this->xase_settings->getModus() == xaseSettingMODUS2 || $this->xase_settings->getModus() == xaseSettingMODUS3 && $xase_answer->getAnswerStatus() == xaseAnswer::ANSWER_STATUS_CAN_BE_VOTED || $xase_answer->getAnswerStatus() == xaseAnswer::ANSWER_STATUS_RATED || $xase_answer->getAnswerStatus() == xaseAnswer::ANSWER_STATUS_CAN_BE_VOTED) {
				//$current_selection_list->addItem($this->obj_facade->getLanguageValue('view_answers'), xaseAnswerListGUI::CMD_STANDARD, $this->obj_facade->getctrl()->getLinkTargetByClass(xaseAnswerListGUI::class, xaseAnswerListGUI::CMD_STANDARD));

				//ToDo Refactor!

				if($a_set['answer_status'] == xaseAnswer::ANSWER_STATUS_CAN_BE_VOTED) {

					/*if(count(xaseVotings::getUnvotedAnswersOfUser($this->obj_facade->getIlObjObId(), $this->dic->user()->getId(), $a_set['question_id'])) >= 2
						OR
						count(xaseVotings::getUnvotedAnswersOfUser($this->obj_facade->getIlObjObId(), $this->dic->user()->getId(), $a_set['question_id'])) == 1
						AND is_object(xaseVotings::getBestVotedAnswerOfUser($this->obj_facade->getIlObjObId(), $this->dic->user()->getId(), $a_set['question_id']))

					) {*/
					if($question_access->hasVotingAccess()) {
						$this->obj_facade->getctrl()->setParameterByClass(xaseVotingGUI::class, xaseQuestionGUI::ITEM_IDENTIFIER,$a_set['question_id']);
						$current_selection_list->addItem($this->obj_facade->getLanguageValue('vote'), xaseAnswerListGUI::CMD_STANDARD, $this->obj_facade->getctrl()->getLinkTargetByClass(xaseVotingGUI::class, xaseVotingGUI::CMD_STANDARD));
					}
							//}

				}



				if($question_access->hasVotingDeleteAccess()) {
					$current_selection_list->addItem($this->obj_facade->getLanguageValue('delete_my_votings'), xaseVotingGUI::CMD_DELETE_USERS_VOTINGS, $this->obj_facade->getctrl()->getLinkTargetByClass(xaseVotingGUI::class, xaseVotingGUI::CMD_DELETE_USERS_VOTINGS));
				}


			}
		//}

		/*if (!empty($xase_answer) && $xase_answer->getAnswerStatus() == xaseAnswer::ANSWER_STATUS_RATED
			&& $this->xase_settings->getModus() != xaseSettingMODUS2) {
			$this->obj_facade->getctrl()->setParameterByClass(xaseAssessmentGUI::class, xaseAnswerGUI::ANSWER_IDENTIFIER, $xase_answer->getId());
			$current_selection_list->addItem($this->obj_facade->getLanguageValue('view_assessment'), xaseAssessmentGUI::CMD_VIEW_ASSESSMENT, $this->obj_facade->getctrl()->getLinkTargetByClass('xaseassessmentgui', xaseAssessmentGUI::CMD_VIEW_ASSESSMENT));
		}
		*/
		if($question_access->hasAccessToSampleSolution()) {
			$current_selection_list->addItem($this->obj_facade->getLanguageValue('view_sample_solution'), xaseSampleSolutionGUI::CMD_STANDARD, $this->obj_facade->getctrl()->getLinkTargetByClass('xaseSampleSolutionGUI', xaseSampleSolutionGUI::CMD_STANDARD));
		}


		if($question_access->hasWriteAccess()) {
			$current_selection_list->addItem($this->obj_facade->getLanguageValue('edit_task'), xaseQuestionGUI::CMD_EDIT, $this->obj_facade->getctrl()->getLinkTargetByClass('xasequestiongui', xaseQuestionGUI::CMD_EDIT));
		}
		if ($question_access->hasDeleteAccess()) {
			$current_selection_list->addItem($this->obj_facade->getLanguageValue('delete_task'), xaseQuestionDeleteGUI::CMD_STANDARD, $this->obj_facade->getctrl()->getLinkTargetByClass('xasequestiondeletegui', xaseQuestionDeleteGUI::CMD_STANDARD));
		}

		/*        if ($this->access->hasWriteAccess()) {
					$current_selection_list->addItem($this->obj_facade->getLanguageValue('edit_answer'), xaseAnswerGUI::CMD_EDIT, $this->obj_facade->getctrl()->getLinkTargetByClass('xaseanswergui', xaseAnswerGUI::CMD_EDIT));
				}*/
		$this->tpl->setVariable('ACTIONS', $current_selection_list->getHTML());
	}

	protected function parseData() {
		$this->setExternalSorting(true);
		$this->setExternalSegmentation(true);
		//$this->setDefaultOrderField($this->columns[0]);

		$this->determineLimit();
		$this->determineOffsetAndOrder();

		$options = array(
			'filters' => $this->filter,
			'limit' => array(),
			'count' => true,
			'sort' => array( 'field' => $this->getOrderField(), 'direction' => $this->getOrderDirection() ),
		);

		$count = xaseQuestions::getData($options,$this->obj_facade->getUser()->getId());
		$options['limit'] = array( 'start' => (int)$this->getOffset(), 'end' => (int)$this->getLimit());
		$data = xaseQuestions::getData(array_merge($options, array( 'count' => false )),$this->obj_facade->getUser()->getId());
		$this->setMaxCount($count);
		$this->setData($data);

	}


	public function getSelectableColumns() {
		$cols["question_title"] = array(
			"txt" => $this->obj_facade->getLanguageValue("title"),
			"default" => true
		);
		$cols["severity"] = array(
			"txt" => $this->obj_facade->getLanguageValue("severity"),
			"default" => true
		);
		$cols["answer_status"] = array(
			"txt" => $this->obj_facade->getLanguageValue("status"),
			"default" => true
		);
		$cols["created_by"] = array(
			"txt" => $this->obj_facade->getLanguageValue("created_by"),
			"default" => true
		);
		$cols["max_points"] = array(
				"txt" => $this->obj_facade->getLanguageValue("max_points"),
				"default" => true
		);
		$cols["number_of_used_hints"] = array(
			"txt" => $this->obj_facade->getLanguageValue("number_of_used_hints"),
			"default" => true
			);
		$cols["points_teacher"] = array(
			"txt" => $this->obj_facade->getLanguageValue("points_teacher"),
			"default" => true
		);
		$cols["additional_points_voting"] = array(
			"txt" => $this->obj_facade->getLanguageValue("additional_points_voting"),
			"default" => true
		);
		$cols["total_points"] = array(
			"txt" => $this->obj_facade->getLanguageValue("total_points"),
			"default" => true
		);
		/*$cols["is_voted"] = array(
			"txt" => $this->obj_facade->getLanguageValue("is_voted"),
			"default" => true
		);*/
		$cols["number_of_upvotings"] = array(
			"txt" => $this->obj_facade->getLanguageValue("number_of_upvotings_my_answer"),
			"default" => true
		);
		$cols["highest_ratet_answer"] = array(
			"txt" => $this->obj_facade->getLanguageValue("highest_ratet_answer"),
			"default" => true
		);


		return $cols;
	}


	public static function getMaxAchievablePoints($assisted_exercise_id, $modus) {
		$items = xaseQuestion::where(array( 'assisted_exercise_id' => $assisted_exercise_id ))->get();
		$max_achievable_points = 0;
		if ($modus != xaseSettingMODUS2) {
			foreach ($items as $item) {
				$xasePoint = xasePoint::where(array( 'id' => $item->getPointId() ))->first();
				if (!empty($xasePoint)) {
					$max_achievable_points += $xasePoint->getMaxPoints();
				}
			}
		}
		return $max_achievable_points;
	}

	public static function getAnswersFromUser($assisted_exercise_object, $dic) {
		$items = xaseQuestion::where(array( 'assisted_exercise_id' => $assisted_exercise_object->getId() ))->get();
		$question_ids = [];
		foreach ($items as $item) {
			$question_ids[] = $item->getId();
		}
		if (empty($question_ids)) {
			return NULL;
		} else {
			return xaseAnswer::where(array( 'user_id' => $dic->user()->getId(), 'question_id' => $question_ids ), array(
				'user_id' => '=',
				'question_id' => 'IN'
			))->get();
		}
	}

	static function getAllUserAnswersFromAssistedExercise($all_items_assisted_exercise, $dic, $user) {
		foreach ($all_items_assisted_exercise as $item_assisted_exercise) {
			$all_items_assisted_exercise_ids[] = $item_assisted_exercise->getId();
		}
		$all_items_assisted_exercise_ids_string = implode(', ', $all_items_assisted_exercise_ids);
		$statement = $dic->database()->query("SELECT * FROM xase_answer where user_id = " . $user->getId()
			. " AND question_id IN ($all_items_assisted_exercise_ids_string)");

		$results = array();

		while ($record = $dic->database()->fetchAssoc($statement)) {
			$results[] = $record;
		}

		return $results;
	}


	protected function hasUserFinishedExercise() {
		/*
		 * 1) retrieve all items from the current assisted exercise
		 * 2) retrieve all answers from the currently logged in user
		 * 3) save all the item ids from the answers
		 * 4) check with the item id if the user has answered all items of the exercise
		 *      a) yes
		 *          return true (afterwards: show a Button Submit for assessment in the list gui)
		 *      b) no
		 *          return false
		 */
		$all_items_assisted_exercise = xaseQuestion::where(array( 'assisted_exercise_id' => $this->assisted_exercise->getId() ))->get();

		if (empty($all_items_assisted_exercise)) {
			return false;
		}

		//$answers_from_current_user = xaseAnswer::where(array('user_id' => $this->dic->user()->getId(), 'question_id' => $this->xase_question->getId()))->get();
		$answers_from_current_user = self::getAllUserAnswersFromAssistedExercise($all_items_assisted_exercise, $this->dic, $this->dic->user());

		foreach ($all_items_assisted_exercise as $item) {
			$all_question_ids[] = $item->getId();
		}

		foreach ($answers_from_current_user as $answer) {
			if (is_array($answer)) {
				$question_ids_from_answers[] = $answer['question_id'];
			} else {
				$question_ids_from_answers[] = $answers_from_current_user['question_id'];
				break;
			}
		}

		if (is_array($all_question_ids) && is_array($question_ids_from_answers)) {
			$not_answered_items = array_diff($all_question_ids, $question_ids_from_answers);
		}

		if (empty($not_answered_items) && is_array($question_ids_from_answers)) {
			return true;
		} else {
			return false;
		}
	}


	/*
	 * 1) create answer objects inside of the foreach loop
	 * 2) check if the answer status is submitted
	 * 3) if the status of one of the answers is submitted
	 * 4)   return false, since the answers can only be submitted all together
	 */
	protected function checkIfAnswersAlreadySubmitted($answers_from_current_user) {
		foreach ($answers_from_current_user as $answer) {
			if (is_array($answer)) {
				$answer_from_current_user_object = xaseAnswer::where(array( 'id' => $answer['id'] ))->first();
			} else {
				$answer_from_current_user_object = xaseAnswer::where(array( 'id' => $answers_from_current_user['id'] ))->first();
			}
			if ($answer_from_current_user_object->getAnswerStatus() == xaseAnswer::ANSWER_STATUS_CAN_BE_VOTED) {
				return true;
			}
		}

		return false;
	}


	protected function isDisposalDateExpired() {
		/*$current_date = date('Y-m-d h:i:s', time());

		$current_date_datetime = DateTime::createFromFormat('Y-m-d H:i:s', $current_date);
		$disposal_date_datetime = DateTime::createFromFormat('Y-m-d H:i:s', $this->mode_settings->getDisposalDate());

		if (($disposal_date_datetime->getTimestamp() > $current_date_datetime->getTimestamp())
			|| $this->mode_settings->getDisposalDate() == "0000-00-00 00:00:00") {
			return false;
		} else {
			return true;
		}*/
	}


	/*
	 * 1) Mode 1
	 *  a)Nach Abschluss der Übung
	 *  b)Ab definiertem Datum
	 * 2) Mode 2 keine Musterlösung
	 * 3) Mode 3
	 *      die Schüler haben die Musterlösung sobald Sie das Voting abgegeben haben
	 */
	protected function isSampleSolutionAvailable($mode, $xase_question) {
/*
		$xase_sample_solution = xaseSampleSolution::where(array( 'id' => $xase_question->getSampleSolutionId() ))->first();
		if (empty($xase_sample_solution)) {
			return false;
		} else {
			if ($mode == xaseSettingMODUS1) {
				if ($this->mode_settings->getSampleSolutionVisible()) {
					if ($this->mode_settings->getVisibleIfExerciseFinished()) {
						if ($this->hasUserFinishedExercise()) {
							return true;
						} else {
							return false;
						}
					} else {
						$current_date = date('Y-m-d h:i:s a', time());
						if ($this->mode_settings->getSolutionVisibleDate() <= $current_date) {
							return true;
						} else {
							return false;
						}
					}
				} else {
					return false;
				}
			} elseif ($mode == xaseSettingMODUS2) {
				return false;
			} else {
				if ($this->hasUserVotedForItem($xase_question)) {
					return true;
				} else {
					return false;
				}
			}
		}*/
	}


	protected function hasUserVotedForAllItems() {
		$items = xaseQuestion::where(array( 'assisted_exercise_id' => $this->assisted_exercise->getId() ))->get();
		foreach ($items as $item) {
			if (!$this->hasUserVotedForItem($item)) {
				return false;
			}
		}

		return true;
	}


	/*
	 * 1) get all Answers from the current item
	 * 2) get all the votings from the current user
	 * 3) check if the user has voted for one of the answers
	 *      -check if the answer_id from the current voting in the loop iteration is in the array of answer ids (all Answers from the current item)
	 *  a) yes -> sample solution available
	 *  b) no -> sample solution not available
	 */
	protected function hasUserVotedForItem(xaseQuestion $xaseQuestion) {
		$answers_for_current_item = xaseAnswer::where(array( 'question_id' => $xaseQuestion->getId() ))->get();
		$votings_from_current_user = xaseVoting::where(array( 'user_id' => $this->dic->user()->getId() ))->get();
		$answers_ids = [];
		foreach ($answers_for_current_item as $answer) {
			$answers_ids[] = $answer->getId();
		}
		if (!empty($votings_from_current_user)) {
			foreach ($votings_from_current_user as $voting) {
				if (array_key_exists($voting->getAnswerId(), $answers_for_current_item)) {
					return true;
				}
			}
		}

		return false;
	}


}