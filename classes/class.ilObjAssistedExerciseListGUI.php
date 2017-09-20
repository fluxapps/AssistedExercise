<?php

/**
 * Class    ilObjAssistedExerciseListGUI
 * @author  Benjamin Seglias <bs@studer-raimann.ch>
 */

require_once('./Services/Repository/classes/class.ilObjectPluginListGUI.php');

/**
 * Class ilObjAssistedExerciseListGUI
 */
class ilObjAssistedExerciseListGUI extends ilObjectPluginListGUI
{

    function initType()
    {
        $this->setType(ilAssistedExercisePlugin::PLUGIN_PREFIX);
    }

    function getGuiClass()
    {
        return ilObjAssistedExerciseGUI::class;
    }

    function initCommands()
    {
        $this->timings_enabled = false;
        $this->subscribe_enabled = false;
        $this->payment_enabled = false;
        $this->link_enabled = false;
        $this->info_screen_enabled = true;
        $this->delete_enabled = true;

        // Should be overwritten according to status
        $this->cut_enabled = false;
        $this->copy_enabled = false;

        return array(
            array(
                'permission' => 'read',
                'cmd' => 'index',
                'default' => 'true'
            )
        );
    }
}