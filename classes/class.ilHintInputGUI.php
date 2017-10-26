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
require_once('./Services/UIComponent/Button/classes/class.ilJsLinkButton.php');

class ilHintInputGUI extends ilFormPropertyGUI {

    protected $dic;
    protected $lvl_1_hint;
    protected $lvl_1_minus_points;
    protected $lvl_2_hint;
    protected $lvl_2_minus_points;
    protected $remove_hint_btn;
    protected $hint_to_label;
    //saves the values for the different hints
    protected $values = [];
    protected $existing_hint_data = [];
    protected $existing_level_data = [];
    //TODO rename to toal_minus_points and with local history add minus points and getters & setters again
    protected $total_minus_points;
    protected $minus_points = [];
    /**
     * @var ilAssistedExercisePlugin
     */
    protected $pl;


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
        $this->lvl_1_hint = new ilTextInputGUI("Ebene 1 Tipp", "hint[][lvl_1_hint]");
        $this->lvl_1_hint->setRequired(true);
        $this->lvl_1_minus_points = new ilNumberInputGUI("Punkteabzug", "hint[][lvl_1_minus_points]");
        $this->lvl_1_minus_points->setSize(4);
        $this->lvl_1_minus_points->setRequired(true);
        $this->lvl_2_hint = new ilTextInputGUI("Ebene 2 Tipp", "hint[][lvl_2_hint]");
        $this->lvl_2_minus_points = new ilNumberInputGUI("Punkteabzug", "hint[][lvl_2_minus_points]");
        $this->lvl_2_minus_points->setSize(4);
        $this->pl = ilAssistedExercisePlugin::getInstance();
        $btn_remove_hint = ilJsLinkButton::getInstance();
        $btn_remove_hint->setCaption($this->pl->txt("text_remove_hint_btn"), false);
        $btn_remove_hint->setName('text_remove_hint_btn');
        $btn_remove_hint->setId('remove_hint_');
        $btn_remove_hint->addCSSClass('remove_hint_btn');
        $this->setRemoveHintBtn($btn_remove_hint->render());

        $DIC->ui()->mainTemplate()->addCss("./Customizing/global/plugins/Services/Repository/RepositoryObject/AssistedExercise/templates/default/less/hint_form.css");

