<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2007 ILIAS open source, University of Cologne            |
	|                                                                             |
	| This program is free software; you can redistribute it and/or               |
	| modify it under the terms of the GNU General Public License                 |
	| as published by the Free Software Foundation; either version 2              |
	| of the License, or (at your option) any later version.                      |
	|                                                                             |
	| This program is distributed in the hope that it will be useful,             |
	| but WITHOUT ANY WARRANTY; without even the implied warranty of              |
	| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
	| GNU General Public License for more details.                                |
	|                                                                             |
	| You should have received a copy of the GNU General Public License           |
	| along with this program; if not, write to the Free Software                 |
	| Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
	+-----------------------------------------------------------------------------+
*/

/**
 * This class represents an answer in the list view of all answers
 *
 * @author  : Benjamin Seglias   <bs@studer-raimann.ch>
 * @ingroup ServicesForm
 */
class ilAnswerListInputGUI extends ilFormPropertyGUI {

	protected $dic;
	protected $xase_item;
	/**
	 * @var xaseAnswer
	 */
	protected $xase_answer;
	/**
	 * @var xaseComment
	 */
	protected $xase_comment;
	/**
	 * @var ilAssistedExercisePlugin
	 */
	protected $pl;
	protected $answer_non_editable_value_gui;
	protected $comment_non_editable_value_gui;
	protected $comments = [];
	protected $values = [];
	protected $modus;


	/**
	 * Constructor
	 *
	 * @param    string $a_title Title
	 * @param    string $a_postvar Post Variable
	 */
	function __construct($a_title = "", $a_postvar = "", $modus) {
		global $DIC;
		$this->dic = $DIC;
		$this->pl = ilAssistedExercisePlugin::getInstance();
		$this->answer_non_editable_value_gui = new ilNonEditableValueGUI("", "answer[]");
		$this->comment_non_editable_value_gui = new ilNonEditableValueGUI("", "comment[]");
		$this->modus = $modus;

		$DIC->ui()->mainTemplate()
			->addCss("./Customizing/global/plugins/Services/Repository/RepositoryObject/AssistedExercise/templates/default/less/answer_list.css");

		parent::__construct();
	}


	/**
	 * @return bool
	 */
	public function checkInput() {
		foreach ($_POST['answer'] as $id => $data) {
			if(is_array($data)) {
				if ($data['is_voted_by_current_user'] == 1 || $this->hasUserVotedForOneAnswer()) {
					return true;
				}
			}
		}
		return false;
	}


	/**
	 * Set value by array
	 *
	 * @param    array $a_values value array
	 */
	function setValueByArray($a_values) {
		if (is_array($a_values['answer']) && !empty($a_values['answer'])) {
			foreach ($a_values['answer'] as $id => $data) {
				if(is_array($data)) {
					$this->values[$id]['is_voted_by_current_user'] = $data["is_voted_by_current_user"];
					$this->values[$id]['answer_id'] = $data["answer_id"];
				}
			}
		}
		if($this->getModus() == 2) {
			if(is_array($a_values['comment_data']) && !empty($a_values['comment_data'])) {
				foreach ($a_values['comment_data'] as $answer_id => $data) {
					$this->values[$answer_id]['comments'] = $data["comments"];
				}
			}
		}
	}

	function hasUserVotedForOneAnswer() {
		$voting = xaseVoting::where(array('user_id' => $this->dic->user()->getId(), 'item_id' => $this->xase_item->getId()))->first();
		if (empty($voting)) {
			return false;
		}
		return true;
	}


	function hasUserVotedForAnswer($answer) {
		$voting = xaseVoting::where(array('user_id' => $this->dic->user()->getId(), 'answer_id' => $answer->getId()))->first();
		if (empty($voting)) {
			return false;
		}
		return true;
	}


	function getCommentsForAnswer($answer) {
		$comments = xaseComment::where(array( 'answer_id' => $answer->getId() ))->get();

		return $comments;
	}

