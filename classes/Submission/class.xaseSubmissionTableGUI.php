<?php
/**
 * Class xaseSubmissionTableGUI
 *
 * @author: Benjamin Seglias   <bs@studer-raimann.ch>
 */

require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/AssistedExercise/classes/Point/class.xasePoint.php');
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/AssistedExercise/classes/Assessment/class.xaseAssessment.php');
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/AssistedExercise/classes/Answer/class.xaseAnswer.php');
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/AssistedExercise/classes/Voting/class.xaseVoting.php');
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/AssistedExercise/classes/User/class.xaseilUser.php');
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/AssistedExercise/classes/Assessment/class.xaseAssessmentGUI.php');
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/AssistedExercise/classes/Answer/class.xaseAnswerGUI.php');
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/AssistedExercise/classes/class.xaseUpvotingsGUI.php');
require_once('./Services/UIComponent/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php');
require_once('./Services/Table/classes/class.ilTable2GUI.php');
require_once('./Services/Form/classes/class.ilTextInputGUI.php');

require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/AssistedExercise/classes/Assessment/class.xaseAssessments.php');

class xaseSubmissionTableGUI extends ilTable2GUI {

	const TBL_ID = 'xa_sub';

	/**
	 * @var ilObjAssistedExerciseFacade
	 */
	protected $obj_facade;


	/**
	 * @var array
	 */
	protected $filter = [];
	/**
	 * @var xaseSubmissionGUI
	 */
	protected $parent_obj;
	/**
	 * @var ilObjAssistedExerciseAccess
	 */
	protected $access;



	/**
	 * ilLocationDataTableGUI constructor.
	 *
	 * @param xaseSubmissionGUI $a_parent_obj
	 * @param string            $a_parent_cmd
	 */
	function __construct($a_parent_obj, $a_parent_cmd, $a_template_context = "") {
		$this->obj_facade = ilObjAssistedExerciseFacade::getInstance($_GET['ref_id']);

		$this->parent_obj = $a_parent_obj;
		$this->access =  ilObjAssistedExerciseAccess::getInstance($this->obj_facade,$this->obj_facade->getUser()->getId());

		$this->setId(self::TBL_ID);
		$this->setPrefix(self::TBL_ID);
		$this->setFormName(self::TBL_ID);
		$this->obj_facade->getCtrl()->saveParameter($a_parent_obj, $this->getNavParameter());

		parent::__construct($a_parent_obj, $a_parent_cmd,$a_template_context);
		$this->parent_obj = $a_parent_obj;
		$this->setRowTemplate("tpl.default_row.html", "Customizing/global/plugins/Services/Repository/RepositoryObject/AssistedExercise");

		$this->setFormAction($this->obj_facade->getCtrl()->getFormActionByClass('xasesubmissiongui'));
		$this->setExternalSorting(true);

		$this->setDefaultOrderField("submission_date");
		$this->setDefaultOrderDirection("asc");
		$this->setExternalSegmentation(true);
		$this->setEnableHeader(true);

		$this->initColums();
		$this->addFilterItems();
		$this->parseData();
	}


	protected function addFilterItems() {
		$firstname = new ilTextInputGUI($this->obj_facade->getLanguageValue('first_name'), 'firstname');
		$this->addAndReadFilterItem($firstname);

		$lastname = new ilTextInputGUI($this->obj_facade->getLanguageValue('last_name'), 'lastname');
		$this->addAndReadFilterItem($lastname);

		$item = new ilTextInputGUI($this->obj_facade->getLanguageValue('task_title'), 'title');
		$this->addAndReadFilterItem($item);

		//TODO handle Status
		include_once("./Services/Form/classes/class.ilSelectInputGUI.php");
		$option[''] = '';
		$option[1] = $this->obj_facade->getLanguageValue('no');
		$option[2] = $this->obj_facade->getLanguageValue('yes');
		$assessed = new ilSelectInputGUI($this->obj_facade->getLanguageValue("assessed"), "isassessed");
		$assessed->setOptions($option);
		$this->addAndReadFilterItem($assessed);
	}


	/**
	 * @param $item
	 */
	protected function addAndReadFilterItem(ilFormPropertyGUI $item) {
		$this->addFilterItem($item);
		$item->readFromSession();
		if ($item instanceof ilCheckboxInputGUI) {
			$this->filter[$item->getPostVar()] = $item->getChecked();
		} else {
			$this->filter[$item->getPostVar()] = $item->getValue();
		}
	}


