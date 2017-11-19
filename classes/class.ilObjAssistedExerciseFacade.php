<?php
require_once "./Customizing/global/plugins/Services/Repository/RepositoryObject/AssistedExercise/classes/class.ilAssistedExercisePlugin.php";
require_once "./Customizing/global/plugins/Services/Repository/RepositoryObject/AssistedExercise/classes/class.ilObjAssistedExercise.php";
require_once "./Customizing/global/plugins/Services/Repository/RepositoryObject/AssistedExercise/classes/Setting/class.xaseSettingM1.php";
require_once "./Customizing/global/plugins/Services/Repository/RepositoryObject/AssistedExercise/classes/Setting/class.xaseSettingM2.php";


class ilObjAssistedExerciseFacade {

	/**
	 * @var ilAssistedExercisePlugin
	 */
	private $plugin;
	/**
	 * @var ilObjAssistedExercise
	 */
	private $il_object;
	/**
	 * @var xaseSettingM1|xaseSettingM2
	 */
	private $setting;
	/**
	 * @var \ILIAS\DI\Container
	 */
	private $dic;



	private function __construct(ilAssistedExercisePlugin $plugin,
		                       $il_object,
		                       $setting,
		                       $dic) {
		$this->plugin = $plugin;
		$this->il_object = $il_object;
		$this->setting = $setting;
		$this->dic = $dic;

		$this->dic['tpl']->getStandardTemplate();
	}


	/**
	 * @param $ref_id
	 *
	 * @return ilObjAssistedExerciseFacade
	 */
	public static function getInstance($ref_id = 0) {
		global $DIC;

		$plugin = ilAssistedExercisePlugin::getInstance();

		if(ilObject::_lookupType($ref_id, true) == 'xase') {
			$il_object = new ilObjAssistedExercise($ref_id);
		} else {
			$il_object = NULL;
		}

		$xase_setting = xaseSettingFactory::findOrGetInstanceByRefId($ref_id);


		$obj_facade = new ilObjAssistedExerciseFacade($plugin,$il_object,$xase_setting,$DIC);

		return $obj_facade;
	}



	/**
	 * @param string $tab_id
	 * @param string $lng_var
	 * @param string $a_gui_class
	 * @param string $cmd
	 * @param string frame target
	 */
	public function addTab($tab_id, $lng_var, $a_gui_class, $cmd, $frame = "") {
		$this->dic->tabs()->addTab($tab_id, $this->getLanguageValue($lng_var), $this->dic->ctrl()->getLinkTarget(new $a_gui_class(), $cmd, $frame));
	}

	/**
	 * @var \ILIAS\DI\Container
	 */
	public function getDic() {
		return $this->dic;
	}

	public function getUser() {
		return $this->dic->user();
	}

	/**
	 * @return ilTabsGUI
	 */
	public function getTabsGUI() {
		return $this->dic->tabs();
	}

	/**
	 * @return	\ilCtrl
	 */
	public function getCtrl() {
		return $this->dic->ctrl();
	}

	/**
	 * @return	ilTemplate
	 */
	public function getTpl() {
		return $this->dic['tpl'];
	}

	public function getAccess() {
		return ilObjAssistedExerciseAccess::getInstance($this,$this->getUser()->getId());

	}

	/**
	* @param string $a_class
	*/
	public function forwardCommandByClass($a_class) {
		$gui_object = new $a_class();
		$this->dic->ctrl()->forwardCommand($gui_object);
	}

	/**
	 * @return ilAssistedExercisePlugin
	 */
	public function getPlugin() {
		return $this->plugin;
	}

	/**
	 * @param $lng_key
	 *
	 * @return string
	 */
	public function getLanguageValue($lng_key) {
		return $this->plugin->txt($lng_key);
	}

	/**
	 * @return int
	 */
	public function getIlObjRefId() {
		return $this->il_object->getRefId();
	}

	/**
	 * @return int
	 */
	public function getIlObjObId() {
		return $this->il_object->getId();
	}


	/**
	 * @return string
	 */
	public function getIlObjTitle() {
		return $this->il_object->getTitle();
	}


	/**
	 * @param string $il_obj_title
	 */
	public function setIlObjTitle($il_obj_title) {
		$this->il_object->setTitle($il_obj_title);
	}


	/**
	 * @return string
	 */
	public function getIlObjDescription() {
		return $this->il_object->getDescription();
	}

	/**
	 * @param string $il_obj_desc
	 */
	public function setIlObjDescription($il_obj_desc) {
		$this->il_object->setDescription($il_obj_desc);
	}


	/**
	 * @return xaseSettingM1|xaseSettingM2
	 */
	public function getSetting() {
		return $this->setting;
	}



	public function store() {
		$this->il_object->update();

		$this->setting->setAssistedExerciseObjectId($this->il_object->getId());

		//consistency of setting table
		if($this->setting->getIsTimeLimited() == 0) {
			$this->setting->setStartDate(NULL);
			$this->setting->setEndDate(NULL);
			$this->setting->setAlwaysVisible(0);
		}
		$mode = $this->setting->getModus();
		switch($mode) {
			case xaseSetting::MODUS1;

				if($this->setting->getRateAnswers() == 0) {
					$this->setting->setDisposalDate(NULL);
					$this->setting->setVotingPointsEnabled(0);
				}
				if($this->setting->getVotingPointsEnabled() == 0) {
					$this->setting->setVotingPointsPercentage(0);
				}
				if($this->setting->getSampleSolutionVisible() == 0) {
					$this->setting->setVisibleIfExerciseFinished(0);
					$this->setting->setSolutionVisibleDate(NULL);
				}
				break;
			case xaseSetting::MODUS2;
				$this->setting->setRateAnswers(0);
				$this->setting->setDisposalDate(NULL);
				$this->setting->setSampleSolutionVisible(0);
				$this->setting->setVisibleIfExerciseFinished(0);
				$this->setting->setSolutionVisibleDate(NULL);
				$this->setting->setVotingPointsEnabled(0);
				$this->setting->setVotingPointsPercentage(0);
				break;
		}
		$this->setting->store();
	}
}