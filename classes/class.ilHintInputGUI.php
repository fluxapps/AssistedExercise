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
 * This class represents a hint property in a property form
 *
 * This class is used to generate the hint inputs
 *
 * @author: Benjamin Seglias   <bs@studer-raimann.ch>
 * @ingroup ServicesForm
 */

require_once('./Services/Form/classes/class.ilTextInputGUI.php');
require_once('./Services/Form/classes/class.ilNumberInputGUI.php');

class ilHintInputGUI extends ilFormPropertyGUI {

    protected $dic;
    protected $lvl_1_hint;
    protected $lvl_1_minus_points;
    protected $lvl_2_hint;
    protected $lvl_2_minus_points;
    protected $remove_hint_btn;
/*    protected $value;
    protected $lvl_label;
    protected $lvl_hint;
    protected $lvl_minus_points_label;
    protected $lvl_minus_points;
    protected $maxlength_hint = 200;
    protected $maxlength_minus_points = 200;
    protected $size_minus_points = 4;
    protected $remove_hint_btn;*/

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
        $this->lvl_1_hint = new ilTextInputGUI("Ebene 1 Tipp", "lvl_1_hint_");
        $this->lvl_1_hint->setRequired(true);
        $this->lvl_1_minus_points = new ilNumberInputGUI("Punkteabzug", "lvl_1_minus_points_");
        $this->lvl_1_minus_points->setSize(4);
        $this->lvl_1_minus_points->setRequired(true);
        $this->lvl_2_hint = new ilTextInputGUI("Ebene 2 Tipp", "lvl_2_hint_");
        $this->lvl_2_minus_points = new ilNumberInputGUI("Punkteabzug", "lvl_2_minus_points_");
        $this->lvl_2_minus_points->setSize(4);
        $btn_remove_hint = ilJsLinkButton::getInstance();
        $btn_remove_hint->setCaption('text_remove_hint_btn');
        $btn_remove_hint->setName('text_remove_hint_btn');
        $hint_number = 1;
        $btn_remove_hint->setId('remove_hint_');
        $btn_remove_hint->addCSSClass('remove_hint_btn');
        //$btn_remove_hint->setOnClick("remove_hint_btn()");
        $this->setRemoveHintBtn($btn_remove_hint->render());

        $DIC->ui()->mainTemplate()->addCss("./Customizing/global/plugins/Services/Repository/RepositoryObject/AssistedExercise/templates/default/less/hint_form.css");

        parent::__construct($a_title, $a_postvar);
        $this->setType("hint");
    }

    /**
     * @return ilTextInputGUI
     */
    public function getLvl1Hint()
    {
        return $this->lvl_1_hint;
    }

    /**
     * @param ilTextInputGUI $lvl_1_hint
     */
    public function setLvl1Hint($lvl_1_hint)
    {
        $this->lvl_1_hint = $lvl_1_hint;
    }

    /**
     * @return ilNumberInputGUI
     */
    public function getLvl1MinusPoints()
    {
        return $this->lvl_1_minus_points;
    }

    /**
     * @param ilNumberInputGUI $lvl_1_minus_points
     */
    public function setLvl1MinusPoints($lvl_1_minus_points)
    {
        $this->lvl_1_minus_points = $lvl_1_minus_points;
    }

    /**
     * @return ilTextInputGUI
     */
    public function getLvl2Hint()
    {
        return $this->lvl_2_hint;
    }

    /**
     * @param ilTextInputGUI $lvl_2_hint
     */
    public function setLvl2Hint($lvl_2_hint)
    {
        $this->lvl_2_hint = $lvl_2_hint;
    }

    /**
     * @return ilNumberInputGUI
     */
    public function getLvl2MinusPoints()
    {
        return $this->lvl_2_minus_points;
    }

    /**
     * @param ilNumberInputGUI $lvl_2_minus_points
     */
    public function setLvl2MinusPoints($lvl_2_minus_points)
    {
        $this->lvl_2_minus_points = $lvl_2_minus_points;
    }

    /**
     * @return string
     */
    public function getRemoveHintBtn()
    {
        return $this->remove_hint_btn;
    }

    /**
     * @param string $remove_hint_btn
     */
    public function setRemoveHintBtn($remove_hint_btn)
    {
        $this->remove_hint_btn = $remove_hint_btn;
    }

    //TODO check if method is necessary, since ilTextInputGUI and ilNumberInputGUI already implement this method
    /**
     * Set value by array
     *
     * @param	array	$a_values	value array
     */
/*    function setValueByArray($a_values)
    {
        $this->setLvlHint($a_values[$this->getPostVar()]["lvl_hint"]);
        $this->setLvlMinusPoints($a_values[$this->getPostVar()]["lvl_minus_points"]);
    }*/

    //TODO check if method is necessary, since ilTextInputGUI and ilNumberInputGUI already implement this method
    /**
     * Check input, strip slashes etc. set alert, if input is not ok.
     *
     * @return	boolean		Input ok, true/false
     */