	/**
	 * @param $a_tpl
	 */
	public function insert($a_tpl) {

		$tpl = new ilTemplate("tpl.answer_list.html", true, true, "Customizing/global/plugins/Services/Repository/RepositoryObject/AssistedExercise");

		$tpl->setVariable("ITEM", $this->xase_item->getTask());
		$item_creator = xaseUser::where(array('usr_id' => $this->xase_item->getUserId()))->first();
		$tpl->setVariable("ITEM_CREATOR", $item_creator->getFirstname() . " " . $item_creator->getLastname());

		foreach ($this->getAnswers() as $answer) {
			if ($answer->getAnswerStatus() == xaseAnswer::ANSWER_STATUS_SUBMITTED || $answer->getAnswerStatus() == xaseAnswer::ANSWER_STATUS_RATED
				|| $answer->getAnswerStatus() == xaseAnswer::ANSWER_STATUS_CAN_BE_VOTED) {
				$tpl->setCurrentBlock("answer_form");
				$tpl->setVariable("ANSWER_ID", $answer->getId());
				$tpl->setVariable("ANSWER_FORM_ID", $answer->getId());
				if ($this->hasUserVotedForAnswer($answer)) {
					$tpl->setVariable("IS_VOTED", 1);
				} else {
					$tpl->setVariable("IS_VOTED", 0);
				}
				if (!empty($answer->getNumberOfUpvotings())) {
					$tpl->setVariable("NUMBEROFUPVOTINGS", $answer->getNumberOfUpvotings());
				} else {
					$tpl->setVariable("NUMBEROFUPVOTINGS", 0);
				}
				$tpl->setVariable("VOTE_ERROR_TEXT", $this->pl->txt("vote_error_text"));

				$this->answer_non_editable_value_gui->setValue($answer->getBody());
				$tpl->setVariable("ANSWER", $this->answer_non_editable_value_gui->render());
				$answer_creator = xaseUser::where(array('usr_id' => $answer->getUserId()))->first();
				$tpl->setVariable("ANSWER_CREATOR", $answer_creator->getFirstname() . " " . $answer_creator->getLastname());

				$this->setComments($this->getCommentsForAnswer($answer));

				$tpl->setVariable("NUMBER_OF_COMMENTS", count($this->comments));
				if (count($this->comments) >= 2) {
					$tpl->setVariable("COMMENT_TEXT", $this->pl->txt('comments'));
				} else {
					$tpl->setVariable("COMMENT_TEXT", $this->pl->txt('comment'));
				}

					$tpl_comment_form = new ilTemplate("tpl.comment_form.html", true, true, "Customizing/global/plugins/Services/Repository/RepositoryObject/AssistedExercise");

					$tpl_comment_form->setCurrentBlock("comment_wrapper");

					$tpl_comment_form->setVariable("ANSWER_ID", $answer->getId());

					if (empty($this->comments)) {
						$this->comment_non_editable_value_gui->setValue("");
						$tpl_comment_form->setCurrentBlock("comment");
						$tpl_comment_form->setVariable("COMMENT_ID", "1");
						$tpl_comment_form->setVariable("COMMENT", $this->comment_non_editable_value_gui->render());
						$tpl_comment_form->parseCurrentBlock();

					} else {
						foreach ($this->comments as $comment) {
							$this->comment_non_editable_value_gui->setValue($comment->getBody());
							$tpl_comment_form->setCurrentBlock("comment");
							$tpl_comment_form->setVariable("COMMENT_ID", $comment->getId());
							$tpl_comment_form->setVariable("COMMENT", $this->comment_non_editable_value_gui->render());
							$comment_creator = xaseUser::where(array('usr_id' => $comment->getUserId()))->first();
							$tpl_comment_form->setVariable("COMMENT_CREATOR", $comment_creator->getFirstname() . " " . $comment_creator->getLastname());
							$tpl_comment_form->parseCurrentBlock();
						}
					}
					$tpl_comment_form->setCurrentBlock("comment_wrapper");
					$tpl_comment_form->setVariable("CREATE_COMMENT_LINK_TEXT", $this->pl->txt('add_comment'));
					$tpl_comment_form->setVariable("CREATE_COMMENT_FORM_LABEL", $this->pl->txt('add_new_comment'));
					$tpl_comment_form->setVariable("CREATE_COMMENT_FORM_ERROR_MESSAGE", $this->pl->txt('create_comment_form_error_message'));
					$tpl_comment_form->setVariable("COMMENT_SAVE_TEXT", $this->pl->txt('save'));
					$tpl_comment_form->setVariable("COMMENT_DISCARD_TEXT", $this->pl->txt('discard_comment'));
					$tpl_comment_form->setVariable("ANSWER_ID", $answer->getId());
					$tpl_comment_form->parseCurrentBlock();

					$tpl_comment_form->parseCurrentBlock();

					$tpl->setVariable("COMMENT_FORM", $tpl_comment_form->get());
			}
			$tpl->parseCurrentBlock('answer_form');
		}

		$a_tpl->setCurrentBlock("prop_generic");
		$a_tpl->setVariable("PROP_GENERIC", $tpl->get());
		$a_tpl->parseCurrentBlock();

		if (!empty($_POST)) {

			foreach ($_POST['answer'] as $id => $data) {
				if (!$this->checkInput()) {
					ilUtil::sendFailure($this->pl->txt("msg_vote_for_at_least_one_answer"));
				}

				$tpl = new ilTemplate("tpl.answer_list.html", true, true, "Customizing/global/plugins/Services/Repository/RepositoryObject/AssistedExercise");

				$tpl->setVariable("ITEM", $this->xase_item->getTask());
				$item_creator = xaseUser::where(array('usr_id' => $this->xase_item->getUserId()))->first();
				$tpl->setVariable("ITEM_CREATOR", $item_creator->getFirstname() . " " . $item_creator->getLastname());

				foreach ($this->getAnswers() as $answer) {
					if ($answer->getAnswerStatus() == xaseAnswer::ANSWER_STATUS_SUBMITTED || $answer->getAnswerStatus() == xaseAnswer::ANSWER_STATUS_RATED
						|| $answer->getAnswerStatus() == xaseAnswer::ANSWER_STATUS_CAN_BE_VOTED) {
						$tpl->setCurrentBlock("answer_form");
						$tpl->setVariable("ANSWER_ID", $answer->getId());
						$tpl->setVariable("ANSWER_FORM_ID", $answer->getId());
						if ($this->hasUserVotedForAnswer($answer)) {
							$tpl->setVariable("IS_VOTED", 1);
						} else {
							$tpl->setVariable("IS_VOTED", 0);
						}
						if (!empty($answer->getNumberOfUpvotings())) {
							$tpl->setVariable("NUMBEROFUPVOTINGS", $answer->getNumberOfUpvotings());
						} else {
							$tpl->setVariable("NUMBEROFUPVOTINGS", 0);
						}
						$tpl->setVariable("VOTE_ERROR_TEXT", $this->pl->txt("vote_error_text"));

						$this->answer_non_editable_value_gui->setValue($answer->getBody());
						$tpl->setVariable("ANSWER", $this->answer_non_editable_value_gui->render());
						$answer_creator = xaseUser::where(array('usr_id' => $answer->getUserId()))->first();
						$tpl->setVariable("ANSWER_CREATOR", $answer_creator->getFirstname() . " " . $answer_creator->getLastname());

						$this->setComments($this->getCommentsForAnswer($answer));

						$tpl->setVariable("NUMBER_OF_COMMENTS", count($this->comments));
						if (count($this->comments) >= 2) {
							$tpl->setVariable("COMMENT_TEXT", $this->pl->txt('comments'));
						} else {
							$tpl->setVariable("COMMENT_TEXT", $this->pl->txt('comment'));
						}

							$tpl_comment_form = new ilTemplate("tpl.comment_form.html", true, true, "Customizing/global/plugins/Services/Repository/RepositoryObject/AssistedExercise");

							$tpl_comment_form->setCurrentBlock("comment_wrapper");

							$tpl_comment_form->setVariable("ANSWER_ID", $answer->getId());

							if (empty($this->comments)) {
								$this->comment_non_editable_value_gui->setValue("");
								$tpl_comment_form->setCurrentBlock("comment");
								$tpl_comment_form->setVariable("COMMENT_ID", "1");
								$tpl_comment_form->setVariable("COMMENT", $this->comment_non_editable_value_gui->render());
								$tpl_comment_form->parseCurrentBlock();

							} else {
								foreach ($this->comments as $comment) {
									$this->comment_non_editable_value_gui->setValue($comment->getBody());
									$tpl_comment_form->setCurrentBlock("comment");
									$tpl_comment_form->setVariable("COMMENT_ID", $comment->getId());
									$tpl_comment_form->setVariable("COMMENT", $this->comment_non_editable_value_gui->render());
									$comment_creator = xaseUser::where(array('usr_id' => $comment->getUserId()))->first();
									$tpl_comment_form->setVariable("COMMENT_CREATOR", $comment_creator->getFirstname() . " " . $comment_creator->getLastname());
									$tpl_comment_form->parseCurrentBlock();
								}
							}
							$tpl_comment_form->setCurrentBlock("comment_wrapper");
							$tpl_comment_form->setVariable("CREATE_COMMENT_LINK_TEXT", $this->pl->txt('add_comment'));
							$tpl_comment_form->setVariable("CREATE_COMMENT_FORM_LABEL", $this->pl->txt('add_new_comment'));
							$tpl_comment_form->setVariable("CREATE_COMMENT_FORM_ERROR_MESSAGE", $this->pl->txt('create_comment_form_error_message'));
							$tpl_comment_form->setVariable("COMMENT_SAVE_TEXT", $this->pl->txt('save'));
							$tpl_comment_form->setVariable("COMMENT_DISCARD_TEXT", $this->pl->txt('discard_comment'));
							$tpl_comment_form->setVariable("ANSWER_ID", $answer->getId());
							$tpl_comment_form->parseCurrentBlock();

							$tpl_comment_form->parseCurrentBlock();

							$tpl->setVariable("COMMENT_FORM", $tpl_comment_form->get());
					}
					$tpl->parseCurrentBlock('answer_form');
				}

				$a_tpl->setCurrentBlock("prop_generic");
				$a_tpl->setVariable("PROP_GENERIC", $tpl->get());
				$a_tpl->parseCurrentBlock();
			}
		}
	}


