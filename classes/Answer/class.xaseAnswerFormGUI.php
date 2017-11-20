<?php
/**
 * Class xaseAnswerFormGUI
 *
 * @author: Benjamin Seglias   <bs@studer-raimann.ch>
 */

require_once('./Services/Form/classes/class.ilPropertyFormGUI.php');

class xaseAnswerFormGUI extends ilPropertyFormGUI {

	/**
	 * @var xaseQuestionGUI
	 */
	protected $parent_gui;
	/**
	 * @var ilObjAssistedExerciseFacade
	 */
	protected $obj_facade;
	/**
	 * @var xaseAnswer
	 */
	protected $obj_answer;




	/**
	 * @var ilCheckboxInputGUI
	 */
	protected $toogle_hint_checkbox;
	/**
	 * @var int
	 */
	protected $mode;


	public function __construct(xaseAnswerGUI $xase_answer_gui) {

		$this->obj_facade = ilObjAssistedExerciseFacade::getInstance($_GET['ref_id']);
		$this->obj_answer = xaseAnswer::findOrGetInstance($_GET['answer_id']);
		if($this->obj_answer->getQuestionId() == 0) {
			$this->obj_answer->setQuestionId($_GET['question_id']);
		}
		
		$this->parent_gui = $xase_answer_gui;

		//$this->only_read = $only_read;

		//TODO Refactor
		/*if ($this->obj_answer->getAnswerStatus() == xaseAnswer::ANSWER_STATUS_CAN_BE_VOTED
			|| $this->obj_answer->getAnswerStatus() == xaseAnswer::ANSWER_STATUS_RATED
			|| $this->obj_answer->getAnswerStatus() == xaseAnswer::ANSWER_STATUS_CAN_BE_VOTED) {
			$this->only_read = true;
		}*/

		parent::__construct();

		$this->obj_facade->getTpl()->addJavaScript('./Customizing/global/plugins/Services/Repository/RepositoryObject/AssistedExercise/templates/js/tooltip.js');
		$this->initForm();
	}

/*
	protected function getAnswer() {
		$xaseAnswer = xaseAnswer::where(array(
			'question_id' => $this->obj_answer->returnQuestion()->getId(),
			'user_id' => $this->dic->user()->getId()
		), array( 'question_id' => '=', 'user_id' => '=' ))->first();
		if (empty($xaseAnswer)) {
			$xaseAnswer = new xaseAnswer();
		}

		return $xaseAnswer;
	}*/


	public function initForm() {
		$this->setTarget('_top');
		$this->obj_facade->getCtrl()->setParameter($this->parent_gui, xaseQuestionGUI::ITEM_IDENTIFIER, $_GET['question_id']);
		$this->setFormAction($this->obj_facade->getCtrl()->getFormAction($this->parent_gui));
		$this->setTitle($this->obj_facade->getLanguageValue('answer_task') . " " . $this->obj_answer->returnQuestion()->getTitle());

		$this->initTaskInput();


		$item = new ilNonEditableValueGUI($this->obj_facade->getLanguageValue('max_points'));
		$item->setValue($this->obj_answer->returnQuestion()->getMaxPoints());
		$this->addItem($item);
			


		$answer = new ilTextAreaInputGUI($this->obj_facade->getLanguageValue('answer'), 'answer');
		$answer->setRequired(true);
		$answer->setRows(10);
		$this->addItem($answer);

		//Bewertung Schwierigkeitsgrad
		$item = new ilSelectInputGUI($this->obj_facade->getLanguageValue('severity'),'severity');
		$item->setInfo($this->obj_facade->getLanguageValue('severity_info'));
		$item->setRequired(true);
			$arr_options = array();

			$arr_options[''] = '-';
			for ($i = xaseQuestion::SEVERITY_RATING_FROM; $i <= xaseQuestion::SEVERITY_RATING_TO; $i++) {
				$arr_options[$i] = $i;
			}
		$item->setOptions($arr_options);
		$this->addItem($item);

		
		$item = new ilNonEditableValueGUI($this->obj_facade->getLanguageValue('answered_by'));
		$item->setValue(ilObjUser::_lookupFullname($this->obj_answer->getUserId()));
		$this->addItem($item);

		$item = new ilNonEditableValueGUI($this->obj_facade->getLanguageValue('number_of_upvotings'));
		$item->setValue($this->obj_answer->returnNumberOfUpvotings());
		$this->addItem($item);

		$button = $this->getCommentButton($this->obj_answer->getId());
		$item = new ilCustomInputGUI('');
		$item->setHtml($button->getToolbarHTML());
		$this->addItem($item);
		

		//if ($this->mode == 1 || $this->mode == 3) {

			//$this->initHintData();

			//$this->initHiddenUsedHintsInput();
		//}




		$this->addCommandButton(xaseAnswerGUI::CMD_CANCEL, $this->obj_facade->getLanguageValue("cancel"));
	}


