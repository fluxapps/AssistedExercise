<?php

require_once('./Services/Mail/classes/class.ilMail.php');
require_once('./Services/Mail/classes/class.ilMimeMail.php');
require_once('./Services/Form/classes/class.ilTextInputGUI.php');
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/AssistedExercise/classes/ActiveRecords/class.xaseComment.php');
require_once('./Services/Link/classes/class.ilLink.php');

/**
 * Class xaseAssessmentFormGUI
 *
 * @author       : Benjamin Seglias   <bs@studer-raimann.ch>
 * @ilCtrl_Calls xaseAssessmentFormGUI: xaseItemGUI
 */
class xaseAssessmentFormGUI extends ilPropertyFormGUI {

	const M1 = "1";
	const M2 = "2";
	const M3 = "3";
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
	 * @var xaseComment
	 */
	public $xase_comment;
	/**
	 * @var xaseSettings
	 */
	public $xase_settings;
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
	/**
	 * @var int
	 */
	protected $minus_points;
	/**
	 * @var int
	 */
	protected $max_assignable_points;
	/**
	 * @var int
	 */
	protected $is_student;
	/**
	 * @var ilHTTPS
	 */
	protected $https;
	/**
	 * @var ILIAS
	 */
	protected $ilias;


	public function __construct(xaseAssessmentGUI $xase_assessment_gui, ilObjAssistedExercise $assisted_exericse, $is_student = false) {
		global $DIC;
		$this->dic = $DIC;
		$this->tpl = $this->dic['tpl'];
		$this->tabs = $DIC->tabs();
		$this->ctrl = $this->dic->ctrl();
		$this->access = new ilObjAssistedExerciseAccess();
		$this->pl = ilAssistedExercisePlugin::getInstance();
		$this->assisted_exercise = $assisted_exericse;
		$this->xase_answer = new xaseAnswer($_GET[xaseAnswerGUI::ANSWER_IDENTIFIER]);
		$this->xase_item = $this->getItem();
		$this->xase_assessment = $this->getAssessment();
		$this->xase_point = $this->getPoints();
		$this->xase_comment = $this->getComment();
		$this->parent_gui = $xase_assessment_gui;
		$this->is_student = $is_student;
		$this->xase_settings = xaseSettings::where([ 'assisted_exercise_object_id' => $this->assisted_exercise->getId() ])->first();
		$this->https = $this->dic['https'];
		$this->ilias = $this->dic['ilias'];
		parent::__construct();

		$this->tpl->addJavaScript('./Customizing/global/plugins/Services/Repository/RepositoryObject/AssistedExercise/templates/js/assessment.js');
		$this->initForm();
	}


	protected function getAnswer() {
		$xaseAnswer = xaseAnswer::where(array(
			'item_id' => $this->xase_item->getId(),
			'user_id' => $this->dic->user()->getId()
		), array( 'item_id' => '=', 'user_id' => '=' ))->first();
		if (empty($xaseAnswer)) {
			$xaseAnswer = new xaseAnswer();
		}

		return $xaseAnswer;
	}


	protected function getItem() {
		$xase_item = xaseItem::where(array( 'id' => $this->xase_answer->getItemId() ))->first();

		return $xase_item;
	}


	protected function getAssessment() {
		$xaseAssessment = xaseAssessment::where(array( 'answer_id' => $this->xase_answer->getId() ), array( 'answer_id' => '=' ))->first();
		if (empty($xaseAssessment)) {
			$xaseAssessment = new xaseAssessment();
		}

		return $xaseAssessment;
	}


	protected function getPoints() {
		$xase_point = xasePoint::where(array( 'id' => $this->xase_answer->getPointId() ))->first();
		if (empty($xase_point)) {
			$xase_point = new xasePoint();
		}

		return $xase_point;
	}


	protected function getComment() {
		$xase_comment = xaseComment::where(array( 'answer_id' => $this->xase_answer->getId() ))->first();
		if (empty($xase_comment)) {
			$xase_comment = new xaseComment();
		}

		return $xase_comment;
	}


