<?php
namespace wcf\acp\form;
use wcf\data\box\Box;
use wcf\data\box\BoxAction;
use wcf\form\AbstractForm;
use wcf\system\exception\IllegalLinkException;
use wcf\system\language\LanguageFactory;
use wcf\system\WCF;

/**
 * Shows the box edit form.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.form
 * @category	Community Framework
 */
class BoxEditForm extends BoxAddForm {
	/**
	 * @inheritDoc
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.cms.box.list';
	
	/**
	 * box id
	 * @var integer
	 */
	public $boxID = 0;
	
	/**
	 * box object
	 * @var Box
	 */
	public $box = null;
	
	/**
	 * @inheritDoc
	 */
	public function readParameters() {
		parent::readParameters();
	
		if (isset($_REQUEST['id'])) $this->boxID = intval($_REQUEST['id']);
		$this->box = new Box($this->boxID);
		if (!$this->box->boxID) {
			throw new IllegalLinkException();
		}
		if ($this->box->isMultilingual) $this->isMultilingual = 1;
	}
	
	/**
	 * @inheritDoc
	 */
	protected function validateName() {
		if (mb_strtolower($this->name) != mb_strtolower($this->box->name)) {
			parent::validateName();
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function save() {
		AbstractForm::save();
		
		$content = array();
		if ($this->isMultilingual) {
			foreach (LanguageFactory::getInstance()->getLanguages() as $language) {
				$content[$language->languageID] = [
					'title' => (!empty($_POST['title'][$language->languageID]) ? $_POST['title'][$language->languageID] : ''),
					'content' => (!empty($_POST['content'][$language->languageID]) ? $_POST['content'][$language->languageID] : ''),
				];
			}
		}
		else {
			$content[0] = [
				'title' => (!empty($_POST['title'][0]) ? $_POST['title'][0] : ''),
				'content' => (!empty($_POST['content'][0]) ? $_POST['content'][0] : ''),
			];
		}
		
		$this->objectAction = new BoxAction([$this->box], 'update', ['data' => array_merge($this->additionalFields, [
			'name' => $this->name,
			'isMultilingual' => $this->isMultilingual,
			'boxType' => $this->boxType,
			'position' => $this->position,
			'showOrder' => $this->showOrder,
			'visibleEverywhere' => $this->visibleEverywhere,
			'cssClassName' => $this->cssClassName,
			'showHeader' => $this->showHeader,
			'className' => $this->className
		]), 'content' => $content]);
		$this->objectAction->executeAction();
		
		// call saved event
		$this->saved();
		
		// show success
		WCF::getTPL()->assign('success', true);
	}
	
	/**
	 * @inheritDoc
	 */
	public function readData() {
		parent::readData();
	
		if (empty($_POST)) {
			$this->name = $this->box->name;
			$this->boxType = $this->box->boxType;
			$this->position = $this->box->position;
			$this->showOrder = $this->box->showOrder;
			$this->cssClassName = $this->box->cssClassName;
			$this->className = $this->box->className;
			if ($this->box->showHeader) $this->showHeader = 1;
			if ($this->box->visibleEverywhere) $this->visibleEverywhere = 1;
			
			foreach ($this->box->getBoxContent() as $languageID => $content) {
				$this->title[$languageID] = $content['title'];
				$this->content[$languageID] = $content['content'];
			}
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'action' => 'edit',
			'boxID' => $this->boxID,
			'box' => $this->box
		));
	}
}