<?php

/**
 * Class xaseItemTableGUI
 *
 * @author            Benjamin Seglias <bs@studer-raimann.ch>
 */

require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/AssistedExercise/classes/ActiveRecords/class.xasePoint.php');
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/AssistedExercise/classes/ActiveRecords/class.xaseHint.php');
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/AssistedExercise/classes/ActiveRecords/class.xaseAnswer.php');
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/AssistedExercise/classes/ActiveRecords/class.xaseSampleSolution.php');
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/AssistedExercise/classes/class.xaseAnswerGUI.php');
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/AssistedExercise/classes/class.xaseAssessmentGUI.php');
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/AssistedExercise/classes/class.xaseSampleSolutionGUI.php');
require_once('./Services/UIComponent/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php');
require_once('./Services/Table/classes/class.ilTable2GUI.php');
require_once('./Services/Form/classes/class.ilTextInputGUI.php');

require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/AssistedExercise/classes/services/xaseItemAccess.php');

class xaseItemTableGUI extends ilTable2GUI {

	const TBL_ID = 'tbl_xase_items';
	const M1 = "1";
	const M2 = "2";
	const M3 = "3";
	/**
	 * @var \ILIAS\DI\Container
	 */
	protected $dic;
	/**
	 * @var ilCtrl
	 */
	protected $ctrl;
	/**
	 * @var array
	 */
	protected $filter = [];
	/**
	 * @var xaseItemGUI
	 */
	protected $parent_obj;
	/**
	 * @var xaseItem
	 */
	public $xase_item;
	/**
	 * @var ilAssistedExercisePlugin
	 */
	protected $pl;
	/**
	 * @var ilObjAssistedExerciseAccess
	 */
	protected $access;
	/**
	 * @var ilObjAssistedExercise
	 */
	public $assisted_exercise;
	/**
	 * @var xaseSettings
	 */
	public $xase_settings;
	/**
	 * @var xaseSettingsM1|xaseSettingsM2|xaseSettingsM3
	 */
	protected $mode_settings;


	/**
	 * ilLocationDataTableGUI constructor.
	 *
	 * @param xaseItemGUI $a_parent_obj
	 * @param string      $a_parent_cmd
	 */
	function __construct($a_parent_obj, $a_parent_cmd, ilObjAssistedExercise $assisted_exercise) {
		global $DIC;
		$this->dic = $DIC;
		$this->parent_obj = $a_parent_obj;
		$this->ctrl = $this->dic->ctrl();
		$this->pl = ilAssistedExercisePlugin::getInstance();
		$this->access = new ilObjAssistedExerciseAccess();

		$this->setId(self::TBL_ID);
		$this->setPrefix(self::TBL_ID);
		$this->setFormName(self::TBL_ID);
		$this->ctrl->saveParameter($a_parent_obj, $this->getNavParameter());
		$this->assisted_exercise = $assisted_exercise;
		$this->xase_settings = xaseSettings::where([ 'assisted_exercise_object_id' => $this->assisted_exercise->getId() ])->first();
		$this->mode_settings = $this->getModeSettings($this->xase_settings->getModus());
		$this->xase_item = new xaseItem($_GET[xaseItemGUI::ITEM_IDENTIFIER]);

		$this->initButtons($DIC);

		parent::__construct($a_parent_obj, $a_parent_cmd);
		$this->parent_obj = $a_parent_obj;
		$this->setRowTemplate("tpl.items.html", "Customizing/global/plugins/Services/Repository/RepositoryObject/AssistedExercise");

		$this->setFormAction($this->ctrl->getFormActionByClass('xaseitemgui'));
		$this->setExternalSorting(true);

		$this->setDefaultOrderField("item_title");
		$this->setDefaultOrderDirection("asc");
		$this->setExternalSegmentation(true);
		$this->setEnableHeader(true);


		$this->initColums();
		$this->addFilterItems();
		$this->parseData();
	}


