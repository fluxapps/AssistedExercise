<?php

/**
 * Class xaseItemTableGUI
 * @author  Benjamin Seglias <bs@studer-raimann.ch>
 * @ilCtrl_Calls      xaseItemTableGUI: xaseAnswerGUI
 */

require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/AssistedExercise/classes/ActiveRecords/class.xasePoint.php');
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/AssistedExercise/classes/ActiveRecords/class.xaseHint.php');
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/AssistedExercise/classes/ActiveRecords/class.xaseAnswer.php');
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/AssistedExercise/classes/class.xaseAnswerGUI.php');
require_once('./Services/UIComponent/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php');
require_once('./Services/Table/classes/class.ilTable2GUI.php');
require_once('./Services/Form/classes/class.ilTextInputGUI.php');

class xaseItemTableGUI extends ilTable2GUI
{
    const CMD_STANDARD = 'content';
    const TBL_ID = 'tbl_xase_items';

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
     * @var ilAssistedExercisePlugin
     */
    protected $pl;

    /**
     * @var ilObjAssistedExerciseAccess
     */
    protected $access;


    /**
     * ilLocationDataTableGUI constructor.
     * @param xaseItemGUI $a_parent_obj
     * @param string $a_parent_cmd
     */
    function __construct($a_parent_obj, $a_parent_cmd)
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

        if (ilObjAssistedExerciseAccess::hasWriteAccess()) {
            $new_item_link = $this->ctrl->getLinkTargetByClass("xaseItemGUI", xaseItemGUI::CMD_EDIT);
            $ilLinkButton = ilLinkButton::getInstance();
            $ilLinkButton->setCaption($this->pl->txt("add_item"), false);
            $ilLinkButton->setUrl($new_item_link);
            /** @var $ilToolbar ilToolbarGUI */
            $DIC->toolbar()->addButtonInstance($ilLinkButton);
        }

        parent::__construct($a_parent_obj, $a_parent_cmd);
        $this->parent_obj = $a_parent_obj;
        $this->setRowTemplate("tpl.items.html", "Customizing/global/plugins/Services/Repository/RepositoryObject/AssistedExercise");

        $this->setFormAction($this->ctrl->getFormActionByClass('xaseitemgui'));
        $this->setExternalSorting(true);

        $this->setDefaultOrderField("title");
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
        $title = new ilTextInputGUI($this->pl->txt('title'), 'title');
        $this->addAndReadFilterItem($title);

        include_once("./Services/Form/classes/class.ilSelectInputGUI.php");
        $option[0] = $this->pl->txt('open');
        $option[1] = $this->pl->txt('answered');
        $status = new ilSelectInputGUI($this->pl->txt("status"), "status");
        $status->setOptions($option);
        $this->addAndReadFilterItem($status);
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
        $this->tpl->setVariable('TITLE', $xaseItem->getTitle());
        $this->tpl->setVariable('STATUS', $xaseItem->getItemStatus());
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

        /*        $this->addColumn($this->pl->txt('title'), 'title');
                $this->addColumn($this->pl->txt('status'), 'status');
                $this->addColumn($this->pl->txt('max_points'), 'max_points');
                $this->addColumn($this->pl->txt('number_of_used_hints'), 'number_of_used_hints');
                $this->addColumn($this->pl->txt('points'), 'points');
                $this->addColumn($this->pl->txt('common_actions'), '', '150px');*/
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

        $collection = xaseItem::getCollection();
        $collection->where(array('assisted_exercise_id' => $this->parent_obj->object->getId()));

        $collection->leftjoin(xasePoint::returnDbTableName(), 'point_id', 'id', array('max_points', 'total_points'));

        $collection->leftjoin(xaseAnswer::returnDbTableName(), 'id', 'item_id', array('number_of_used_hints'));

        $sorting_column = $this->getOrderField() ? $this->getOrderField() : 'title';
        $offset = $this->getOffset() ? $this->getOffset() : 0;

        $sorting_direction = $this->getOrderDirection();
        $num = $this->getLimit();

        $collection->orderBy($sorting_column, $sorting_direction);
        $collection->limit($offset, $num);

        //$collection->debug();

        foreach ($this->filter as $filter_key => $filter_value) {
            switch ($filter_key) {
                case 'title':
                case 'status':
                    $collection->where(array($filter_key => '%' . $filter_value . '%'), 'LIKE');
                    break;
            }
        }

        $this->setData($collection->getArray());
    }

    public function getSelectableColumns()
    {
        $cols["title"] = array(
            "txt" => $this->pl->txt("title"),
            "default" => true);
        $cols["status"] = array(
            "txt" => $this->pl->txt("status"),
            "default" => true);
        $cols["max_points"] = array(
            "txt" => $this->pl->txt("max_points"),
            "default" => true);
        $cols["number_of_used_hints"] = array(
            "txt" => $this->pl->txt("number_of_used_hints"),
            "default" => true);
        $cols["total_points"] = array(
            "txt" => $this->pl->txt("points"),
            "default" => true);
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
                $this->pl->txt('max_achieved_points') => strval(64),
                $this->pl->txt('total_used_hints') => strval(4),
                $this->pl->txt('disposal_date') => '05.09.2017',
            )
        );

        return $renderer->render($unordered);
    }

}