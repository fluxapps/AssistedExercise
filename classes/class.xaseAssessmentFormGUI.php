<?php
/**
 * Class xaseAssessmentFormGUI
 * @author: Benjamin Seglias   <bs@studer-raimann.ch>
 */

class xaseAssessmentFormGUI extends ilPropertyFormGUI
{

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

    public function __construct(xaseAssessmentGUI $xase_assessment_gui, ilObjAssistedExercise $assisted_exericse, xaseItem $xase_item)
    {
        global $DIC;
        $this->dic = $DIC;
        $this->tpl = $this->dic['tpl'];
        $this->tabs = $DIC->tabs();
        $this->ctrl = $this->dic->ctrl();
        $this->access = new ilObjAssistedExerciseAccess();
        $this->pl = ilAssistedExercisePlugin::getInstance();
        $this->assisted_exercise = $assisted_exericse;
        $this->xase_item = $xase_item;
        $this->xase_answer = $this->getAnswer();
        $this->xase_assessment = $this->getAssessment();
        $this->xase_point = $this->getAssessmentPoints();
        $this->xase_assessment = $this->getAssessment();
        $this->parent_gui = $xase_assessment_gui;
        parent::__construct();

        $this->tpl->addJavaScript('./Customizing/global/plugins/Services/Repository/RepositoryObject/AssistedExercise/templates/js/tooltip.js');
        $this->initForm();
    }

    protected function getAnswer()
    {
        $xaseAnswer = xaseAnswer::where(array('item_id' => $this->xase_item->getId(), 'user_id' => $this->dic->user()->getId()), array('item_id' => '=', 'user_id' => '='))->first();
        if (empty($xaseAnswer)) {
            $xaseAnswer = new xaseAnswer();
        }
        return $xaseAnswer;
    }

    protected function getAssessment() {
        $xaseAssessment = xaseAssessment::where(array('answer_id' => $this->xase_answer->getId(), array('item_id' => '=')))->first();
        if (empty($xaseAssessment)) {
            $xaseAssessment = new xaseAssessment();
        }
        return $xaseAssessment;
    }

    protected function getAssessmentPoints() {
        $xasePoints = xasePoint::where(array('id' => $this->xase_assessment->getPointId(), array('id' => '=')))->first();
        if (empty($xasePoints)) {
            $xasePoints = new xasePoint();
        }
        return $xasePoints;
    }

    public function initForm()
    {
        $this->setTarget('_top');
        //TODO check if necessary
        $this->ctrl->setParameter($this->parent_gui, xaseItemGUI::ITEM_IDENTIFIER, $_GET['item_id']);
        $this->setFormAction($this->ctrl->getFormAction($this->parent_gui));
        $this->setTitle($this->pl->txt('assessment_for_item') . " " . $this->xase_item->getTitle() . " " . $this->pl->txt('submitted_by') . " " . $this->dic->user()->getFullname());

        $this->toogle_hint_checkbox = new ilCheckboxInputGUI($this->pl->txt('show_used_hints'), 'show_used_hints');
        $this->toogle_hint_checkbox->setChecked(true);
        $this->toogle_hint_checkbox->setValue(1);
        $this->addItem($this->toogle_hint_checkbox);

        $item = new ilNonEditableValueGUI($this->pl->txt('item') . " " . $this->xase_item->getTitle(), 'item', true);
        $item->setValue($this->xase_item->getTask());
        $this->addItem($item);

        $answer = new ilNonEditableValueGUI($this->pl->txt('answer'), 'answer', true);
        $answer->setValue($this->xase_answer->getBody());
        $this->addItem($answer);

        $comment = new ilTextAreaInputGUI($this->pl->txt('comment'), 'comment');
        $comment->setRequired(true);
        $comment->setRows(10);
        $this->addItem($comment);

        $this->initUsedHintsForm();

        $this->addCommandButton(xaseAssessmentGUI::CMD_UPDATE, $this->pl->txt('save'));
        $this->addCommandButton(xaseAssessmentGUI::CMD_CANCEL, $this->pl->txt("cancel"));
    }

    /*
     * 1) xaseAnswer used_hints holen / alle hints die der Benutzer zur Beantwortung des Items verwendet hat
     * 2) json decode
     * 3) hint ids herauslesen
     * 4) entsprechende hints holen
     * 5) levels mit entsprechenden ids holen
     */
    protected function getHints() {
        $used_hints = json_decode($this->xase_answer->getUsedHints(), true);
        foreach($used_hints as $used_hint) {

        }
    }

    protected function getLevels($hints) {

    }

    public function createListing()
    {
        $f = $this->dic->ui()->factory();
        $renderer = $this->dic->ui()->renderer();

        /*
         * 1) get all hints which the user used for this item
         * 2) loop through the hints in the array of the unordered list
         * 3) show the following things foreach hint
         *       a) label von hint
         *       b) hint von level 1 und 2
         *
         */

        $used_hints = $this->getHints();

        $unordered = $f->listing()->descriptive(
            array
            (
                $this->pl->txt('hint') => strval(80),
                $this->pl->txt('max_achieved_points') => strval(64),
                $this->pl->txt('total_used_hints') => strval(4),
                $this->pl->txt('disposal_date') => '05.09.2017',
            )
        );

        return $renderer->render($unordered);
    }

    public function initUsedHintsForm() {
        $list = $this->createListing();
    }

    /**
     * @return bool|string
     */
    public function updateObject()
    {
        if (!$this->fillObject()) {
            return false;
        }
        return true;
    }
}