	protected function replace_hint_identifiers_with_glyphs() {
		preg_match_all('/\[hint (\d+)\]/i', $this->obj_answer->returnQuestion()->getQuestiontext(), $hint_matches);

		$hint_numbers = $hint_matches[1];

		$replacement_array = [];
		foreach ($hint_numbers as $hint_number) {

			$this->obj_facade->getCtrl()->setParameterByClass('xaseHintGUI','question_id',$this->obj_answer->getQuestionId());
			$this->obj_facade->getCtrl()->setParameterByClass('xaseHintGUI','answer_id',$this->obj_answer->getId());
			$this->obj_facade->getCtrl()->setParameterByClass('xaseHintGUI','hint_number',$hint_number);

			$link = $this->obj_facade->getCtrl()->getLinkTargetByClass(array('ilObjAssistedExerciseGUI', 'xaseAnswerGUI', 'xaseHintGUI'));



			$replacement_array[] = <<<EOT
 <a href="{$link}" data-hint-id="{$hint_number}" class="hint-popover-link"><span class="glyphicon glyphicon-exclamation-sign"></span></a> 
EOT;
		}
		preg_match_all('/\[\/hint\]/', $this->obj_answer->returnQuestion()->getQuestiontext(), $hint_delimiter_matches);

		foreach ($hint_delimiter_matches as &$hint_delimiter) {
			foreach ($hint_delimiter as $key => $hint_delimiter_string) {
				$hint_delimiter_string = str_replace("/", "\/", $hint_delimiter_string);
				$hint_delimiter_string = str_replace("[", "/\[", $hint_delimiter_string);
				$hint_delimiter[$key] = str_replace("]", "\]/", $hint_delimiter_string);
			}
		}

		$task_text_with_glyphicons = preg_replace($hint_delimiter_matches[0], $replacement_array, $this->obj_answer->returnQuestion()->getQuestiontext(), 1);

		$task_text_with_glyphicons_cleaned = preg_replace('/\[hint (\d+)\]/i', "", $task_text_with_glyphicons);

		return $task_text_with_glyphicons_cleaned;
	}


	/*
	 * //TODO check if this method is necessary
	 * this method is used after data is sent via post
	 */
	public function replace_gaps_with_glyphs() {
		preg_match_all('/h(\d+)/g', $this->obj_answer->returnQuestion()->getQuestiontext(), $hint_matches);

		$hint_numbers = $hint_matches[1];

		$replacement_array = [];
		foreach ($hint_numbers as $hint_number) {
			$replacement_array[] = <<<EOT
 <a href="#" data-hint-id="{$hint_number}" class="hint-popover-link"><span class="glyphicon glyphicon-exclamation-sign"></span></a> 
EOT;
		}
		$task_text_with_glyphicons = preg_replace('[\s]', $replacement_array, $this->obj_answer->returnQuestion()->getQuestiontext(), 1);

		return $task_text_with_glyphicons;
	}


