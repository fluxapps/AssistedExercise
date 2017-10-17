<?php
/**
 * Class xaseSubmissionTableGUI
 * @author: Benjamin Seglias   <bs@studer-raimann.ch>
 */

require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/AssistedExercise/classes/ActiveRecords/class.xasePoint.php');
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/AssistedExercise/classes/ActiveRecords/class.xaseHint.php');
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/AssistedExercise/classes/ActiveRecords/class.xaseAnswer.php');
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/AssistedExercise/classes/class.xaseAssessmentGUI.php');
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/AssistedExercise/classes/class.xaseAnswerGUI.php');
require_once('./Services/UIComponent/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php');
require_once('./Services/Table/classes/class.ilTable2GUI.php');
require_once('./Services/Form/classes/class.ilTextInputGUI.php');

class xaseSubmissionTableGUI extends ilTable2GUI
{
    const CMD_STANDARD = 'content';
    const CMD_ASSESSMENT_IDENTIFIER = 'assessment_id';
    const TBL_ID = 'tbl_xase_submissions';

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
     * @var xaseSubmissionGUI
     */
    protected $parent_obj;

    /**
     * @var xaseAnswer
     */
    protected $xase_answer;

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
     * ilLocationDataTableGUI constructor.
     * @param xaseSubmissionGUI $a_parent_obj
     * @param string $a_parent_cmd
     */
    function __construct($a_parent_obj, $a_parent_cmd, ilObjAssistedExercise $assisted_exercise)
    {
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
        $this->xase_answer = $this->getSubmittedAnswers();

        parent::__construct($a_parent_obj, $a_parent_cmd);
        $this->parent_obj = $a_parent_obj;
        $this->setRowTemplate("tpl.submissions.html", "Customizing/global/plugins/Services/Repository/RepositoryObject/AssistedExercise");

        $this->setFormAction($this->ctrl->getFormActionByClass('xasesubmissiongui'));
        $this->setExternalSorting(true);

        $this->setDefaultOrderField("submitted_date");
        $this->setDefaultOrderDirection("asc");
        $this->setExternalSegmentation(true);
        $this->setEnableHeader(true);

        $list = $this->createListing();
        $this->tpl->setVariable('LIST', $list);

        $this->initColums();
        $this->addFilterItems();
        $this->parseData();
    }

    protected function addFilterItems()
    {
        $first_name = new ilTextInputGUI($this->pl->txt('first_name'), 'first_name');
        $this->addAndReadFilterItem($first_name);

        $last_name = new ilTextInputGUI($this->pl->txt('last_name'), 'last_name');
        $this->addAndReadFilterItem($last_name);

        $item = new ilTextInputGUI($this->pl->txt('item'), 'item');
        $this->addAndReadFilterItem($item);

        //TODO handle Status
        include_once("./Services/Form/classes/class.ilSelectInputGUI.php");
        $option[0] = $this->pl->txt('yes');
        $option[1] = $this->pl->txt('no');
        $assessed = new ilSelectInputGUI($this->pl->txt("assessed"), "assessed");
        $assessed->setOptions($option);
        $this->addAndReadFilterItem($assessed);
    }

    /**
     * @param $item
     */
    protected function addAndReadFilterItem(ilFormPropertyGUI $item)
    {
        $this->addFilterItem($item);
        $item->readFromSession();
        if ($item instanceof ilCheckboxInputGUI) {
            $this->filter[$item->getPostVar()] = $item->getChecked();
        } else {
            $this->filter[$item->getPostVar()] = $item->getValue();
        }
    }

    /**
     * @param array $a_set
     */
    public function fillRow($a_set)
    {
        /**
         * @var $xaseItem xaseItem
         */
        //$a_set contains the items
        $xaseItem = xaseItem::find($a_set['id']);
        $this->tpl->setVariable('FIRSTNAME', $this->dic->user()->getFirstname());
        $this->tpl->setVariable('LASTNAME', $this->dic->user()->getLastname());
        $this->tpl->setVariable('SUBMISSIONDATE', $this->xase_answer->getSubmissionDate());
        $this->tpl->setVariable('ITEMTITEL');
        /**
         * @var $xasePoint xasePoint
         */
        $xasePoint = xasePoint::find($xaseItem->getPointId());

        if (!empty($xasePoint)) {
            $this->tpl->setVariable('MAXPOINTS', $xasePoint->getMaxPoints());
            if(!empty($xasePoint->getTotalPoints())) {
                $this->tpl->setVariable('POINTS', $xasePoint->getTotalPoints());
            } else {
                $this->tpl->setVariable('POINTS', 0);
            }
        }
        /**
         * @var $xaseHint xaseHint
         */
        $xaseHint = xaseHint::where(array('item_id' => $xaseItem->getId()))->get();

        /**
         * @var $xaseAnswer xaseAnswer
         */
        if (!empty($xaseHint)) {
            $xaseAnswer = xaseAnswer::where(array('item_id' => $xaseItem->getId()))->first();
        }
        if (!empty($xaseAnswer)) {
            $this->tpl->setVariable('NUMBEROFUSEDHINTS', $xaseAnswer->getNumberOfUsedHints());
        } else {
            $this->tpl->setVariable('NUMBEROFUSEDHINTS', 0);
        }

        $this->addActionMenu($xaseItem);
    }

    protected function initColums()
    {
        $all_cols = $this->getSelectableColumns();
        foreach ($this->getSelectedColumns() as $col) {
            $this->addColumn($all_cols[$col]['txt'], $col, '16.66666666667%');
        }
        $this->addColumn($this->pl->txt('common_actions'), '', '16.66666666667%');
    }

