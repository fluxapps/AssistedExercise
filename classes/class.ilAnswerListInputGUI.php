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
 * @author: Benjamin Seglias   <bs@studer-raimann.ch>
 * @ingroup ServicesForm
 */

class ilAnswerListInputGUI extends ilFormPropertyGUI
{
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
    protected $answer;
    protected $comment;
    protected $comments = [];
    protected $values = [];
    protected $existing_answer_data = [];
    protected $existing_comment_data = [];
    protected $existing_voting_data = [];
    protected $total_upvotings;
    protected $votings = [];
    protected $upvotings = [];
    //TODO check if necessary
    protected $number_of_comments;

    /**
     * Constructor
     *
     * @param	string	$a_title	Title
     * @param	string	$a_postvar	Post Variable
     */
    function __construct($a_title = "", $a_postvar = "")
    {
        global $DIC;
        $this->dic = $DIC;
        $this->pl = ilAssistedExercisePlugin::getInstance();
        $this->answer = new ilNonEditableValueGUI("", "answer[]");
        $this->comment = new ilNonEditableValueGUI("", "comment[]");

        $DIC->ui()->mainTemplate()->addCss("./Customizing/global/plugins/Services/Repository/RepositoryObject/AssistedExercise/templates/default/less/answer_list.css");

        parent::__construct();
    }

    /**
     * @return bool
     */
    public function checkInput()
    {
        $has_voted = false;
        foreach ($_POST['answer']['is_voted_by_current_user'] as $data) {

            if ($data == 1) {
                $has_voted = true;
                continue;
            }
        }
        return $has_voted;
    }

    /**
     * Set value by array
     *
     * @param	array	$a_values	value array
     */
    function setValueByArray($a_values)
    {
        if (is_array($a_values['answer'])) {
            foreach ($a_values['answer'] as $id => $data) {
                $this->values[$id]['is_template'] = $data["is_template"];
                $this->values[$id]['label'] = $data["label"];
                $this->values[$id]['lvl_1_hint'] = $data["lvl_1_hint"];
                $this->values[$id]['lvl_1_minus_points'] = $data["lvl_1_minus_points"];
                $this->values[$id]['lvl_2_hint'] = $data["lvl_2_hint"];
                $this->values[$id]['lvl_2_minus_points'] = $data["lvl_2_minus_points"];
            }
        }
    }

    function hasUserVotedForAnswer() {
        $voting = xaseVoting::where(array('user_id' => $this->dic->user()->getId(), 'answer_id' => $this->xase_answer->getId()))->first();
        if(empty($voting)) {
            return false;
        }
        return true;
    }

    function getCommentsForAnswer() {
        $comments = xaseComment::where(array('answer_id' => $this->xase_answer->getId()))->get();
        return $comments;
    }