	protected function initTaskInput() {
		$ta = new ilNonEditableValueGUI($this->obj_facade->getLanguageValue('task'), 'question_text', true);

		$test_text_and_html = $this->replace_hint_identifiers_with_glyphs();
		$ta->setValue($test_text_and_html);

		$this->addItem($ta);
	}



	public function fillForm() {
		if ($this->mode == 1 || $this->mode == 3) {
			$array = array(
				'task' => $this->replace_hint_identifiers_with_glyphs(),
				'show_hints' => $this->obj_answer->getShowHints(),
				'answer' => $this->obj_answer->getAnswertext(),
				'severity' => $this->obj_answer->getQuestionSeverityRating(),
			);
		} else {
			$array = array(
				'task' => $this->obj_answer->returnQuestion()->getQuestiontext(),
				'answer' => $this->obj_answer->getAnswertext(),
				'severity' => $this->obj_answer->getQuestionSeverityRating(),
			);
		}
		$this->setValuesByArray($array);
	}


	/*
	 * This method is used to fill the task input if the form was sent with invalid data.
	 */
	public function fillTaskInput() {
		if ($this->mode == 1 || $this->mode == 3) {
			$array = array(
				'task' => $this->replace_hint_identifiers_with_glyphs(),
			);
		} else {
			$array = array(
				'task' => $this->obj_answer->returnQuestion()->getQuestiontext(),
			);
		}
		$this->setValuesByArray($array);
	}



	/**
	 * @return bool
	 */
	public function fillObject($status) {
		if (!$this->checkInput()) {
			return false;
		}
		$this->obj_answer->setUserId($this->obj_facade->getUser()->getId());
		$this->obj_answer->setQuestionId($this->obj_answer->returnQuestion()->getId());
		$this->obj_answer->setShowHints($this->getInput('show_hints'));
		$this->obj_answer->setAnswerStatus($status);

		$array_sumitted_states = array(xaseAnswer::ANSWER_STATUS_CAN_BE_VOTED, xaseAnswer::ANSWER_STATUS_CAN_BE_VOTED);
		if(in_array($status, $array_sumitted_states)) {
			$now = new ilDateTime(time(),IL_CAL_UNIX);
			$this->obj_answer->setSubmissionDate($now->get(IL_CAL_DATETIME));
		}

		$this->obj_answer->setAnswertext($this->getInput('answer'));
		$this->obj_answer->setQuestionSeverityRating($this->getInput('severity'));
		$this->obj_answer->store();

		return true;
	}


	/**
	 * @return bool|string
	 */
	public function updateObject($status = xaseAnswer::ANSWER_STATUS_ANSWERED) {
		if (!$this->fillObject($status)) {
			return false;
		}

		return true;
	}

	//TODO Rector. Only one Method. You find this method also at. caseVotingFormGUI
	/**
	 * @param int $answer_id
	 *
	 * @return ilLinkButton
	 */
	protected function getCommentButton($answer_id) {
		ilNoteGUI::initJavascript($this->obj_facade->getCtrl()->getLinkTargetByClass(array(
			"ilcommonactiondispatchergui",
			"ilnotegui"
		), "", "", true, false));
		ilNote::activateComments($this->obj_facade->getIlObjObId(), $answer_id, 'answer', true);
		$ajaxHash = ilCommonActionDispatcherGUI::buildAjaxHash(ilCommonActionDispatcherGUI::TYPE_REPOSITORY, $this->obj_facade->getIlObjRefId(), $this->obj_facade->getPlugin()->getPrefix(), $this->obj_facade->getIlObjObId(), 'answer', $answer_id);
		$redraw_js = "il.Object.redrawListItem(" . $this->obj_facade->getIlObjRefId(). ")";
		$on_click_js = "return " . ilNoteGUI::getListCommentsJSCall($ajaxHash, $redraw_js);

		$button = ilLinkButton::getInstance();
		$button->setUrl('#');
		$button->setOnClick($on_click_js);
		$button->setCaption($this->obj_facade->getLanguageValue('comments'),false);


		return $button;
	}
}