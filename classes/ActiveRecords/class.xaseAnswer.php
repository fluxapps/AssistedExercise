<?php
/**
 * Class xaseAnswer
 * @author  Benjamin Seglias <bs@studer-raimann.ch>
 */

class xaseAnswer extends ActiveRecord {
    /**
     * @return string
     */
    public static function returnDbTableName() {
        return 'rep_robj_xase_answer';
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
     * @db_is_notnull false
     */
    protected $user_id;

    /**
     * @var int
     *
     * @db_has_field  true
     * @db_fieldtype  integer
     * @db_length     4
     * @db_is_notnull false
     */
    protected $item_id;

    /**
     * @var int
     *
     * @db_has_field  true
     * @db_fieldtype  integer
     * @db_length     1
     * @db_is_notnull false
     */
    protected $show_hints;

    /**
     * @var int
     *
     * @db_has_field  true
     * @db_fieldtype  integer
     * @db_length     4
     * @db_is_notnull false
     */
    protected $number_of_used_hints;

    /**
     * @var string
     *
     * @db_has_field        true
     * @db_fieldtype        text
     * @db_length           4000
     * @db_is_notnull       false
     */
    protected $used_hints;

    /**
     * @var string
     *
     * @db_has_field        true
     * @db_fieldtype        text
     * @db_length           4000
     * @db_is_notnull       true
     */
    protected $body;

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
    public function getUserId()
    {
        return $this->user_id;
    }

    /**
     * @param int $user_id
     */
    public function setUserId($user_id)
    {
        $this->user_id = $user_id;
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
    public function getShowHints()
    {
        return $this->show_hints;
    }

    /**
     * @param int $show_hints
     */
    public function setShowHints($show_hints)
    {
        $this->show_hints = $show_hints;
    }

    /**
     * @return int
     */
    public function getNumberOfUsedHints()
    {
        return $this->number_of_used_hints;
    }

    /**
     * @param int $number_of_used_hints
     */
    public function setNumberOfUsedHints($number_of_used_hints)
    {
        $this->number_of_used_hints = $number_of_used_hints;
    }

    /**
     * @return string
     */
    public function getUsedHints()
    {
        return $this->used_hints;
    }

    /**
     * @param string $used_hints
     */
    public function setUsedHints($used_hints)
    {
        $this->used_hints = $used_hints;
    }

    /**
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @param string $body
     */
    public function setBody($body)
    {
        $this->body = $body;
    }

}