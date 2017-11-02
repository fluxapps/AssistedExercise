<?php

require_once("./Customizing/global/plugins/Services/Repository/RepositoryObject/AssistedExercise/classes/class.ilAnswerListInputGUI.php");
/**
 * Class xaseAnswerFormListGUI
 * @author: Benjamin Seglias   <bs@studer-raimann.ch>
 */

class xaseAnswerFormListGUI extends ilPropertyFormGUI
{
    /**
     * @var ilObjAssistedExercise
     */
    public $assisted_exercise;
    /**
     * @var xaseAnswerListGUI
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
     * @var xaseItem
     */
    protected $xase_item;

    public function __construct(ilObjAssistedExercise $assisted_exericse, xaseAnswerListGUI $parent_gui)
    {
        global $DIC;
        $this->dic = $DIC;
        $this->tpl = $this->dic['tpl'];
        $this->tabs = $DIC->tabs();
        $this->ctrl = $this->dic->ctrl();
        $this->access = new ilObjAssistedExerciseAccess();
        $this->pl = ilAssistedExercisePlugin::getInstance();
        $this->assisted_exercise = $assisted_exericse;
        $this->parent_gui = $parent_gui;
        $this->xase_item = new xaseItem($_GET['item_id']);

        $this->tpl->addJavaScript('./Customizing/global/plugins/Services/Repository/RepositoryObject/AssistedExercise/templates/js/answerformlist.js');
        $this->initAnswerList();

        parent::__construct();
    }

    protected function getAnswers() {
        $answers = xaseAnswer::where(array('item_id' => $this->xase_item->getId()))->get();
        return $answers;
    }

    protected function getCommentsForAnswer($xase_answer) {
        $comments = xaseComment::where(array('answer_id' => $xase_answer->getId()))->get();
        return $comments;
    }

    protected function hasUserVoted() {
        $answers_for_current_item = xaseAnswer::where(array('item_id' => $this->xase_item->getId()))->get();
        $votings_from_current_user = xaseVoting::where(array('user_id' => $this->dic->user()->getId()))->get();
        $answers_ids = [];
        foreach($answers_for_current_item as $answer) {
            $answers_ids[] = $answer->getId();
        }
        if(!empty($votings_from_current_user)) {
            foreach($votings_from_current_user as $voting) {
                if(in_array($voting->getAnswerId(), $answers_for_current_item)) {
                    return true;
                }
            }
        }
        return false;
    }

    public function fillForm() {
        $array = array (


        );
        $this->setValuesByArray($array);
    }

    /*
     * Usage in: after answer update to check if the user gets redirected to the item table list gui or to the list of answers
     * Questions:
     * -Should the user be redirected to the list of answers in all cases? Also if he answered it for the first time?
     *  -yes for the voting after the first time
     *  -afterwards?
     *      -yes to adopt the voting
     */
    protected function is_already_answered_by_user() {
        $user_answers = xaseAnswer::where(array('item_id' => $this->xase_item->getId(), 'user_id' => $this->dic->user()->getId()))->get();
        if(count($user_answers) > 0) {
            return true;
        }
        return false;
    }

    protected function initAnswerList() {
        $answers = $this->getAnswers();
        if(!empty($answers)) {
            $this->setFormAction($this->ctrl->getFormAction($this->parent_gui));
            $this->setTarget('_top');
            $header = new ilFormSectionHeaderGUI();
            $header->setTitle($this->pl->txt('view_answers'));
            $this->addItem( $header );

            ilUtil::sendInfo($this->pl->txt("pleas_vote_for_the_best_answer"));

            foreach($answers as $answer) {
                $answer_list_input_gui = new ilAnswerListInputGUI();
                $answer_list_input_gui->setXaseItem($this->xase_item);
                $answer_list_input_gui->setXaseAnswer($answer);
                $comments_for_answer = $this->getCommentsForAnswer($answer);
                $answer_list_input_gui->setComments($comments_for_answer);
                $this->addItem($answer_list_input_gui);
            }

            $this->addCommandButton(xaseAnswerListGUI::CMD_UPDATE, $this->pl->txt('save'));
            if($this->hasUserVoted()) {
                $this->addCommandButton(xaseAnswerListGUI::CMD_CANCEL, $this->pl->txt("cancel"));
            }
        }
    }
}