    /**
     * Insert property html
     */
    function insert($a_tpl) {

        // hint[*id*][.....]

        $tpl = new ilTemplate("tpl.answer_list.html", true, true, "Customizing/global/plugins/Services/Repository/RepositoryObject/AssistedExercise");

        $tpl->setCurrentBlock("item");
        //$tpl->setVariable("ITEM_LABEL", $this->pl->txt("task_label"));
        $tpl->setVariable("ITEM", $this->xase_item->getTask());
        $tpl->parseCurrentBlock();

        $tpl->setVariable("ANSWER_FORM_ID", $this->xase_answer->getId());
        if($this->hasUserVotedForAnswer()) {
            $tpl->setVariable("IS_VOTED", 1);
        } else {
            $tpl->setVariable("IS_VOTED", 0);
        }

        $tpl->setVariable("ANSWER_ID", $this->xase_answer->getId());

            $tpl->setCurrentBlock("voting");
            if(!empty($this->xase_answer->getNumberOfUpvotings())) {
                $tpl->setVariable("NUMBEROFUPVOTINGS", $this->xase_answer->getNumberOfUpvotings());
            } else {
                $tpl->setVariable("NUMBEROFUPVOTINGS", 0);
            }
            $tpl->setVariable("VOTE_ERROR_TEXT", $this->pl->txt("vote_error_text"));
            $tpl->parseCurrentBlock();

        $tpl->setCurrentBlock("answer");
        $this->answer->setValue($this->xase_answer->getBody());
        $tpl->setVariable("ANSWER", $this->answer->render());
        $tpl->parseCurrentBlock();

        $tpl->setCurrentBlock("comment_counter");
        $tpl->setVariable("NUMBER_OF_COMMENTS", count($this->comments));
        if(count($this->comments) >= 2) {
            $tpl->setVariable("COMMENT_TEXT", $this->pl->txt('comments'));
        } else {
            $tpl->setVariable("COMMENT_TEXT", $this->pl->txt('comment'));
        }
        $tpl->parseCurrentBlock();

        $tpl->setCurrentBlock("comment_wrapper");

        if(empty($this->comments)) {
            $this->comment->setValue("");
            $tpl->setCurrentBlock("comment");
            $tpl->setVariable("COMMENT_ID", "1");
            $tpl->setVariable("COMMENT", $this->comment->render());
            $tpl->parseCurrentBlock();

        } else {
            foreach($this->comments as $comment) {

                $this->comment->setValue($comment->getBody());
                $tpl->setCurrentBlock("comment");
                $tpl->setVariable("COMMENT_ID", $comment->getId());
                $tpl->setVariable("COMMENT", $this->comment->render());
                $tpl->parseCurrentBlock();
            }
        }

        $tpl->setCurrentBlock("comment_wrapper");
        $tpl->setVariable("CREATE_COMMENT_LINK_TEXT", $this->pl->txt('add_comment'));
        $tpl->setVariable("CREATE_COMMENT_FORM_LABEL", $this->pl->txt('add_new_comment'));
        $tpl->setVariable("CREATE_COMMENT_FORM_ERROR_MESSAGE", $this->pl->txt('create_comment_form_error_message'));
        $tpl->setVariable("COMMENT_SAVE_TEXT", $this->pl->txt('save'));
        $tpl->setVariable("COMMENT_DISCARD_TEXT", $this->pl->txt('discard_comment'));
        $tpl->parseCurrentBlock();

        if (!empty($this->getExistingAnswerData())) {
            foreach ($this->getExistingAnswerData() as $answer_data) {
                $tpl->setCurrentBlock("existing_answer_data");
                $tpl->setVariable("CONTENT_ANSWER", htmlentities(json_encode($answer_data, JSON_UNESCAPED_UNICODE)));
                $tpl->parseCurrentBlock();

                if (!empty($this->getExistingCommentData())) {
                    foreach ($this->getExistingCommentData() as $comment_data) {
                            $tpl->setCurrentBlock("existing_comment_data");
                            $tpl->setVariable("CONTENT_COMMENT", htmlentities(json_encode($comment_data, JSON_UNESCAPED_UNICODE)));
                            $tpl->parseCurrentBlock();
                    }
                }
                if(!empty($this->getExistingVotingData())) {
                    foreach ($this->getExistingVotingData() as $voting) {
                            $tpl->setVariable("VOTING_DATA", htmlentities(json_encode($voting, JSON_UNESCAPED_UNICODE)));
                    }
                }

            }
        }

        $a_tpl->setCurrentBlock("prop_generic");
        //$a_tpl->setVariable("PROP_GENERIC", $tpl->get().$tpl->get().$tpl->get().$tpl->get());
        $a_tpl->setVariable("PROP_GENERIC", $tpl->get());
        $a_tpl->parseCurrentBlock();

        if(!empty($_POST)) {

            foreach ($_POST['answer'] as $id => $data) {
               if(!$this->checkInput()) {
                   ilUtil::sendFailure($this->pl->txt("msg_vote_for_at_least_one_answer"));
               }

                $tpl = new ilTemplate("tpl.answer_list.html", true, true, "Customizing/global/plugins/Services/Repository/RepositoryObject/AssistedExercise");

                $tpl->setCurrentBlock("item");
                //$tpl->setVariable("ITEM_LABEL", $this->pl->txt("task_label"));
                $tpl->setVariable("ITEM", $this->xase_item->getTask());
                $tpl->parseCurrentBlock();

                if($this->xase_answer->getAnswerStatus() == xaseAnswer::ANSWER_STATUS_SUBMITTED || $this->xase_answer->getAnswerStatus() == xaseAnswer::ANSWER_STATUS_RATED || $this->xase_answer->getAnswerStatus() == xaseAnswer::ANSWER_STATUS_M2_CAN_BE_VOTED) {
                    $tpl->setVariable("ANSWER_FORM_ID", $this->xase_answer->getId());
                    if($this->hasUserVotedForAnswer()) {
                        $tpl->setVariable("IS_VOTED", 1);
                    } else {
                        $tpl->setVariable("IS_VOTED", 0);
                    }

                    $tpl->setVariable("ANSWER_ID", $this->xase_answer->getId());

                    $tpl->setCurrentBlock("answer");
                    $this->answer->setValue($this->xase_answer->getBody());
                    $tpl->setVariable("ANSWER", $this->answer->render());
                    $tpl->parseCurrentBlock();

                    $tpl->setCurrentBlock("comment_counter");
                    $tpl->setVariable("NUMBER_OF_COMMENTS", count($this->comments));
                    if(count($this->comments) >= 2) {
                        $tpl->setVariable("COMMENT_TEXT", $this->pl->txt('comments'));
                    } else {
                        $tpl->setVariable("COMMENT_TEXT", $this->pl->txt('comment'));
                    }
                    $tpl->parseCurrentBlock();

                    $tpl->setCurrentBlock("comment_wrapper");

                    foreach($this->comments as $comment) {

                        $this->comment->setValue($comment->getBody());
                        $tpl->setCurrentBlock("comment");
                        $tpl->setVariable("COMMENT_ID", $comment->getId());
                        $tpl->setVariable("COMMENT", $this->comment->render());
                        $tpl->parseCurrentBlock();
                    }

                    $tpl->setCurrentBlock("comment_wrapper");
                    $tpl->setVariable("CREATE_COMMENT_LINK_TEXT", $this->pl->txt('add_comment'));
                    $tpl->setVariable("CREATE_COMMENT_FORM_LABEL", $this->pl->txt('add_new_comment'));
                    $tpl->setVariable("CREATE_COMMENT_FORM_ERROR_MESSAGE", $this->pl->txt('create_comment_form_error_message'));
                    $tpl->setVariable("COMMENT_SAVE_TEXT", $this->pl->txt('save'));
                    $tpl->setVariable("COMMENT_DISCARD_TEXT", $this->pl->txt('discard_comment'));
                    $tpl->parseCurrentBlock();

/*                    if (!empty($this->getExistingAnswerData())) {
                        foreach ($this->getExistingAnswerData() as $answer_data) {
                            $tpl->setCurrentBlock("existing_answer_data");
                            $tpl->setVariable("CONTENT_ANSWER", htmlentities(json_encode($answer_data, JSON_UNESCAPED_UNICODE)));
                            $tpl->parseCurrentBlock();

                            if (!empty($this->getExistingCommentData())) {
                                foreach ($this->getExistingCommentData() as $comment_data) {
                                    $tpl->setCurrentBlock("existing_comment_data");
                                    $tpl->setVariable("CONTENT_COMMENT", htmlentities(json_encode($comment_data, JSON_UNESCAPED_UNICODE)));
                                    $tpl->parseCurrentBlock();
                                }
                            }
                            if(!empty($this->getExistingVotingData())) {
                                foreach ($this->getExistingVotingData() as $voting) {
                                    $tpl->setVariable("VOTING_DATA", htmlentities(json_encode($voting, JSON_UNESCAPED_UNICODE)));
                                }
                            }

                        }
                    }*/
                }

                $a_tpl->setCurrentBlock("prop_generic");
                //$a_tpl->setVariable("PROP_GENERIC", $tpl->get().$tpl->get().$tpl->get().$tpl->get());
                $a_tpl->setVariable("PROP_GENERIC", $tpl->get());
                $a_tpl->parseCurrentBlock();
            }
        }
    }