/*    function checkInput()
    {
        $_POST[$this->getPostVar()]["lvl_hint"] =
        ilUtil::stripSlashes($_POST[$this->getPostVar()]["lvl_hint"]);
        $_POST[$this->getPostVar()]["lvl_minus_points"] =
            ilUtil::stripSlashes($_POST[$this->getPostVar()]["lvl_minus_points"]);
        if ($this->getRequired() &&
            (trim($_POST[$this->getPostVar()]["lvl_hint"]) == "" || trim($_POST[$this->getPostVar()]["lvl_minus_points"]) == "" ))
            {
                $this->setAlert($this->dic->language()->txt("msg_input_is_required"));
                return false;
            }
        return true;
    }*/

    /**
     * Insert property html
     */
    function insert($a_tpl) {
        $tpl = new ilTemplate("tpl.prop_hint.html", true, true, "Customizing/global/plugins/Services/Repository/RepositoryObject/AssistedExercise");

        $tpl->setCurrentBlock("lvl_1_hint");
        $tpl->setVariable("LAB_ID", $this->getLvl1Hint()->getFieldId());
        $tpl->setVariable("PROPERTY_TITLE", $this->getLvl1Hint()->getTitle());
        $tpl->setVariable("LVL_1_HINT", $this->getLvl1Hint()->render());
        $tpl->parseCurrentBlock();

        $tpl->setCurrentBlock("lvl_1_minus_points");
        $tpl->setVariable("LAB_ID", $this->getLvl1MinusPoints()->getFieldId());
        $tpl->setVariable("PROPERTY_TITLE", $this->getLvl1MinusPoints()->getTitle());
        $tpl->setVariable("LVL_1_MINUS_POINTS", $this->getLvl1MinusPoints()->render());
        $tpl->parseCurrentBlock();

        $tpl->setCurrentBlock("lvl_2_hint");
        $tpl->setVariable("LAB_ID", $this->getLvl2Hint()->getFieldId());
        $tpl->setVariable("PROPERTY_TITLE", $this->getLvl2Hint()->getTitle());
        $tpl->setVariable("LVL_2_HINT", $this->getLvl2Hint()->render());
        $tpl->parseCurrentBlock();

        $tpl->setCurrentBlock("lvl_2_minus_points");
        $tpl->setVariable("LAB_ID", $this->getLvl2MinusPoints()->getFieldId());
        $tpl->setVariable("PROPERTY_TITLE", $this->getLvl2MinusPoints()->getTitle());
        $tpl->setVariable("LVL_2_MINUS_POINTS", $this->getLvl2MinusPoints()->render());
        $tpl->parseCurrentBlock();

        $tpl->setCurrentBlock("remove_btn");
        $tpl->setVariable("REMOVE_HINT_BUTTON", $this->getRemoveHintBtn());
        $tpl->parseCurrentBlock("remove_btn");

        $a_tpl->setCurrentBlock("prop_generic");
        //$a_tpl->setVariable("PROP_GENERIC", $tpl->get().$tpl->get().$tpl->get().$tpl->get());
        $a_tpl->setVariable("PROP_GENERIC", $tpl->get());
        $a_tpl->parseCurrentBlock();
    }



    /**
     * Insert property html
     */
/*    function insert($a_tpl) {
        $tpl = new ilTemplate("tpl.prop_hint_backup.html", true, true, "Customizing/global/plugins/Services/Repository/RepositoryObject/AssistedExercise");
        $tpl->setCurrentBlock("lvl_hint");
        $tpl->setVariable("LEVEL_LABEL", $this->getLvlLabel());
        $tpl->setVariable("PROP_INPUT_TYPE", "text");
        $tpl->setVariable("ID", $this->getFieldId());
        $tpl->setVariable("MAXLENGTH", $this->getMaxlengthHint());
        $tpl->setVariable("POST_VAR", $this->getPostVar());
        $tpl->setVariable("PROPERTY_VALUE", $this->getValue());
        $tpl->parseCurrentBlock();

        $tpl->setCurrentBlock("lvl_minus_points");
        $tpl->setVariable("LEVEL__MINUS_POINTS_LABEL", $this->getLvlMinusPointsLabel());
        $tpl->setVariable("SIZE", $this->getSizeMinusPoints());
        $tpl->setVariable("ID", $this->getFieldId());
        $tpl->setVariable("MAXLENGTH", $this->getMaxlengthMinusPoints());
        $tpl->setVariable("POST_VAR", $this->getPostVar());
        $tpl->setVariable("PROPERTY_VALUE", $this->getValue());
        $tpl->parseCurrentBlock();

        $tpl->setCurrentBlock("CODE");
        $tpl->setVariable("REMOVE_HINT_BUTTON", $this->getRemoveHintBtn());

        $a_tpl->setCurrentBlock("prop_generic");
        $a_tpl->setVariable("PROP_GENERIC", $tpl->get());
        $a_tpl->parseCurrentBlock();
    }*/
}