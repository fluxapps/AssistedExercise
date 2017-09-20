<?php
/**
 * Class xaseVoting
 * @author  Benjamin Seglias <bs@studer-raimann.ch>
 */

class xaseVoting extends ActiveRecord
{

    /**
     * @return string
     */
    public static function returnDbTableName()
    {
        return 'rep_robj_xase_voting';
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
    protected $answer_id;

    /**
     * @var int
     *
     * @db_has_field  true
     * @db_fieldtype  integer
     * @db_length     4
     * @db_is_notnull false
     */
    protected $number_of_upvotings;

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
    public function getAnswerId()
    {
        return $this->answer_id;
    }

    /**
     * @param int $answer_id
     */
    public function setAnswerId($answer_id)
    {
        $this->answer_id = $answer_id;
    }

    /**
     * @return int
     */
    public function getNumberOfUpvotings()
    {
        return $this->number_of_upvotings;
    }

    /**
     * @param int $number_of_upvotings
     */
    public function setNumberOfUpvotings($number_of_upvotings)
    {
        $this->number_of_upvotings = $number_of_upvotings;
    }

}