	/**
	 * @return mixed
	 */
	public function getXaseItem() {
		return $this->xase_item;
	}


	/**
	 * @param mixed $xase_item
	 */
	public function setXaseItem($xase_item) {
		$this->xase_item = $xase_item;
	}


	/**
	 * @return ilNonEditableValueGUI
	 */
	public function getAnswerNoneditablevaluegui() {
		return $this->answer_non_editable_value_gui;
	}


	/**
	 * @param ilNonEditableValueGUI $answer_non_editable_value_gui
	 */
	public function setAnswerNoneditablevaluegui($answer_non_editable_value_gui) {
		$this->answer_non_editable_value_gui = $answer_non_editable_value_gui;
	}


	/**
	 * @return ilNonEditableValueGUI
	 */
	public function getCommentNoneditablevaluegui() {
		return $this->comment_non_editable_value_gui;
	}


	/**
	 * @param ilNonEditableValueGUI $comment_non_editable_value_gui
	 */
	public function setCommentNoneditablevaluegui($comment_non_editable_value_gui) {
		$this->comment_non_editable_value_gui = $comment_non_editable_value_gui;
	}


	/**
	 * @return array
	 */
	public function getValues() {
		return $this->values;
	}


	/**
	 * @param array $values
	 */
	public function setValues($values) {
		$this->values = $values;
	}

	/**
	 * @return xaseAnswer
	 */
	public function getXaseAnswer() {
		return $this->xase_answer;
	}


	/**
	 * @param xaseAnswer $xase_answer
	 */
	public function setXaseAnswer($xase_answer) {
		$this->xase_answer = $xase_answer;
	}


	/**
	 * @return xaseComment
	 */
	public function getXaseComment() {
		return $this->xase_comment;
	}


	/**
	 * @param xaseComment $xase_comment
	 */
	public function setXaseComment($xase_comment) {
		$this->xase_comment = $xase_comment;
	}


	/**
	 * @return array
	 */
	public function getComments() {
		return $this->comments;
	}


	/**
	 * @param array $comments
	 */
	public function setComments($comments) {
		$this->comments = $comments;
	}


	/**
	 * @return mixed
	 */
	public function getModus() {
		return $this->modus;
	}


	/**
	 * @param mixed $modus
	 */
	public function setModus($modus) {
		$this->modus = $modus;
	}


}