<?php
/**
 * Class xaseHint
 * @author  Benjamin Seglias <bs@studer-raimann.ch>
 */

class xaseHint extends ActiveRecord
{

    /**
     * @return string
     */
    public static function returnDbTableName()
    {
        return 'rep_robj_xase_hint';
    }

    /**
     * @var int
     *
     * @db_has_field        true
     * @db_fieldtype        integer
     * @db_length           8
     * @db_is_primary       true
     * @con_sequence        true
     */
    protected $id;

    /**
     * @var int
     *
     * @db_has_field  true
     * @db_fieldtype  integer
     * @db_length     4
     * @db_is_notnull true
     */
    protected $item_id;

    /**
     * @var int
     *
     * @db_has_field  true
     * @db_fieldtype  integer
     * @db_length     4
     * @db_is_notnull true
     */
    protected $hint_number;

    /**
     * @var int
     *
     * @db_has_field  true
     * @db_fieldtype  integer
     * @db_length     1
     * @db_is_notnull true
     */
    protected $is_template;

    /**
     * @var string
     *
     * @db_has_field        true
     * @db_fieldtype        text
     * @db_length           600
     * @db_is_notnull       true
     */
    protected $label;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getItemId()
    {
        return $this->item_id;
    }

    /**
     * @param int $item_id
     */
    public function setItemId($item_id)
    {
        $this->item_id = $item_id;
    }

    /**
     * @return int
     */
    public function getHintNumber()
    {
        return $this->hint_number;
    }

    /**
     * @param int $hint_number
     */
    public function setHintNumber($hint_number)
    {
        $this->hint_number = $hint_number;
    }

    /**
     * @return int
     */
    public function getisTemplate()
    {
        return $this->is_template;
    }

    /**
     * @param int $is_template
     */
    public function setIsTemplate($is_template)
    {
        $this->is_template = $is_template;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @param string $label
     */
    public function setLabel($label)
    {
        $this->label = $label;
    }

}