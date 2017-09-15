<?php

class xaseSettingsM1 extends ActiveRecord {
    /**
     * @return string
     */
    public static function returnDbTableName() {
        return 'rep_robj_xase_sett_m1';
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
    protected $settings_id;

    /**
     * @var int
     *
     * @db_has_field  true
     * @db_fieldtype  integer
     * @db_length     1
     * @db_is_notnull true
     */
    protected $rate_answers = 1;

    /**
     * @var string
     *
     * @db_has_field        true
     * @db_fieldtype        timestamp
     */
    protected $disposal_date;

    /**
     * @var int
     *
     * @db_has_field  true
     * @db_fieldtype  integer
     * @db_length     1
     * @db_is_notnull true
     */
    protected $sample_solution_visible = 1;

    /**
     * @var int
     *
     * @db_has_field  true
     * @db_fieldtype  integer
     * @db_length     1
     * @db_is_notnull true
     */
    protected $visible_if_exercise_finished = 1;

    /**
     * @var string
     *
     * @db_has_field        true
     * @db_fieldtype        timestamp
     */
    protected $solution_visible_date;

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
    public function getSettingsId()
    {
        return $this->settings_id;
    }

    /**
     * @param int $settings_id
     */
    public function setSettingsId($settings_id)
    {
        $this->settings_id = $settings_id;
    }

    /**
     * @return int
     */
    public function getRateAnswers()
    {
        return $this->rate_answers;
    }

    /**
     * @param int $rate_answers
     */
    public function setRateAnswers($rate_answers)
    {
        $this->rate_answers = $rate_answers;
    }

    /**
     * @return string
     */
    public function getDisposalDate()
    {
        return $this->disposal_date;
    }

    /**
     * @param string $disposal_date
     */
    public function setDisposalDate($disposal_date)
    {
        $this->disposal_date = $disposal_date;
    }

    /**
     * @return int
     */
    public function getSampleSolutionVisible()
    {
        return $this->sample_solution_visible;
    }

    /**
     * @param int $sample_solution_visible
     */
    public function setSampleSolutionVisible($sample_solution_visible)
    {
        $this->sample_solution_visible = $sample_solution_visible;
    }

    /**
     * @return int
     */
    public function getVisibleIfExerciseFinished()
    {
        return $this->visible_if_exercise_finished;
    }

    /**
     * @param int $visible_if_exercise_finished
     */
    public function setVisibleIfExerciseFinished($visible_if_exercise_finished)
    {
        $this->visible_if_exercise_finished = $visible_if_exercise_finished;
    }

    /**
     * @return string
     */
    public function getSolutionVisibleDate()
    {
        return $this->solution_visible_date;
    }

    /**
     * @param string $solution_visible_date
     */
    public function setSolutionVisibleDate($solution_visible_date)
    {
        $this->solution_visible_date = $solution_visible_date;
    }

}