	protected function initButtons($DIC) {

		if (ilObjAssistedExerciseAccess::hasWriteAccess($_GET['ref_id'], $this->dic->user()->getId())
			|| $this->xase_settings->getModus() == self::M2) {
			//if(!$this->has_submitted_answers()) {
			$new_item_link = $this->ctrl->getLinkTargetByClass("xaseItemGUI", xaseItemGUI::CMD_EDIT);
			$ilLinkButton = ilLinkButton::getInstance();
			$ilLinkButton->setCaption($this->pl->txt("add_task"), false);
			$ilLinkButton->setUrl($new_item_link);
			/** @var $ilToolbar ilToolbarGUI */
			$DIC->toolbar()->addButtonInstance($ilLinkButton);
			//}
		}

		if ($this->xase_settings->getModus() == self::M1 || $this->xase_settings->getModus() == self::M3) {
			if ($this->hasUserFinishedExercise()) {
				if (!$this->checkIfAnswersAlreadySubmitted(self::getAllUserAnswersFromAssistedExercise(xaseItem::where(array( 'assisted_exercise_id' => $this->assisted_exercise->getId() ))
					->get(), $this->dic, $this->dic->user()))) {
					if (!$this->isDisposalDateExpired()) {
						if ($this->mode_settings->getRateAnswers()) {
							if ($this->xase_settings->getModus() == self::M3 && $this->hasUserVotedForAllItems()) {
								$this->ctrl->setParameterByClass("xasesubmissiongui", xaseItemGUI::ITEM_IDENTIFIER, $this->xase_item->getId());
								$new_submission_link = $this->ctrl->getLinkTargetByClass("xaseSubmissionGUI", xaseSubmissionGUI::CMD_ADD_SUBMITTED_EXERCISE);
								$submissionLinkButton = ilLinkButton::getInstance();
								$submissionLinkButton->setCaption($this->pl->txt("submit_for_assessment"), false);
								$submissionLinkButton->setUrl($new_submission_link);
								/** @var $ilToolbar ilToolbarGUI */
								$DIC->toolbar()->addButtonInstance($submissionLinkButton);
							}
						}
					}
				}
			}
		}
		if ($this->xase_settings->getModus() == self::M2 || $this->xase_settings->getModus() == self::M3) {
			if (!empty(self::getAnswersFromUser($this->parent_obj->object, $this->dic))) {
				$new_release_answers_for_voting_link = $this->ctrl->getLinkTarget($this->parent_obj, xaseItemGUI::CMD_SET_ANSWER_STATUS_TO_CAN_BE_VOTED);
				$releaseForVotingLinkButton = ilLinkButton::getInstance();
				$releaseForVotingLinkButton->setCaption($this->pl->txt("release_answers_for_voting"), false);
				$releaseForVotingLinkButton->setUrl($new_release_answers_for_voting_link);
				/** @var $ilToolbar ilToolbarGUI */
				$DIC->toolbar()->addButtonInstance($releaseForVotingLinkButton);
			}
		}
	}


	//TODO evtl. In Antwort Assisted Exercise Id speichern und mit count Function arbeiten
	protected function has_answers() {
		$all_items = xaseItem::where(array( 'assisted_exercise_id' => $this->assisted_exercise->getId() ))->get();
		foreach ($all_items as $item) {
			$answer_array = xaseAnswer::where(array( 'item_id' => $item->getId() ))->get();
			if (!empty($answer_array)) {
				return true;
			}
		}
		return false;
	}


