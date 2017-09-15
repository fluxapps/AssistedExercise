<?php
/**
 * Created by PhpStorm.
 * User: bseglias
 * Date: 11.09.17
 * Time: 11:46
 */

class xasePoint extends ActiveRecord {

    /**
     * @return string
     */
    public static function returnDbTableName() {
        return 'rep_robj_xase_point';
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
    protected $point_id;

    /**
     * @var int
     *
     * @db_has_field  true
     * @db_fieldtype  integer
     * @db_length     4
     * @db_is_notnull true
     */
    protected $max_points;


    /**
     * @var int
     *
     * @db_has_field  true
     * @db_fieldtype  integer
     * @db_length     4
     * @db_is_notnull true
     */
    protected $total_points;

    /**
     * @var int
     *
     * @db_has_field  true
     * @db_fieldtype  integer
     * @db_length     4
     * @db_is_notnull true
     */
    protected $points_teacher;

    /**
     * @var int
     *
     * @db_has_field  true
     * @db_fieldtype  integer
     * @db_length     4
     * @db_is_notnull true
     */
    protected $additional_points;

    /**
     * @var int
     *
     * @db_has_field  true
     * @db_fieldtype  integer
     * @db_length     4
     * @db_is_notnull true
     */
    protected $minus_points;

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
    public function getMaxPoints()
    {
        return $this->max_points;
    }

    /**
     * @param int $max_points
     */
    public function setMaxPoints($max_points)
    {
        $this->max_points = $max_points;
    }

    /**
     * @return int
     */
    public function getTotalPoints()
    {
        return $this->total_points;
    }

    /**
     * @param int $total_points
     */
    public function setTotalPoints($total_points)
    {
        $this->total_points = $total_points;
    }

    /**
     * @return int
     */
    public function getPointsTeacher()
    {
        return $this->points_teacher;
    }

    /**
     * @param int $points_teacher
     */
    public function setPointsTeacher($points_teacher)
    {
        $this->points_teacher = $points_teacher;
    }

    /**
     * @return int
     */
    public function getAdditionalPoints()
    {
        return $this->additional_points;
    }

    /**
     * @param int $additional_points
     */
    public function setAdditionalPoints($additional_points)
    {
        $this->additional_points = $additional_points;
    }

    /**
     * @return int
     */
    public function getMinusPoints()
    {
        return $this->minus_points;
    }

    /**
     * @param int $minus_points
     */
    public function setMinusPoints($minus_points)
    {
        $this->minus_points = $minus_points;
    }

}