    /**
     * @param xaseItem $xaseItem
     */
    protected function addActionMenu(xaseItem $xaseItem)
    {
        $current_selection_list = new ilAdvancedSelectionListGUI();
        $current_selection_list->setListTitle($this->pl->txt('common_actions'));
        $current_selection_list->setId('item_actions_' . $xaseItem->getId());
        $current_selection_list->setUseImages(false);

        $this->ctrl->setParameter($this->parent_obj, xaseItemGUI::ITEM_IDENTIFIER, $xaseItem->getId());
        $this->ctrl->setParameterByClass(xaseAnswerGUI::class, xaseItemGUI::ITEM_IDENTIFIER, $xaseItem->getId());
        if ($this->access->hasWriteAccess()) {
            $current_selection_list->addItem($this->pl->txt('edit_item'), xaseItemGUI::CMD_EDIT, $this->ctrl->getLinkTargetByClass('xaseitemgui', xaseItemGUI::CMD_EDIT));
        }
        if ($this->access->hasWriteAccess()) {
            $current_selection_list->addItem($this->pl->txt('answer'), xaseAnswerGUI::CMD_STANDARD, $this->ctrl->getLinkTargetByClass('xaseanswergui', xaseAnswerGUI::CMD_STANDARD));
        }
        /*        if ($this->access->hasWriteAccess()) {
                    $current_selection_list->addItem($this->pl->txt('edit_answer'), xaseAnswerGUI::CMD_EDIT, $this->ctrl->getLinkTargetByClass('xaseanswergui', xaseAnswerGUI::CMD_EDIT));
                }*/
        $this->tpl->setVariable('ACTIONS', $current_selection_list->getHTML());
    }

    protected function parseData()
    {
        $this->determineOffsetAndOrder();
        $this->determineLimit();

        $collection = xaseAnswer::getCollection();
        $collection->where(array('status' => 'submitted'), '=');

        $collection->leftjoin(xaseAssessment::returnDbTableName(), 'id', 'answer_id');

        $collection->leftjoin(xasePoint::returnDbTableName(), 'point_id', 'id');

        $xaseSettings = xaseSettings::getCollection()->where(array('assisted_exercise_object_id' => $this->getId()), '=')->first();

        if($xaseSettings->getModus() == 3) {
            $collection->leftjoin(xaseVoting::returnDbTableName(), 'id', 'answer_id');
        }

        $sorting_column = $this->getOrderField() ? $this->getOrderField() : 'submission_date';
        $offset = $this->getOffset() ? $this->getOffset() : 0;

        $sorting_direction = $this->getOrderDirection();
        $num = $this->getLimit();

        $collection->orderBy($sorting_column, $sorting_direction);
        $collection->limit($offset, $num);

        //$collection->debug();

        foreach ($this->filter as $filter_key => $filter_value) {
            switch ($filter_key) {
                case 'first_name':
                case 'last_name':
                case 'item_title':
                case 'assessed':
                    $collection->where(array($filter_key => '%' . $filter_value . '%'), 'LIKE');
                    break;
            }
        }

        $this->setData($collection->getArray());
    }

    public function getSelectableColumns()
    {
        $cols["submission_date"] = array(
            "txt" => $this->pl->txt("submission_date"),
            "default" => true);
        $cols["first_name"] = array(
            "txt" => $this->pl->txt("first_name"),
            "default" => false);
        $cols["last_name"] = array(
            "txt" => $this->pl->txt("last_name"),
            "default" => false);
        $cols["item_title"] = array(
            "txt" => $this->pl->txt("item_title"),
            "default" => false);
        $cols["assessed"] = array(
            "txt" => $this->pl->txt("assessed"),
            "default" => false);
        $cols["max_points"] = array(
            "txt" => $this->pl->txt("max_points"),
            "default" => true);
        $cols["number_of_used_hints"] = array(
            "txt" => $this->pl->txt("number_of_used_hints"),
            "default" => false);
        $cols["points_teacher"] = array(
            "txt" => $this->pl->txt("points_teacher"),
            "default" => false);
        $cols["additional_points"] = array(
            "txt" => $this->pl->txt("additional_points"),
            "default" => false);
        $cols["total_points"] = array(
            "txt" => $this->pl->txt("total_points"),
            "default" => false);
        $cols["number_of_upvotings"] = array(
            "txt" => $this->pl->txt("number_of_upvotings"),
            "default" => false);
        return $cols;
    }

    // TODO change static array values
    // TODO decide if the listing appears in front of the filter
    public function createListing()
    {
        $f = $this->dic->ui()->factory();
        $renderer = $this->dic->ui()->renderer();

        $unordered = $f->listing()->descriptive(
            array
            (
                $this->pl->txt('max_achievable_points') => strval(80),
                $this->pl->txt('average_achieved_points') => strval(64),
                $this->pl->txt('average_used_hints_per_item') => strval(4)
            )
        );

        return $renderer->render($unordered);
    }

    /*
     * 1) get all the answers where submission date is not empty
     * (when the user clicks submission button, save the submission date for all answers of the user)
     */

    protected function getSubmittedAnswers() {
        $statement = $this->dic->database()->query("SELECT submission_date FROM ilias.rep_robj_xase_answer where submission_date IS NOT NULL");
        $result = $statement->fetchAssoc();
        return $result;
    }
}