<?php
/**
 * Created by PhpStorm.
 * User: bseglias
 * Date: 11.09.17
 * Time: 09:57
 */

class xaseSettings extends ActiveRecord {

    /**
     * @return string
     */
    public static function returnDbTableName() {
        return 'rep_robj_xase_settings';
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
     * @db_length     1
     * @db_is_notnull true
     */
    protected $is_online = 1;

    /**
     * @var int
     *
     * @db_has_field  true
     * @db_fieldtype  integer
     * @db_length     1
     * @db_is_notnull true
     */
    protected $is_time_limited = 0;

    /**
     * @var string
     *
     * @db_has_field        true
     * @db_fieldtype        timestamp
     */
    protected $start_date;

    /**
     * @var string
     *
     * @db_has_field        true
     * @db_fieldtype        timestamp
     */
    protected $end_date;

    /**
     * @var int
     *
     * @db_has_field  true
     * @db_fieldtype  integer
     * @db_length     1
     * @db_is_notnull true
     */
    protected $always_visible = 0;

    /**
     * @var int
     *
     * @db_has_field  true
     * @db_fieldtype  integer
     * @db_length     1
     * @db_is_notnull true
     */
    protected $modus = 1;

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getisOnline()
    {
        return $this->is_online;
    }

    /**
     * @param int $is_online
     */
    public function setIsOnline($is_online)
    {
        $this->is_online = $is_online;
    }

    /**
     * @return int
     */
    public function getisTimeLimited()
    {
        return $this->is_time_limited;
    }

    /**
     * @param int $is_time_limited
     */
    public function setIsTimeLimited($is_time_limited)
    {
        $this->is_time_limited = $is_time_limited;
    }

    /**
     * @return string
     */
    public function getStartDate()
    {
        return $this->start_date;
    }

    /**
     * @param string $start_date
     */
    public function setStartDate($start_date)
    {
        $this->start_date = $start_date;
    }

    /**
     * @return string
     */
    public function getEndDate()
    {
        return $this->end_date;
    }

    /**
     * @param string $end_date
     */
    public function setEndDate($end_date)
    {
        $this->end_date = $end_date;
    }

    /**
     * @return int
     */
    public function getAlwaysVisible()
    {
        return $this->always_visible;
    }

    /**
     * @param int $always_visible
     */
    public function setAlwaysVisible($always_visible)
    {
        $this->always_visible = $always_visible;
    }

    /**
     * @return int
     */
    public function getModus()
    {
        return $this->modus;
    }

    /**
     * @param int $modus
     */
    public function setModus($modus)
    {
        $this->modus = $modus;
    }
}