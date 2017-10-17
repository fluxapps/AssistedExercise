<?php
/**
 * Class xaseAssessment
 * @author: Benjamin Seglias   <bs@studer-raimann.ch>
 */

class xaseAssessment extends ActiveRecord
{
    /**
     * @return string
     */
    public static function returnDbTableName() {
        return 'rep_robj_xase_assessm';
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
    protected $answer_id;

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
     * @var string
     *
     * @db_has_field        true
     * @db_fieldtype        text
     * @db_length           4000
     * @db_is_notnull       true
     */
    protected $assessment_comment;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
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
     * @return string
     */
    public function getAssessmentComment()
    {
        return $this->assessment_comment;
    }

    /**
     * @param string $assessment_comment
     */
    public function setAssessmentComment($assessment_comment)
    {
        $this->assessment_comment = $assessment_comment;
    }
}