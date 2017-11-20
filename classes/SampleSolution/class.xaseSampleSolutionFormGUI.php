<?php
/**
 * Class xaseSampleSolutionFormGUI
 *
 * @author: Benjamin Seglias   <bs@studer-raimann.ch>
 */

class xaseSampleSolutionFormGUI extends ilPropertyFormGUI {

	/**
	 * @var ilObjAssistedExercise
	 */
	public $object;
	/**
	 * @var xaseQuestion
	 */
	public $xase_question;
	/**
	 * @var xaseSampleSolutionGUI
	 */
	protected $parent_gui;
	/**
	 * @var xaseSampleSolution
	 */
	public $xase_sample_solution;
	/**
	 * @var xaseSetting
	 */
	public $xase_settings;
	/**
	 * @var \ILIAS\DI\Container
	 */
	protected $dic;
	/**
	 * @var ilTemplate
	 */
	protected $tpl;
	/**
	 * @var ilTabsGUI
	 */
	protected $tabs;
	/**
	 * @var ilCtrl
	 */
	protected $ctrl;
	/**
	 * @var ilAssistedExercisePlugin
	 */
	protected $pl;

	/**
	 * @var xaseAnswerAccess
	 */
	protected $answer_access;

	/**
	 * @var ilObjAssistedExerciseFacade
	 */
	protected $obj_facade;



	public function __construct($parentGUI, xaseQuestion $xaseQuestion) {
		$this->obj_facade = ilObjAssistedExerciseFacade::getInstance($_GET['ref_id']);

		global $DIC;
		$this->dic = $DIC;
		$this->tpl = $this->dic['tpl'];
		$this->tabs = $DIC->tabs();
		$this->ctrl = $this->dic->ctrl();
		$this->pl = ilAssistedExercisePlugin::getInstance();
		$this->object = ilObjectFactory::getInstanceByRefId($_GET['ref_id']);
		$this->parent_gui = $parentGUI;
		$this->xase_question = $xaseQuestion;

		parent::__construct();
	}


	public function show_sample_solution() {
		$this->setTarget('_top');
		$this->setFormAction($this->obj_facade->getCtrl()->getFormAction($this->parent_gui));

		$this->setTitle($this->obj_facade->getLanguageValue('sample_solution'));

		$item = new ilNonEditableValueGUI($this->obj_facade->getLanguageValue('task_title'));
		$item->setValue($this->xase_question->getTitle());
		$this->addItem($item);

		$item = new ilNonEditableValueGUI($this->obj_facade->getLanguageValue('task'));
		$item->setValue($this->xase_question->getQuestiontext());
		$this->addItem($item);

		$sample_solution = new ilNonEditableValueGUI($this->obj_facade->getLanguageValue('answer'));
		$sample_solution->setValue($this->xase_question->getSampleSolution());
		$this->addItem($sample_solution);

		$this->addCommandButton(xaseSampleSolutionGUI::CMD_CANCEL, $this->obj_facade->getLanguageValue("cancel"));
	}
}