    /**
     * @return mixed
     */
    public function getXaseItem()
    {
        return $this->xase_item;
    }

    /**
     * @param mixed $xase_item
     */
    public function setXaseItem($xase_item)
    {
        $this->xase_item = $xase_item;
    }

    /**
     * @return ilNonEditableValueGUI
     */
    public function getAnswer()
    {
        return $this->answer;
    }

    /**
     * @param ilNonEditableValueGUI $answer
     */
    public function setAnswer($answer)
    {
        $this->answer = $answer;
    }

    /**
     * @return ilNonEditableValueGUI
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * @param ilNonEditableValueGUI $comment
     */
    public function setComment($comment)
    {
        $this->comment = $comment;
    }

    /**
     * @return array
     */
    public function getValues()
    {
        return $this->values;
    }

    /**
     * @param array $values
     */
    public function setValues($values)
    {
        $this->values = $values;
    }

    /**
     * @return array
     */
    public function getExistingAnswerData()
    {
        return $this->existing_answer_data;
    }

    /**
     * @param array $existing_answer_data
     */
    public function setExistingAnswerData($existing_answer_data)
    {
        $this->existing_answer_data = $existing_answer_data;
    }

    /**
     * @return array
     */
    public function getExistingCommentData()
    {
        return $this->existing_comment_data;
    }