        parent::__construct($a_title, $a_postvar);
        $this->setType("hint");
    }

    /**
     * @return bool
     */
    public function checkInput()
    {
        // Wenn inputbenennung wie folgt: name="hint[1][lvl_1_minus_points]"
        foreach ($_POST['hint'] as $id => $data) {

            $data["is_template"] = ilUtil::stripSlashes($data["is_template"]);
            $data["lvl_1_hint"] = ilUtil::stripSlashes($data["lvl_1_hint"]);
            $data["lvl_1_minus_points"] = ilUtil::stripSlashes($data["lvl_1_minus_points"]);
            $data["lvl_2_hint"] = ilUtil::stripSlashes($data["lvl_2_hint"]);
            $data["lvl_2_minus_points"] = ilUtil::stripSlashes($data["lvl_2_minus_points"]);
            //don't check hidden input
            if ($data['is_template'] == 0) {
                continue;
            }
            if (trim($data["lvl_1_hint"]) == "" || trim($data["lvl_1_minus_points"]) == "") {
                ilUtil::sendFailure($this->dic->language()->txt("msg_input_hints"));
                return false;
            }
            if(!is_numeric($data["lvl_1_minus_points"])) {
                ilUtil::sendFailure($this->dic->language()->txt("msg_input_hints"));
                return false;
            }
            if (!empty(trim($data["lvl_2_hint"]))) {
                if (trim($data["lvl_2_minus_points"]) == "" || !is_numeric($data["lvl_2_minus_points"])) {
                    ilUtil::sendFailure($this->dic->language()->txt("msg_input_hints"));
                    return false;
                }
            }
            if (!empty(trim($data["lvl_2_minus_points"]))) {
                if (trim($data["lvl_2_hint"]) == "" || !is_numeric($data["lvl_2_minus_points"])) {
                    ilUtil::sendFailure($this->dic->language()->txt("msg_input_hints"));
                    return false;
                }
            }
            $this->total_minus_points += $data["lvl_1_minus_points"] +  $data["lvl_2_minus_points"];
        }
        if ($_POST["max_points"] <= $this->total_minus_points) {
            ilUtil::sendFailure($this->dic->language()->txt("msg_input_minus_points_error"));
            return false;
        }
        return true;
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

    /**
     * @return mixed
     */
    public function getHintToLabel()
    {
        return $this->hint_to_label;
    }

    /**
     * @param mixed $hint_to_label
     */
    public function setHintToLabel($hint_to_label)
    {
        $this->hint_to_label = $hint_to_label;
    }

    /**
     * @return mixed
     */
    public function getExistingHintData()
    {
        return $this->existing_hint_data;
    }

    /**
     * @param mixed $existing_hint_data
     */
    public function setExistingHintData($existing_hint_data)
    {
        $this->existing_hint_data = $existing_hint_data;
    }

    /**
     * @return array
     */
    public function getExistingLevelData()
    {
        return $this->existing_level_data;
    }

    /**
     * @param array $existing_level_data
     */
    public function setExistingLevelData($existing_level_data)
    {
        $this->existing_level_data = $existing_level_data;
    }

    /**
     * @return array
     */
    public function getMinusPoints()
    {
        return $this->minus_points;
    }

    /**
     * @param array $minus_points
     */
    public function setMinusPoints($minus_points)
    {
        $this->minus_points = $minus_points;
    }



    /**
     * Set value by array
     *
     * @param	array	$a_values	value array
     */
    function setValueByArray($a_values)
    {
       if (is_array($a_values['hint'])) {
           foreach ($a_values['hint'] as $id => $data) {
               $this->values[$id]['is_template'] = $data["is_template"];
               $this->values[$id]['label'] = $data["label"];
               $this->values[$id]['lvl_1_hint'] = $data["lvl_1_hint"];
               $this->values[$id]['lvl_1_minus_points'] = $data["lvl_1_minus_points"];
               $this->values[$id]['lvl_2_hint'] = $data["lvl_2_hint"];
               $this->values[$id]['lvl_2_minus_points'] = $data["lvl_2_minus_points"];
           }
       }
    }

    //TODO SET HINT ID VARIABLE
    /**
     * Insert property html
     */
    function insert($a_tpl) {

        // hint[*id*][.....]

        $tpl = new ilTemplate("tpl.prop_hint.html", true, true, "Customizing/global/plugins/Services/Repository/RepositoryObject/AssistedExercise");

        $tpl->setVariable("DISPLAY", "style='display: none;'");

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

        if (!empty($this->getExistingHintData())) {
            foreach ($this->getExistingHintData() as $hint_data) {
                $tpl->setVariable("HIDDEN_LABEL_INPUT", $hint_data['label']);
                $tpl->setVariable("HINT_ID", $hint_data['id']);
                $tpl->setCurrentBlock("existing_hint_data");
                $tpl->setVariable("CONTENT", htmlentities(json_encode($hint_data, JSON_UNESCAPED_UNICODE)));

                if (!empty($this->getExistingLevelData())) {
                    foreach ($this->getExistingLevelData() as $level_data) {
                        if ($level_data['hint_id'] !== $hint_data['id']) {
                            continue;
                        } else {
                            if($level_data['hint_level'] == 1) {
                                $tpl->setVariable("CONTENT_LEVEL_1", htmlentities(json_encode($level_data, JSON_UNESCAPED_UNICODE)));
                            } else {
                                $tpl->setVariable("CONTENT_LEVEL_2", htmlentities(json_encode($level_data, JSON_UNESCAPED_UNICODE)));
                            }
                        }

                        if(!empty($this->getExistingLevelData())) {
                            foreach ($this->getMinusPoints() as $minus_point) {
                                if ($minus_point['id'] !== $level_data['point_id']) {
                                    continue;
                                } else {
                                    if($level_data['hint_level'] == 1) {
                                        $tpl->setVariable("CONTENT_LEVEL_1_MINUS_POINTS", htmlentities(json_encode($minus_point, JSON_UNESCAPED_UNICODE)));
                                    } else {
                                        $tpl->setVariable("CONTENT_LEVEL_2_MINUS_POINTS", htmlentities(json_encode($minus_point, JSON_UNESCAPED_UNICODE)));
                                    }
                                }
                            }
                        }
                    }
                }
                $tpl->parseCurrentBlock("existing_hint_data");
            }
        }

        $a_tpl->setCurrentBlock("prop_generic");
        //$a_tpl->setVariable("PROP_GENERIC", $tpl->get().$tpl->get().$tpl->get().$tpl->get());
        $a_tpl->setVariable("PROP_GENERIC", $tpl->get());
        $a_tpl->parseCurrentBlock();

        if(!empty($_POST)) {

            foreach ($_POST['hint'] as $id => $data) {
                if ($data['is_template'] == 0) {
                    continue;
                }
                if (trim($data["lvl_1_hint"]) == "" || trim($data["lvl_1_minus_points"]) == "") {
                    ilUtil::sendFailure($this->dic->language()->txt("msg_input_hints"));
                }
                if(!is_numeric($data["lvl_1_minus_points"])) {
                    ilUtil::sendFailure($this->dic->language()->txt("msg_input_hints"));
                }
                if (!empty(trim($data["lvl_2_hint"]))) {
                    if (trim($data["lvl_2_minus_points"]) == "" || !is_numeric($data["lvl_2_minus_points"])) {
                        ilUtil::sendFailure($this->dic->language()->txt("msg_input_hints"));
                    }
                }
                if (!empty(trim($data["lvl_2_minus_points"]))) {
                    if (trim($data["lvl_2_hint"]) == "" || !is_numeric($data["lvl_2_minus_points"])) {
                        ilUtil::sendFailure($this->dic->language()->txt("msg_input_hints"));
                    }
                }

                $tpl = new ilTemplate("tpl.prop_hint.html", true, true, "Customizing/global/plugins/Services/Repository/RepositoryObject/AssistedExercise");
                $tpl->setVariable("DISPLAY", "style='display: inline-block;'");
                $tpl->setVariable("HIDDEN_LABEL_INPUT", $data['label']);
                $tpl->setVariable("HINT_ID", $data['hint_id']);
                $tpl->setCurrentBlock("lvl_1_hint");
                $tpl->setVariable("LAB_ID", $this->getLvl1Hint()->getFieldId());
                $tpl->setVariable("PROPERTY_TITLE", $this->getLvl1Hint()->getTitle());
                $this->getLvl1Hint()->setValue($this->values[$id]['lvl_1_hint']);
                $tpl->setVariable("LVL_1_HINT", $this->getLvl1Hint()->render());
                $tpl->parseCurrentBlock();

                $tpl->setCurrentBlock("lvl_1_minus_points");
                $tpl->setVariable("LAB_ID", $this->getLvl1MinusPoints()->getFieldId());
                $tpl->setVariable("PROPERTY_TITLE", $this->getLvl1MinusPoints()->getTitle());
                $this->getLvl1MinusPoints()->setValue($this->values[$id]['lvl_1_minus_points']);
                $tpl->setVariable("LVL_1_MINUS_POINTS", $this->getLvl1MinusPoints()->render());
                $tpl->parseCurrentBlock();

                $tpl->setCurrentBlock("lvl_2_hint");
                $tpl->setVariable("LAB_ID", $this->getLvl2Hint()->getFieldId());
                $tpl->setVariable("PROPERTY_TITLE", $this->getLvl2Hint()->getTitle());
                $this->getLvl2Hint()->setValue($this->values[$id]['lvl_2_hint']);
                $tpl->setVariable("LVL_2_HINT", $this->getLvl2Hint()->render());
                $tpl->parseCurrentBlock();

                $tpl->setCurrentBlock("lvl_2_minus_points");
                $tpl->setVariable("LAB_ID", $this->getLvl2MinusPoints()->getFieldId());
                $tpl->setVariable("PROPERTY_TITLE", $this->getLvl2MinusPoints()->getTitle());
                $this->getLvl2MinusPoints()->setValue($this->values[$id]['lvl_2_minus_points']);
                $tpl->setVariable("LVL_2_MINUS_POINTS", $this->getLvl2MinusPoints()->render());
                $tpl->parseCurrentBlock();

                $tpl->setCurrentBlock("remove_btn");
                $tpl->setVariable("REMOVE_HINT_BUTTON", $this->getRemoveHintBtn());
                $tpl->parseCurrentBlock("remove_btn");

                if (!empty($this->getExistingHintData())) {
                    foreach ($this->getExistingHintData() as $hint_data) {
                        $tpl->setCurrentBlock("existing_hint_data");
                        $tpl->setVariable("CONTENT", htmlentities(json_encode($hint_data, JSON_UNESCAPED_UNICODE)));

                        if (!empty($this->getExistingLevelData())) {
                            foreach ($this->getExistingLevelData() as $level_data) {
                                if ($level_data['hint_id'] !== $hint_data['id']) {
                                    continue;
                                } else {
                                    if($level_data['hint_level'] == 1) {
                                        $tpl->setVariable("CONTENT_LEVEL_1", htmlentities(json_encode($level_data, JSON_UNESCAPED_UNICODE)));
                                    } else {
                                        $tpl->setVariable("CONTENT_LEVEL_2", htmlentities(json_encode($level_data, JSON_UNESCAPED_UNICODE)));
                                    }
                                }
                                if(!empty($this->getExistingLevelData())) {
                                    foreach ($this->getMinusPoints() as $minus_point) {
                                        if ($minus_point['id'] !== $level_data['point_id']) {
                                            continue;
                                        } else {
                                            if($level_data['hint_level'] == 1) {
                                                $tpl->setVariable("CONTENT_LEVEL_1_MINUS_POINTS", htmlentities(json_encode($minus_point, JSON_UNESCAPED_UNICODE)));
                                            } else {
                                                $tpl->setVariable("CONTENT_LEVEL_2_MINUS_POINTS", htmlentities(json_encode($minus_point, JSON_UNESCAPED_UNICODE)));
                                            }
                                        }
                                    }
                                }
                            }
                        }
                        $tpl->parseCurrentBlock("existing_hint_data");
                    }
                }
                $a_tpl->setCurrentBlock("prop_generic");
                //$a_tpl->setVariable("PROP_GENERIC", $tpl->get().$tpl->get().$tpl->get().$tpl->get());
                $a_tpl->setVariable("PROP_GENERIC", $tpl->get());
                $a_tpl->parseCurrentBlock();
            }
        }
    }
}