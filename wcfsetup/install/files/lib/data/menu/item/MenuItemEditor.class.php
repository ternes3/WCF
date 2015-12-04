<?php
namespace wcf\data\menu\item;
use wcf\data\DatabaseObjectEditor;
use wcf\system\language\LanguageFactory;
use wcf\system\WCF;

/**
 * Provides functions to edit menu items.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.menu.item
 * @category	Community Framework
 */
class MenuItemEditor extends DatabaseObjectEditor {
	/**
	 * @inheritDoc
	 */
	protected static $baseClass = MenuItem::class;
	
	/**
	 * @inheritDoc
	 */
	public static function create(array $parameters = []) {
		$title = '';
		if (is_array($parameters['title'])) {
			$title = $parameters['title'];
			$parameters['title'] = '';
		}
		
		$menuItem = parent::create($parameters);
		
		if (is_array($title)) {
			if (count($title) > 1) {
				$sql = "SELECT  languageCategoryID
					FROM    wcf".WCF_N."_language_category
					WHERE   languageCategory = ?";
				$statement = WCF::getDB()->prepareStatement($sql, 1);
				$statement->execute(['wcf.menu']);
				$languageCategoryID = $statement->fetchSingleColumn();
				
				$sql = "INSERT INTO     wcf".WCF_N."_language_item
							(languageID, languageItem, languageItemValue, languageItemOriginIsSystem, languageCategoryID, packageID)
					VALUES          (?, ?, ?, ?, ?, ?)";
				$statement = WCF::getDB()->prepareStatement($sql);
				
				WCF::getDB()->beginTransaction();
				foreach ($title as $languageCode => $value) {
					$statement->execute([
						LanguageFactory::getInstance()->getLanguageByCode($languageCode)->languageID,
						'wcf.menu.menuItem' . $menuItem->itemID,
						$value,
						1,
						$languageCategoryID,
						$menuItem->packageID
					]);
				}
				WCF::getDB()->commitTransaction();
				
				$title = 'wcf.menu.menuItem' . $menuItem->itemID;
			}
			else {
				$title = reset($title);
			}
			
			$menuEditor = new self($menuItem);
			$menuEditor->update(['title' => $title]);
			$menuItem = new static::$baseClass($menuItem->itemID);
		}
		
		return $menuItem;
	}
}