    /**
     * @param array $existing_comment_data
     */
    public function setExistingCommentData($existing_comment_data)
    {
        $this->existing_comment_data = $existing_comment_data;
    }

    /**
     * @return array
     */
    public function getExistingVotingData()
    {
        return $this->existing_voting_data;
    }

    /**
     * @param array $existing_voting_data
     */
    public function setExistingVotingData($existing_voting_data)
    {
        $this->existing_voting_data = $existing_voting_data;
    }

    /**
     * @return mixed
     */
    public function getTotalUpvotings()
    {
        return $this->total_upvotings;
    }

    /**
     * @param mixed $total_upvotings
     */
    public function setTotalUpvotings($total_upvotings)
    {
        $this->total_upvotings = $total_upvotings;
    }

    /**
     * @return array
     */
    public function getUpvotings()
    {
        return $this->upvotings;
    }

    /**
     * @param array $upvotings
     */
    public function setUpvotings($upvotings)
    {
        $this->upvotings = $upvotings;
    }

    /**
     * @return xaseAnswer
     */
    public function getXaseAnswer()
    {
        return $this->xase_answer;
    }

    /**
     * @param xaseAnswer $xase_answer
     */
    public function setXaseAnswer($xase_answer)
    {
        $this->xase_answer = $xase_answer;
    }

    /**
     * @return xaseComment
     */
    public function getXaseComment()
    {
        return $this->xase_comment;
    }

    /**
     * @param xaseComment $xase_comment
     */
    public function setXaseComment($xase_comment)
    {
        $this->xase_comment = $xase_comment;
    }

    /**
     * @return array
     */
    public function getComments()
    {
        return $this->comments;
    }

    /**
     * @param array $comments
     */
    public function setComments($comments)
    {
        $this->comments = $comments;
    }

    /**
     * @return array
     */
    public function getVotings()
    {
        return $this->votings;
    }

    /**
     * @param array $votings
     */
    public function setVotings($votings)
    {
        $this->votings = $votings;
    }

    /**
     * @return mixed
     */
    public function getNumberOfComments()
    {
        return $this->number_of_comments;
    }

    /**
     * @param mixed $number_of_comments
     */
    public function setNumberOfComments($number_of_comments)
    {
        $this->number_of_comments = $number_of_comments;
    }

}