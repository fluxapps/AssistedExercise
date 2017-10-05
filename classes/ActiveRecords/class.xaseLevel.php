<?php
/**
 * Class xaseLevel
 * @author  Benjamin Seglias <bs@studer-raimann.ch>
 */

class xaseLevel extends ActiveRecord
{

    /**
     * @return string
     */
    public static function returnDbTableName()
    {
        return 'rep_robj_xase_level';
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
    protected $hint_id;

    /**
     * @var int
     *
     * @db_has_field  true
     * @db_fieldtype  integer
     * @db_length     4
     * @db_is_notnull true
     */
    protected $point_id;

    /**
     * @var int
     *
     * @db_has_field  true
     * @db_fieldtype  integer
     * @db_length     1
     * @db_is_notnull true
     */
    protected $hint_level;

    /**
     * @var string
     *
     * @db_has_field        true
     * @db_fieldtype        text
     * @db_length           4000
     * @db_is_notnull       true
     */
    protected $hint;

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
    public function getHintId()
    {
        return $this->hint_id;
    }

    /**
     * @param int $hint_id
     */
    public function setHintId($hint_id)
    {
        $this->hint_id = $hint_id;
    }

    /**
     * @return int
     */
    public function getPointId()
    {
        return $this->point_id;
    }

    /**
     * @param int $point_id
     */
    public function setPointId($point_id)
    {
        $this->point_id = $point_id;
    }

    /**
     * @return int
     */
    public function getHintLevel()
    {
        return $this->hint_level;
    }

    /**
     * @param int $hint_level
     */
    public function setHintLevel($hint_level)
    {
        $this->hint_level = $hint_level;
    }

    /**
     * @return string
     */
    public function getHint()
    {
        return $this->hint;
    }

    /**
     * @param string $hint
     */
    public function setHint($hint)
    {
        $this->hint = $hint;
    }

}