<?php
/**
 * Class    ilAssistedExercisePlugin
 * @author  Benjamin Seglias <bs@studer-raimann.ch>
 */

require_once('./Services/Repository/classes/class.ilRepositoryObjectPlugin.php');

class ilAssistedExercisePlugin extends ilRepositoryObjectPlugin
{

    const PLUGIN_PREFIX = 'xase';
    const PLUGIN_NAME = 'AssistedExercise';

    /**
     * @var ilAssistedExercisePlugin
     */
    protected static $instance;

    /**
     * @return ilAssistedExercisePlugin
     */
    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    function getPluginName()
    {
        return self::PLUGIN_NAME;
    }

    protected function uninstallCustom()
    {
        // TODO: Implement uninstallCustom() method. Remove all Data-tables and created user-files
    }

}