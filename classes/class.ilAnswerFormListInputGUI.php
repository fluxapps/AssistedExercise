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

class ilAnswerFormListInputGUI extends ilPropertyFormGUI
{
    protected $dic;
    protected $save_btn;
    protected $item;
    protected $values = [];
    protected $existing_answer_data = [];
    protected $existing_comment_data = [];
    protected $existing_voting_data = [];
    protected $total_upvotings;
    protected $upvotings = [];


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
        $btn_remove_hint = ilJsLinkButton::getInstance();
        $btn_remove_hint->setCaption('text_remove_hint_btn');
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
     * @return mixed
     */
    public function getSaveBtn()
    {
        return $this->save_btn;
    }

    /**
     * @param mixed $save_btn
     */
    public function setSaveBtn($save_btn)
    {
        $this->save_btn = $save_btn;
    }

    /**
     * @return mixed
     */
    public function getItem()
    {
        return $this->item;
    }

    /**
     * @param mixed $item
     */
    public function setItem($item)
    {
        $this->item = $item;
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