	protected function addFilterItems() {
		$title = new ilTextInputGUI($this->pl->txt('title'), 'item_title');
		$this->addAndReadFilterItem($title);

		include_once("./Services/Form/classes/class.ilSelectInputGUI.php");
		$option[0] = $this->pl->txt('open');
		$option[1] = $this->pl->txt('answered');
		$option[2] = $this->pl->txt('submitted');
		$option[3] = $this->pl->txt('rated');
		if ($this->xase_settings->getModus() == self::M2 || $this->xase_settings->getModus() == self::M3) {
			$option[4] = $this->pl->txt('can_be_voted');
		}
		$status = new ilSelectInputGUI($this->pl->txt("status"), "answer_status");
		$status->setOptions($option);
		$this->addAndReadFilterItem($status);
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


	protected function getAnswer($item_id) {
		$xaseAnswer = xaseAnswer::where(array( 'item_id' => $item_id, 'user_id' => $this->dic->user()->getId() ), array(
			'item_id' => '=',
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
		/**
		 * @var $xaseItem xaseItem
		 */
		//$a_set contains the items
		$xaseItem = xaseItem::find($a_set['id']);
		if ($this->isColumnSelected('item_title')) {
			$this->tpl->setCurrentBlock("TITLE");
			$this->tpl->setVariable('TITLE', $xaseItem->getItemTitle());
			$this->tpl->parseCurrentBlock();
		}

		$xaseAnswer = $this->getAnswer($xaseItem->getId());
		if ($this->isColumnSelected('answer_status')) {
			$this->tpl->setCurrentBlock("STATUS");
			if (empty($xaseAnswer->getBody())) {
				$this->tpl->setVariable('STATUS', $this->pl->txt('open'));
			} elseif ($xaseAnswer->getAnswerStatus() == xaseAnswer::ANSWER_STATUS_ANSWERED) {
				$this->tpl->setVariable('STATUS', $this->pl->txt('answered'));
			} elseif ($xaseAnswer->getAnswerStatus() == xaseAnswer::ANSWER_STATUS_SUBMITTED) {
				$this->tpl->setVariable('STATUS', $this->pl->txt('submitted'));
			} elseif ($xaseAnswer->getAnswerStatus() == xaseAnswer::ANSWER_STATUS_RATED) {
				$this->tpl->setVariable('STATUS', $this->pl->txt('rated'));
			} elseif ($xaseAnswer->getAnswerStatus() == xaseAnswer::ANSWER_STATUS_CAN_BE_VOTED) {
				$this->tpl->setVariable('STATUS', $this->pl->txt('can_be_voted'));
			}
			$this->tpl->parseCurrentBlock();
		}

		if ($this->xase_settings->getModus() != self::M2) {

			/**
			 * @var $xasePointItem xasePoint
			 */
			$xasePointItem = xasePoint::find($xaseItem->getPointId());

			if ($this->isColumnSelected('max_points')) {
				$this->tpl->setCurrentBlock("MAXPOINTS");
				if (!empty($xasePointItem->getMaxPoints())) {
					$this->tpl->setVariable('MAXPOINTS', $xasePointItem->getMaxPoints());
				} else {
					$this->tpl->setVariable('MAXPOINTS', 0);
				}
				$this->tpl->parseCurrentBlock();
			}

			/**
			 * @var $xasePointAnswer xasePoint
			 */

			$xasePointAnswer = xasePoint::find($xaseAnswer->getPointId());

			if ($this->xase_settings->getModus() == self::M1) {
				if ($this->isColumnSelected('points_teacher')) {
					$this->tpl->setCurrentBlock("POINTS");
					if (!empty($xasePointAnswer->getPointsTeacher())) {
						$this->tpl->setVariable('POINTS', $xasePointAnswer->getPointsTeacher());
					} else {
						$this->tpl->setVariable('POINTS', 0);
					}
				}
				$this->tpl->parseCurrentBlock();
			} elseif ($this->xase_settings->getModus() == self::M3) {
				if ($this->isColumnSelected('points_teacher')) {
					$this->tpl->setCurrentBlock("POINTSTEACHER");
					if (!empty($xasePointAnswer->getPointsTeacher())) {
						$this->tpl->setVariable('POINTSTEACHER', $xasePointAnswer->getPointsTeacher());
					} else {
						$this->tpl->setVariable('POINTSTEACHER', 0);
					}
					$this->tpl->parseCurrentBlock();
				}

				if ($this->isColumnSelected('additional_points_voting')) {
					$this->tpl->setCurrentBlock("ADDITIONALPOINTSVOTING");
					if (!empty($xasePointAnswer->getAdditionalPoints())) {
						$this->tpl->setVariable('ADDITIONALPOINTSVOTING', $xasePointAnswer->getAdditionalPoints());
					} else {
						$this->tpl->setVariable('ADDITIONALPOINTSVOTING', 0);
					}
					$this->tpl->parseCurrentBlock();
				}

				if ($this->isColumnSelected('total_points')) {
					$this->tpl->setCurrentBlock("TOTALPOINTS");
					if (!empty($xasePointAnswer->getTotalPoints())) {
						$this->tpl->setVariable('TOTALPOINTS', $xasePointAnswer->getTotalPoints());
					} else {
						$this->tpl->setVariable('TOTALPOINTS', 0);
					}
					$this->tpl->parseCurrentBlock();
				}
			}

			/**
			 * @var $xaseHint xaseHint
			 */
			$xaseHint = xaseHint::where(array( 'item_id' => $xaseItem->getId() ))->get();

			/**
			 * @var $xaseAnswer xaseAnswer
			 */
			if (!empty($xaseHint)) {
				$xaseAnswer = xaseAnswer::where(array( 'item_id' => $xaseItem->getId() ))->first();
			}

			if ($this->isColumnSelected('number_of_used_hints')) {
				$this->tpl->setCurrentBlock("NUMBEROFUSEDHINTS");
				if (is_object($xaseAnswer) && !empty($xaseAnswer) && !empty($xaseAnswer->getNumberOfUsedHints())) {
					$this->tpl->setVariable('NUMBEROFUSEDHINTS', $xaseAnswer->getNumberOfUsedHints());
				} else {
					$this->tpl->setVariable('NUMBEROFUSEDHINTS', 0);
				}
				$this->tpl->parseCurrentBlock();
			}
		}

		if ($this->xase_settings->getModus() == self::M2 || $this->xase_settings->getModus() == self::M3) {
			if ($this->isColumnSelected('is_voted')) {
				$this->tpl->setCurrentBlock("ISVOTED");
				if ($this->hasUserVotedForItem($xaseItem)) {
					$this->tpl->setVariable('ISVOTED', $this->pl->txt('yes'));
				} else {
					$this->tpl->setVariable('ISVOTED', $this->pl->txt('no'));
				}
				$this->tpl->parseCurrentBlock();
			}
			if ($this->isColumnSelected('number_of_upvotings')) {
				$this->tpl->setCurrentBlock("NUMBEROFUPVOTINGS");
				if (!empty($xaseAnswer) && !empty($xaseAnswer->getNumberOfUpvotings())) {
					$this->tpl->setVariable('NUMBEROFUPVOTINGS', $xaseAnswer->getNumberOfUpvotings());
				} else {
					$this->tpl->setVariable('NUMBEROFUPVOTINGS', 0);
				}
				$this->tpl->parseCurrentBlock();
			}
		}
		$this->addActionMenu($xaseItem);
	}

	protected function initColums() {
		$all_cols = $this->getSelectableColumns();
		$number_of_columns = count($this->getSelectedColumns());
		$column_width = 100 / $number_of_columns;
		foreach ($this->getSelectedColumns() as $col) {
			$this->addColumn($all_cols[$col]['txt'], $col, $column_width);
		}
		$this->addColumn($this->pl->txt('common_actions'), '', $column_width);
	}


	/**
	 * @param $item_id
	 *
	 * @return xaseAnswer
	 */
	protected function getUserAnswerByItemId($item_id) {
		$xase_answer = xaseAnswer::where(array( 'item_id' => $item_id, 'user_id' => $this->dic->user()->getId() ))->first();

		return $xase_answer;
	}


	/**
	 * @param xaseItem $xaseItem
	 */
	protected function addActionMenu(xaseItem $xaseItem) {
		$current_selection_list = new ilAdvancedSelectionListGUI();
		$current_selection_list->setListTitle($this->pl->txt('common_actions'));
		$current_selection_list->setId('item_actions_' . $xaseItem->getId());
		$current_selection_list->setUseImages(false);

		$this->ctrl->setParameter($this->parent_obj, xaseItemGUI::ITEM_IDENTIFIER, $xaseItem->getId());
		$this->ctrl->setParameterByClass(xaseAnswerGUI::class, xaseItemGUI::ITEM_IDENTIFIER, $xaseItem->getId());
		$this->ctrl->setParameterByClass(xaseSampleSolutionGUI::class, xaseItemGUI::ITEM_IDENTIFIER, $xaseItem->getId());
		$this->ctrl->setParameterByClass(xaseItemDeleteGUI::class, xaseItemGUI::ITEM_IDENTIFIER, $xaseItem->getId());
		$this->ctrl->setParameterByClass(xaseAnswerListGUI::class, xaseItemGUI::ITEM_IDENTIFIER, $xaseItem->getId());

		$current_selection_list->addItem($this->pl->txt('answer'), xaseAnswerGUI::CMD_STANDARD, $this->ctrl->getLinkTargetByClass('xaseanswergui', xaseAnswerGUI::CMD_STANDARD));
		$xase_answer = $this->getUserAnswerByItemId($xaseItem->getId());

		if(!empty($xase_answer)) {
			if($this->xase_settings->getModus() == self::M2 || $this->xase_settings->getModus() == self::M3 && $xase_answer->getAnswerStatus() == xaseAnswer::ANSWER_STATUS_SUBMITTED || $xase_answer->getAnswerStatus() == xaseAnswer::ANSWER_STATUS_RATED || $xase_answer->getAnswerStatus() == xaseAnswer::ANSWER_STATUS_CAN_BE_VOTED) {
				$current_selection_list->addItem($this->pl->txt('view_answers'), xaseAnswerListGUI::CMD_STANDARD, $this->ctrl->getLinkTargetByClass(xaseAnswerListGUI::class, xaseAnswerListGUI::CMD_STANDARD));
			}
		}

		if (!empty($xase_answer) && $xase_answer->getAnswerStatus() == xaseAnswer::ANSWER_STATUS_RATED
			&& $this->xase_settings->getModus() != self::M2) {
			$this->ctrl->setParameterByClass(xaseAssessmentGUI::class, xaseAnswerGUI::ANSWER_IDENTIFIER, $xase_answer->getId());
			$current_selection_list->addItem($this->pl->txt('view_assessment'), xaseAssessmentGUI::CMD_VIEW_ASSESSMENT, $this->ctrl->getLinkTargetByClass('xaseassessmentgui', xaseAssessmentGUI::CMD_VIEW_ASSESSMENT));
		}
		if ($this->isSampleSolutionAvailable($this->xase_settings->getModus(), $xaseItem)) {
			$current_selection_list->addItem($this->pl->txt('view_sample_solution'), xaseSampleSolutionGUI::CMD_STANDARD, $this->ctrl->getLinkTargetByClass('xaseSampleSolutionGUI', xaseSampleSolutionGUI::CMD_STANDARD));
		}

		if (xaseItemAccess::hasWriteAccess($this->xase_settings, $xaseItem)) {
			if (!$this->has_answers()) {
				$current_selection_list->addItem($this->pl->txt('edit_task'), xaseItemGUI::CMD_EDIT, $this->ctrl->getLinkTargetByClass('xaseitemgui', xaseItemGUI::CMD_EDIT));
			}
		}

		if (xaseItemAccess::hasDeleteAccess($this->xase_settings, $xaseItem)) {
			$current_selection_list->addItem($this->pl->txt('delete_task'), xaseItemDeleteGUI::CMD_STANDARD, $this->ctrl->getLinkTargetByClass('xaseitemdeletegui', xaseItemDeleteGUI::CMD_STANDARD));
		}
		/*        if ($this->access->hasWriteAccess()) {
					$current_selection_list->addItem($this->pl->txt('edit_answer'), xaseAnswerGUI::CMD_EDIT, $this->ctrl->getLinkTargetByClass('xaseanswergui', xaseAnswerGUI::CMD_EDIT));
				}*/
		$this->tpl->setVariable('ACTIONS', $current_selection_list->getHTML());
	}

	protected function parseData() {
		$this->determineOffsetAndOrder();
		$this->determineLimit();

		$collection = xaseItem::getCollection();
		$collection->where(array( 'assisted_exercise_id' => $this->parent_obj->object->getId() ));

		$collection->leftjoin(xasePoint::returnDbTableName(), 'point_id', 'id', array( 'max_points', 'points_teacher' ));

		$collection->leftjoin(xaseAnswer::returnDbTableName(), 'id', 'item_id', array( 'number_of_used_hints', 'answer_status' ));

		$sorting_column = $this->getOrderField() ? $this->getOrderField() : 'item_title';
		$offset = $this->getOffset() ? $this->getOffset() : 0;

		$sorting_direction = $this->getOrderDirection();
		$num = $this->getLimit();

		$collection->orderBy($sorting_column, $sorting_direction);
		$collection->limit($offset, $num);

		//$collection->debug();

		foreach ($this->filter as $filter_key => $filter_value) {
			switch ($filter_key) {
				case 'item_title':
					$collection->where(array( $filter_key => '%' . $filter_value . '%' ), 'LIKE');
					break;
				case 'answer_status':
					if (!empty($filter_value)) {
						$collection->where(array( xaseAnswer::returnDbTableName() . '.' . $filter_key => '%' . $filter_value . '%' ), 'LIKE');
						break;
					}
			}
		}
		$this->setData($collection->getArray());
	}


	public function getSelectableColumns() {
		$cols["item_title"] = array(
			"txt" => $this->pl->txt("title"),
			"default" => true
		);
		$cols["answer_status"] = array(
			"txt" => $this->pl->txt("status"),
			"default" => true
		);
		if ($this->xase_settings->getModus() == self::M1 || $this->xase_settings->getModus() == self::M3) {
			$cols["max_points"] = array(
				"txt" => $this->pl->txt("max_points"),
				"default" => true
			);
			$cols["number_of_used_hints"] = array(
				"txt" => $this->pl->txt("number_of_used_hints"),
				"default" => true
			);
		}
		if ($this->xase_settings->getModus() == self::M1) {
			$cols["points_teacher"] = array(
				"txt" => $this->pl->txt("points"),
				"default" => true
			);
		} elseif ($this->xase_settings->getModus() == self::M3) {
			$cols["points_teacher"] = array(
				"txt" => $this->pl->txt("points_teacher"),
				"default" => true
			);
			$cols["additional_points_voting"] = array(
				"txt" => $this->pl->txt("additional_points_voting"),
				"default" => true
			);
			$cols["total_points"] = array(
				"txt" => $this->pl->txt("total_points"),
				"default" => true
			);
		}
		if ($this->xase_settings->getModus() == self::M2 || $this->xase_settings->getModus() == self::M3) {
			$cols["is_voted"] = array(
				"txt" => $this->pl->txt("is_voted"),
				"default" => true
			);
			$cols["number_of_upvotings"] = array(
				"txt" => $this->pl->txt("number_of_upvotings"),
				"default" => true
			);
		}

		return $cols;
	}


	public static function getMaxAchievablePoints($assisted_exercise_id, $modus) {
		$items = xaseItem::where(array( 'assisted_exercise_id' => $assisted_exercise_id ))->get();
		$max_achievable_points = 0;
		if ($modus != self::M2) {
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
		$items = xaseItem::where(array( 'assisted_exercise_id' => $assisted_exercise_object->getId() ))->get();
		$item_ids = [];
		foreach ($items as $item) {
			$item_ids[] = $item->getId();
		}
		if (empty($item_ids)) {
			return NULL;
		} else {
			return xaseAnswer::where(array( 'user_id' => $dic->user()->getId(), 'item_id' => $item_ids ), array(
				'user_id' => '=',
				'item_id' => 'IN'
			))->get();
		}
	}

	static function getAllUserAnswersFromAssistedExercise($all_items_assisted_exercise, $dic, $user) {
		foreach ($all_items_assisted_exercise as $item_assisted_exercise) {
			$all_items_assisted_exercise_ids[] = $item_assisted_exercise->getId();
		}
		$all_items_assisted_exercise_ids_string = implode(', ', $all_items_assisted_exercise_ids);
		$statement = $dic->database()->query("SELECT * FROM ilias.rep_robj_xase_answer where user_id = " . $user->getId()
			. " AND item_id IN ($all_items_assisted_exercise_ids_string)");

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
		$all_items_assisted_exercise = xaseItem::where(array( 'assisted_exercise_id' => $this->assisted_exercise->getId() ))->get();

		if (empty($all_items_assisted_exercise)) {
			return false;
		}

		//$answers_from_current_user = xaseAnswer::where(array('user_id' => $this->dic->user()->getId(), 'item_id' => $this->xase_item->getId()))->get();
		$answers_from_current_user = self::getAllUserAnswersFromAssistedExercise($all_items_assisted_exercise, $this->dic, $this->dic->user());

		foreach ($all_items_assisted_exercise as $item) {
			$all_item_ids[] = $item->getId();
		}

		foreach ($answers_from_current_user as $answer) {
			if (is_array($answer)) {
				$item_ids_from_answers[] = $answer['item_id'];
			} else {
				$item_ids_from_answers[] = $answers_from_current_user['item_id'];
				break;
			}
		}

		if (is_array($all_item_ids) && is_array($item_ids_from_answers)) {
			$not_answered_items = array_diff($all_item_ids, $item_ids_from_answers);
		}

		if (empty($not_answered_items) && is_array($item_ids_from_answers)) {
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
			if ($answer_from_current_user_object->getAnswerStatus() == xaseAnswer::ANSWER_STATUS_SUBMITTED
				|| $answer_from_current_user_object->getAnswerStatus() == xaseAnswer::ANSWER_STATUS_RATED) {
				return true;
			}
		}

		return false;
	}


	protected function isDisposalDateExpired() {
		$current_date = date('Y-m-d h:i:s', time());
		$current_date_datetime = DateTime::createFromFormat('Y-m-d H:i:s', $current_date);
		$disposal_date_datetime = DateTime::createFromFormat('Y-m-d H:i:s', $this->mode_settings->getDisposalDate());
		if (($disposal_date_datetime->getTimestamp() < $current_date_datetime->getTimestamp())
			|| $this->mode_settings->getDisposalDate() == "0000-00-00 00:00:00") {
			return false;
		} else {
			return true;
		}
	}


	/*
	 * 1) Mode 1
	 *  a)Nach Abschluss der Übung
	 *  b)Ab definiertem Datum
	 * 2) Mode 2 keine Musterlösung
	 * 3) Mode 3
	 *      die Schüler haben die Musterlösung sobald Sie das Voting abgegeben haben
	 */
	protected function isSampleSolutionAvailable($mode, $xase_item) {

		$xase_sample_solution = xaseSampleSolution::where(array( 'id' => $xase_item->getSampleSolutionId() ))->first();
		if (empty($xase_sample_solution)) {
			return false;
		} else {
			if ($mode == self::M1) {
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
			} elseif ($mode == self::M2) {
				return false;
			} else {
				if ($this->hasUserVotedForItem($xase_item)) {
					return true;
				} else {
					return false;
				}
			}
		}
	}


	protected function hasUserVotedForAllItems() {
		$items = xaseItem::where(array( 'assisted_exercise_id' => $this->assisted_exercise->getId() ))->get();
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
	protected function hasUserVotedForItem(xaseItem $xaseItem) {
		$answers_for_current_item = xaseAnswer::where(array( 'item_id' => $xaseItem->getId() ))->get();
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


	protected function getModeSettings($mode) {
		if ($mode == self::M1) {
			return xaseSettingsM1::where([ 'settings_id' => $this->xase_settings->getId() ])->first();
		} elseif ($mode == self::M3) {
			return xaseSettingsM3::where([ 'settings_id' => $this->xase_settings->getId() ])->first();
		} else {
			return xaseSettingsM2::where([ 'settings_id' => $this->xase_settings->getId() ])->first();
		}
	}
}