	public function initForm() {
		$this->setTarget('_top');
		$this->ctrl->setParameter($this->parent_gui, xaseItemGUI::ITEM_IDENTIFIER, $_GET['item_id']);
		$this->setFormAction($this->ctrl->getFormAction($this->parent_gui));

		$student_user = $this->getStudentUser();
		$this->setTitle($this->pl->txt('assessment_for_task') . " " . $this->xase_item->getItemTitle() . " " . $this->pl->txt('submitted_by') . " "
			. $student_user->getFirstName() . " " . $student_user->getLastName());

		if (!$this->is_student) {
			$this->toogle_hint_checkbox = new ilCheckboxInputGUI($this->pl->txt('show_used_hints'), 'show_used_hints');
			$this->toogle_hint_checkbox->setChecked(true);
			$this->toogle_hint_checkbox->setValue(1);
			$this->addItem($this->toogle_hint_checkbox);
		}

		$item = new ilNonEditableValueGUI($this->pl->txt('item') . " " . $this->xase_item->getItemTitle(), 'item', true);
		$item->setValue($this->xase_item->getTask());
		$this->addItem($item);

		$answer = new ilNonEditableValueGUI($this->pl->txt('answer'), 'answer', true);
		$answer->setValue($this->xase_answer->getBody());
		$this->addItem($answer);

		if (!$this->is_student) {
			$comment = new ilTextAreaInputGUI($this->pl->txt('comment'), 'comment');
			$comment->setRows(10);
		} else {
			$comment = new ilNonEditableValueGUI($this->pl->txt('comment'), 'comment');
			$comment->setValue($this->xase_comment->getBody());
		}
		$this->addItem($comment);

		$this->initUsedHintsForm();

		$this->initPointsForm();

		if (!$this->is_student) {
			$this->addCommandButton(xaseAssessmentGUI::CMD_UPDATE, $this->pl->txt('save'));
		}
		$this->addCommandButton(xaseAssessmentGUI::CMD_CANCEL, $this->pl->txt("cancel"));
	}


	public function initPointsForm() {
		$max_points = new ilNonEditableValueGUI($this->pl->txt('max_points'));
		$max_points->setValue($this->xase_point->getMaxPoints());
		$this->addItem($max_points);

		if (!$this->is_student) {
			$points_input = new ilTextInputGUI("points", "points");
			$points_input->setRequired(true);
			$this->addItem($points_input);
		} else {
			$points = new ilNonEditableValueGUI($this->pl->txt('points'));
			$points->setValue($this->xase_point->getPointsTeacher());
			$this->addItem($points);
		}

		$max_assignable_points_input = new ilNonEditableValueGUI($this->pl->txt('max_assignable_points'));
		$this->max_assignable_points = $this->xase_point->getMaxPoints() - $this->minus_points;
		$max_assignable_points_input->setValue($this->max_assignable_points);
		$this->addItem($max_assignable_points_input);
	}


	protected function checkLevel($hint_array) {
		$is_level_1 = false;
		$is_level_2 = false;
		$array_keys = array_keys($hint_array);
		foreach ($array_keys as $array_key) {
			if (is_array($array_key)) {
				if (in_array('1', $array_key)) {
					$is_level_1 = true;
				} elseif (in_array('2', $array_key)) {
					$is_level_2 = true;
				}
			} else {
				if (strpos($array_key, '1') !== false) {
					$is_level_1 = true;
				} elseif (strpos($array_key, '2') !== false) {
					$is_level_2 = true;
				}
			}
		}

		return array(
			'is_level_1' => $is_level_1,
			'is_level_2' => $is_level_2
		);
	}


	protected function getListingArray($hint_object, $check_level_array, $listing_array) {
		if ($check_level_array['is_level_1']) {
			$level_1_object = xaseLevel::where(array( 'hint_id' => $hint_object->getId(), 'hint_level' => 1 ))->first();
			$level_1_hint_data = $level_1_object->getHint();
			$level_1_minus_points = xasePoint::where(array( 'id' => $level_1_object->getPointId() ))->first();
			$level_1_minus_points_data = $level_1_minus_points->getMinusPoints();
			$this->minus_points += $level_1_minus_points_data;
		}
		if ($check_level_array['is_level_2']) {
			$level_2_object = xaseLevel::where(array( 'hint_id' => $hint_object->getId(), 'hint_level' => 2 ))->first();
			$level_2_hint_data = $level_2_object->getHint();
			$level_2_minus_points = xasePoint::where(array( 'id' => $level_2_object->getPointId() ))->first();
			$level_2_minus_points_data = $level_2_minus_points->getMinusPoints();
			$this->minus_points += $level_2_minus_points_data;
		}
		if (!empty($level_1_hint_data) || !empty($level_2_hint_data)) {
			if (!empty($level_1_hint_data) && !empty($level_2_hint_data)) {
				$listing_array[$hint_object->getLabel()] = $level_1_hint_data . " Minus Points: " . $level_1_minus_points_data . " "
					. $level_2_hint_data . " Minus Points: " . $level_2_minus_points_data;

				return $listing_array;
			}
			if (!empty($level_1_hint_data)) {
				$listing_array[$hint_object->getLabel()] = $level_1_hint_data . " Minus Points: " . $level_1_minus_points_data;
			}
			if (!empty($level_2_hint_data)) {
				$listing_array[$hint_object->getLabel()] = $level_2_hint_data . " Minus Points: " . $level_2_minus_points_data;
			}
		}

		return $listing_array;
	}


