<?php
/**
 * Class xaseItem
 * @author  Benjamin Seglias <bs@studer-raimann.ch>
 */

class xaseItem extends ActiveRecord
{

    /**
     * @return string
     */
    public static function returnDbTableName()
    {
        return 'rep_robj_xase_item';
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
    protected $assisted_exercise_id;

    /**
     * @var int
     *
     * @db_has_field  true
     * @db_fieldtype  integer
     * @db_length     4
     * @db_is_notnull false
     */
    protected $sample_solution_id;

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
     * @db_length           256
     * @db_is_notnull       true
     */
    protected $item_title;

    /**
     * @var string
     *
     * @db_has_field        true
     * @db_fieldtype        text
     * @db_length           4000
     * @db_is_notnull       true
     */
    protected $task;

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
    public function getAssistedExerciseId()
    {
        return $this->assisted_exercise_id;
    }

    /**
     * @param int $assisted_exercise_id
     */
    public function setAssistedExerciseId($assisted_exercise_id)
    {
        $this->assisted_exercise_id = $assisted_exercise_id;
    }

    /**
     * @return int
     */
    public function getSampleSolutionId()
    {
        return $this->sample_solution_id;
    }

    /**
     * @param int $sample_solution_id
     */
    public function setSampleSolutionId($sample_solution_id)
    {
        $this->sample_solution_id = $sample_solution_id;
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
    public function getItemTitle()
    {
        return $this->item_title;
    }

    /**
     * @param string $item_title
     */
    public function setItemTitle($item_title)
    {
        $this->item_title = $item_title;
    }

    /**
     * @return string
     */
    public function getTask()
    {
        return $this->task;
    }

    /**
     * @param string $task
     */
    public function setTask($task)
    {
        $this->task = $task;
    }

}