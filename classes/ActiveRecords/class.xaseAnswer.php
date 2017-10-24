<?php
/**
 * Class xaseAnswer
 * @author  Benjamin Seglias <bs@studer-raimann.ch>
 */

class xaseAnswer extends ActiveRecord {

    const ANSWER_STATUS_OPEN = 0;
    const ANSWER_STATUS_ANSWERED = 1;
    const ANSWER_STATUS_SUBMITTED = 2;
    const ANSWER_STATUS_RATED = 3;


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
     * @db_length     4
     * @db_is_notnull false
     */
    protected $point_id;

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
     * @var string
     *
     * @db_has_field        true
     * @db_fieldtype        timestamp
     */
    protected $submission_date;

    /**
     * @var int
     *
     * @db_has_field  true
     * @db_fieldtype  integer
     * @db_length     1
     * @db_is_notnull false
     */
    protected $answer_status;

    /**
     * @var int
     *
     * @db_has_field  true
     * @db_fieldtype  integer
     * @db_length     1
     * @db_is_notnull false
     */
    protected $is_assessed;

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

    /**
     * @return string
     */
    public function getSubmissionDate()
    {
        return $this->submission_date;
    }

    /**
     * @param string $submission_date
     */
    public function setSubmissionDate($submission_date)
    {
        $this->submission_date = $submission_date;
    }

    /**
     * @return string
     */
    public function getAnswerStatus()
    {
        return $this->answer_status;
    }

    /**
     * @param string $answer_status
     */
    public function setAnswerStatus($answer_status)
    {
        $this->answer_status = $answer_status;
    }

    /**
     * @return int
     */
    public function getisAssessed()
    {
        return $this->is_assessed;
    }

    /**
     * @param int $is_assessed
     */
    public function setIsAssessed($is_assessed)
    {
        $this->is_assessed = $is_assessed;
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