	public function createListing() {
		$f = $this->dic->ui()->factory();
		$renderer = $this->dic->ui()->renderer();

		$used_hints = json_decode($this->xase_answer->getUsedHints(), true);

		if ($used_hints === NULL) {
			$listing_array = [];
			$unordered = $f->listing()->descriptive($listing_array);

			return $renderer->render($unordered);
		}

		$hint_ids = array_keys($used_hints);
		$hint_objects = [];
		foreach ($hint_ids as $hint_id) {
			$hint_object = xaseHint::where([ 'id' => $hint_id ])->first();
			$hint_objects[] = $hint_object;
		}
		/*
		 * 1) loop through hint objects
		 * 2) set the hint label as text for the listing
		 * 3) get the hint array in the used_hints array with the id of the hint object of the current iteration
		 * 4) check which data level the hint is
		 * 5) save in a variable if it is level 1 or level 2 hint
		 * 6) retrieve the corresponding level db record with the hint id and the level number
		 * 7) set the actual hint content as text for the listing
		 * 8) retrieve the corresponding points db entry
		 * 9) set the minus points, with a short text, next to the corresponding level hint
		 */
		$listing_array = [];
		if (is_array($hint_objects)) {
			foreach ($hint_objects as $hint_object) {
				$hint_array = $used_hints[$hint_object->getId()];
				$check_level_array = $this->checkLevel($hint_array);
				$listing_array = $this->getListingArray($hint_object, $check_level_array, $listing_array);
			}
		} else {
			$hint_array = $used_hints[$hint_objects->getId()];
			$check_level_array = $this->checkLevel($hint_array);
			$listing_array = $this->getListingArray($hint_objects, $check_level_array, $listing_array);
		}

		$unordered = $f->listing()->descriptive($listing_array);

		return $renderer->render($unordered);
	}


	public function initUsedHintsForm() {

		$custom_input_gui = new ilCustomInputGUI($this->pl->txt('used_hints'), 'used_hints');
		$custom_input_gui->setHtml($this->createListing());
		$this->addItem($custom_input_gui);
	}


	public function fillForm() {
		$array = array(
			'comment' => $this->xase_comment->getBody(),
			'points' => $this->xase_point->getPointsTeacher()
		);
		$this->setValuesByArray($array, true);
	}


	public function fillObject() {
		if (!$this->checkInput()) {
			return false;
		}
		if ($_POST['points'] > $this->max_assignable_points) {
			ilUtil::sendFailure($this->pl->txt('msg_input_max_assignable_points') . " " . $this->max_assignable_points);

			return false;
		}
		$this->xase_answer->setAnswerStatus(xaseAnswer::ANSWER_STATUS_RATED);
		$this->xase_answer->setIsAssessed(1);
		$this->xase_answer->store();
		if (!empty($this->getInput('comment'))) {
			$this->xase_comment->setAnswerId($this->xase_answer->getId());
			$this->xase_comment->setBody($this->getInput('comment'));
			$this->xase_comment->store();
		}
		if (!empty($this->getInput('points'))) {
			if ($this->xase_settings->getModus() == self::M1) {
				$this->xase_point->setTotalPoints($this->getInput('points'));
			} elseif ($this->xase_settings->getModus() == self::M3) {
				$this->xase_point->setTotalPoints(intval($this->getInput('points')) + $this->xase_point->getAdditionalPoints());
			}
			$this->xase_point->setPointsTeacher($this->getInput('points'));
			$this->xase_point->store();
		}

		return true;
	}


	/**
	 * @return bool|string
	 */
	public function updateObject() {
		if (!$this->fillObject()) {
			return false;
		}

		$this->notifyUserAboutAssessment();

		return true;
	}


	protected function getStudentUser() {
		return xaseilUser::where(array( 'usr_id' => $this->xase_answer->getUserId() ))->first();
	}


	// TODO: Prüfen, ob eine Variante implementier werden muss, die direkt auf eine Bewertung führt. ilobjassistedexercisegui
	public function notifyUserAboutAssessment() {
		$protocol = $this->https->isDetected() ? 'https://' : 'http://';
		$server_url = $protocol . $_SERVER['HTTP_HOST'] . substr($_SERVER['PHP_SELF'], 0, strrpos($_SERVER['PHP_SELF'], '/')) . '/';
		$contact_address = ilMail::getIliasMailerAddress();

		$mm = new ilMimeMail();
		$mm->Subject($this->pl->txt('your_answer_of_the_task') . " " . $this->xase_item->getItemTitle() . " " . $this->pl->txt("was_assessed"));
		$mm->From($contact_address);
		$mm->To($this->getStudentUser()->getEmail());

		$assessment_url = ilLink::_getStaticLink($_GET['ref_id'], 'xase', true);

		/*        $mm->Body
				(
					str_replace
					(
						array("\\n", "\\t"),
						array("\n", "\t"),
						sprintf
						(
							$this->pl->txt('pleas_click_on_the_following_link_to_view_the_assessment'),
							$assessment_url,
							$server_url,
							$_SERVER['REMOTE_ADDR'],
							'mailto:' . $contact_address[0]
						)
					)
				);*/

		$body = $this->pl->txt('the_following_link_leads_to_the_list_view_of_the_items') . "\n" . $assessment_url . "\n"
			. $this->pl->txt('click_on_actions_view_assessment');

		$mm->Body($body);
		$mm->Send();
	}
}