	protected function getAssessment($answer_id) {
		$xaseAssessment = xaseAssessment::where(array( 'answer_id' => $answer_id ))->first();
		if (empty($xaseAssessment)) {
			$xaseAssessment = new xaseAssessment();
		}

		return $xaseAssessment;
	}


	protected function getUserObject($xase_answer) {
		return xaseilUser::where(array( 'usr_id' => $xase_answer->getUserId() ))->first();
	}



	/**
	 * @param array $a_set
	 */
	public function fillRow($a_set) {
		foreach ($this->getSelectableColumns() as $k => $v) {

			//TODO
			if($k == 'is_assessed') {
				if($a_set[$k] == 0) {
					$a_set[$k] = $this->obj_facade->getLanguageValue('no');
				}
				if($a_set[$k] == 1) {
					$a_set[$k] = $this->obj_facade->getLanguageValue('yes');
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

		$this->addActionMenu($a_set['answer_id'], $a_set['question_id']);

	}


	protected function initColums() {
		$number_of_selected_columns = count($this->getSelectedColumns());
		//add one to the number of columns for the action
		$number_of_selected_columns ++;
		$column_width = 100 / $number_of_selected_columns . '%';

		$all_cols = $this->getSelectableColumns();
		foreach ($this->getSelectedColumns() as $col) {
			$this->addColumn($all_cols[$col]['txt'], $col, $column_width);
		}

		$this->addColumn($this->obj_facade->getLanguageValue('common_actions'), '', $column_width);
	}


	/**
	 * @param xaseQuestion $xaseQuestion
	 */
	protected function addActionMenu($answer_id,$question_id) {


		$current_selection_list = new ilAdvancedSelectionListGUI();
		$current_selection_list->setListTitle($this->obj_facade->getLanguageValue('common_actions'));
		$current_selection_list->setId('answer_actions' . $answer_id);
		$current_selection_list->setUseImages(false);

		$this->obj_facade->getCtrl()->setParameter($this->parent_obj, xaseQuestionGUI::ITEM_IDENTIFIER, $answer_id);
		$this->obj_facade->getCtrl()->setParameterByClass(xaseAnswerGUI::class, 'answer_id', $answer_id);
		$this->obj_facade->getCtrl()->setParameterByClass(xaseAssessmentGUI::class, 'answer_id', $answer_id);
		$this->obj_facade->getCtrl()->setParameterByClass(xaseUpvotingsGUI::class, 'question_id', $question_id);
		$this->obj_facade->getCtrl()->setParameterByClass(xaseUpvotingsGUI::class, 'answer_id', $answer_id);

		$this->obj_facade->getCtrl()->setParameterByClass(xaseAssessmentGUI::class, 'question_id', $question_id);


		if ($this->access->hasWriteAccess() && !ilObjAssistedExerciseAccess::isDisposalLimitRespected($this->obj_facade)) {
			$current_selection_list->addItem($this->obj_facade->getLanguageValue('assess'), xaseAssessmentGUI::CMD_STANDARD, $this->obj_facade->getCtrl()->getLinkTargetByClass('xaseassessmentgui', xaseAssessmentGUI::CMD_STANDARD));
		}

		/*if ($this->xase_settings->getModus() == 3) {
			if ($this->access->hasWriteAccess()) {
				$current_selection_list->addItem($this->obj_facade->getLanguageValue('show_upvotings'), xaseUpvotingsGUI::CMD_STANDARD, $this->obj_facade->getCtrl()->getLinkTargetByClass(xaseUpvotingsGUI::class, xaseUpvotingsGUI::CMD_STANDARD));
			}
		}*/
		$this->tpl->setVariable('ACTIONS', $current_selection_list->getHTML());
	}

	protected function getQuestionIdsFromThisExercise() {
		$items = xaseQuestion::where(array( 'assisted_exercise_id' => $this->obj_facade->getIlObjObId() ))->get();
		$question_ids = [];
		foreach ($items as $item) {
			$question_ids[] = $item->getId();
		}
		return $question_ids;
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

		$count = xaseAssessments::getData($options,$this->obj_facade->getIlObjObId());
		$options['limit'] = array( 'start' => (int)$this->getOffset(), 'end' => (int)$this->getLimit());
		$data = xaseAssessments::getData(array_merge($options, array( 'count' => false )),$this->obj_facade->getIlObjObId());

		$this->setMaxCount($count);
		$this->setData($data);
	}


	public function getSelectableColumns() {
		$cols["firstname"] = array(
			"txt" => $this->obj_facade->getLanguageValue("first_name"),
			"default" => true
		);
		$cols["lastname"] = array(
			"txt" => $this->obj_facade->getLanguageValue("last_name"),
			"default" => true
		);
		$cols["submission_date"] = array(
			"txt" => $this->obj_facade->getLanguageValue("submission_date"),
			"default" => true
		);
		$cols["question_title"] = array(
			"txt" => $this->obj_facade->getLanguageValue("task_title"),
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
		$cols["is_assessed"] = array(
			"txt" => $this->obj_facade->getLanguageValue("assessed"),
			"default" => true
		);
		$cols["points_teacher"] = array(
			"txt" => $this->obj_facade->getLanguageValue("points_teacher"),
			"default" => true
		);
		$cols["additional_points"] = array(
			"txt" => $this->obj_facade->getLanguageValue("additional_points_voting"),
			"default" => true
		);
		$cols["total_points"] = array(
			"txt" => $this->obj_facade->getLanguageValue("total_points"),
			"default" => true
		);
		$cols["number_of_upvotings"] = array(
			"txt" => $this->obj_facade->getLanguageValue("number_of_upvotings"),
			"default" => true
		);


		return $cols;
	}


	/*
	 * 1) get all items from this exercise
	 * 2) loop through items
	 * 3) get answers for this item (from all users)
	 * 4) loop through answers
	 * 5) save the total points from each answer in a variable
	 * 6) save the number of answers in a variable ( count() )
	 * 7) divide total points from all answers by the number of answers
	 * 8) return the result of the division
	 */
	protected function getAverageAchievedPoints() {
		$items = xaseQuestion::where(array( 'assisted_exercise_id' => $this->obj_facade->getIlObjObId() ))->get();
		$total_points = 0;
		$number_of_answers = 0;
		foreach ($items as $item) {
			$answers = xaseAnswer::where(array( 'question_id' => $item->getId() ))->get();
			foreach ($answers as $answer) {
				$number_of_answers ++;
				$total_points += $answer->getTotalPoints();
			}
		}

		return $number_of_answers > 0 ? $total_points / $number_of_answers : 0;
	}


	protected function getAverageUsedHintsPerItem() {
		$items = xaseQuestion::where(array( 'assisted_exercise_id' => $this->obj_facade->getIlObjObId() ))->get();
		$total_used_hints = 0;
		$number_of_answers = 0;
		foreach ($items as $item) {
			$total_used_hints += xaseUsedHintLevel::where(array('question_id' => $item->getId()))->count();
			/**
			 * @var xaseAnswer[] $answers
			 */
			$number_of_answers = xaseAnswer::where(array( 'question_id' => $item->getId() ))->count();
		}

		return $number_of_answers > 0 ? $total_used_hints / $number_of_answers : 0;
	}


	public function createListing() {
		$f = $this->dic->ui()->factory();
		$renderer = $this->dic->ui()->renderer();

		$unordered = $f->listing()->descriptive(array(
				$this->obj_facade->getLanguageValue('max_achievable_points') => strval(xaseQuestionTableGUI::getMaxAchievablePoints($this->obj_facade->getIlObjObId(), $this->xase_settings->getModus())),
				$this->obj_facade->getLanguageValue('average_achieved_points') => strval($this->getAverageAchievedPoints()),
				$this->obj_facade->getLanguageValue('average_used_hints_per_item') => strval($this->getAverageUsedHintsPerItem())
			));

		return $renderer->render($unordered);
	}

	protected function getModeSetting($mode) {
		if ($mode == xaseSettingMODUS1) {
			return xaseSettingM1::where([ 'settings_id' => $this->xase_settings->getId() ])->first();
		} elseif ($mode == xaseSettingMODUS3) {
			return xaseSettingM3::where([ 'settings_id' => $this->xase_settings->getId() ])->first();
		} else {
			return xaseSettingM2::where([ 'settings_id' => $this->xase_settings->getId() ])->first();
		}
	}
}