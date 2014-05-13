<?php
namespace wcf\system\condition;
use wcf\data\condition\Condition;
use wcf\data\user\group\UserGroup;
use wcf\data\user\User;
use wcf\data\user\UserList;
use wcf\system\exception\UserInputException;
use wcf\util\ArrayUtil;

/**
 * Condition implementation for the user group a user is a member or no member of.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2014 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.condition
 * @category	Community Framework
 */
class UserGroupCondition extends AbstractMultipleFieldsCondition implements IUserCondition {
	/**
	 * @see	\wcf\system\condition\AbstractMultipleFieldsCondition::$descriptions
	 */
	protected $descriptions = array(
		'groupIDs' => 'wcf.user.condition.groupIDs.description',
		'notGroupIDs' => 'wcf.user.condition.notGroupIDs.description'
	);
	
	/**
	 * ids of the selected user groups the user has to be member of
	 * @var	array<integer>
	 */
	protected $groupIDs = array();
	
	/**
	 * @see	\wcf\system\condition\AbstractMultipleFieldsCondition::$labels
	 */
	protected $labels = array(
		'groupIDs' => 'wcf.user.condition.groupIDs',
		'notGroupIDs' => 'wcf.user.condition.notGroupIDs'
	);
	
	/**
	 * ids of the selected user groups the user may not be member of
	 * @var	array<integer>
	 */
	protected $notGroupIDs = array();
	
	/**
	 * selectable user groups
	 * @var	array<\wcf\data\user\group\UserGroup>
	 */
	protected $userGroups = null;
	
	/**
	 * @see	\wcf\system\condition\IUserCondition::addUserCondition()
	 */
	public function addUserCondition(Condition $condition, UserList $userList) {
		if ($condition->groupIDs !== null) {
			$userList->getConditionBuilder()->add('user_table.userID IN (SELECT userID FROM wcf'.WCF_N.'_user_to_group WHERE groupID IN (?) GROUP BY userID HAVING COUNT(userID) = ?)', array($condition->groupIDs, count($condition->groupIDs)));
		}
		if ($condition->notGroupIDs !== null) {
			$userList->getConditionBuilder()->add('user_table.userID NOT IN (SELECT userID FROM wcf'.WCF_N.'_user_to_group WHERE groupID IN (?))', array($condition->notGroupIDs));
		}
	}
	
	/**
	 * @see	\wcf\system\condition\IUserCondition::checkUser()
	 */
	public function checkUser(Condition $condition, User $user) {
		$groupIDs = $user->getGroupIDs();
		if (!empty($condition->groupIDs) && count(array_diff($condition->groupIDs, $groupIDs))) {
			return false;
		}
		
		if (!empty($condition->notGroupIDs) && count(array_intersect($condition->notGroupIDs, $groupIDs))) {
			return false;
		}
		
		return true;
	}
	
	/**
	 * @see	\wcf\system\condition\ICondition::getData()
	 */
	public function getData() {
		$data = array();

		if (!empty($this->groupIDs)) {
			$data['groupIDs'] = $this->groupIDs;
		}
		if (!empty($this->notGroupIDs)) {
			$data['notGroupIDs'] = $this->notGroupIDs;
		}
		
		if (!empty($data)) {
			return $data;
		}
		
		return null;
	}
	
	/**
	 * @see	\wcf\system\condition\ICondition::getHTML()
	 */
	public function getHTML() {
		return <<<HTML
<dl{$this->getErrorClass('groupIDs')}>
	<dt>{$this->getLabel('groupIDs')}</dt>
	<dd>
		{$this->getOptionElements('groupIDs')}
		{$this->getDescriptionElement('groupIDs')}
		{$this->getErrorMessageElement('groupIDs')}
	</dd>
</dl>
<dl{$this->getErrorClass('notGroupIDs')}>
	<dt>{$this->getLabel('notGroupIDs')}</dt>
	<dd>
		{$this->getOptionElements('notGroupIDs')}
		{$this->getDescriptionElement('notGroupIDs')}
		{$this->getErrorMessageElement('notGroupIDs')}
	</dd>
</dl>
HTML;
	}
	
	/**
	 * Returns the option elements for the user group selection.
	 * 
	 * @param	string		$identifier
	 * @return	string
	 */
	protected function getOptionElements($identifier) {
		$userGroups = $this->getUserGroups();
		
		$returnValue = "";
		foreach ($userGroups as $userGroup) {
			$returnValue .= "<label><input type=\"checkbox\" name=\"".$identifier."[]\" value=\"".$userGroup->groupID."\"".(in_array($userGroup->groupID, $this->$identifier) ? ' checked="checked"' : "")." /> ".$userGroup->getName()."</label>";
		}
		
		return $returnValue;
	}
	
	/**
	 * Returns the selectable user groups.
	 * 
	 * @return	array<\wcf\data\user\group\UserGroup>
	 */
	protected function getUserGroups() {
		if ($this->userGroups == null) {
			$this->userGroups = UserGroup::getGroupsByType(array(UserGroup::OTHER));
			foreach ($this->userGroups as $key => $userGroup) {
				if (!$userGroup->isAccessible()) {
					unset($this->userGroups[$key]);
				}
			}
			
			uasort($this->userGroups, function(UserGroup $groupA, UserGroup $groupB) {
				return strcmp($groupA->getName(), $groupB->getName());
			});
		}
		
		return $this->userGroups;
	}
	
	/**
	 * @see	\wcf\system\condition\ICondition::readFormParameters()
	 */
	public function readFormParameters() {
		if (isset($_POST['groupIDs'])) $this->groupIDs = ArrayUtil::toIntegerArray($_POST['groupIDs']);
		if (isset($_POST['notGroupIDs'])) $this->notGroupIDs = ArrayUtil::toIntegerArray($_POST['notGroupIDs']);
	}
	
	/**
	 * @see	\wcf\system\condition\ICondition::reset()
	 */
	public function reset() {
		$this->groupIDs = array();
		$this->notGroupIDs = array();
	}
	
	/**
	 * @see	\wcf\system\condition\ICondition::setData()
	 */
	public function setData(Condition $condition) {
		if ($condition->groupIDs !== null) {
			$this->groupIDs = $condition->groupIDs;
		}
		if ($condition->notGroupIDs !== null) {
			$this->notGroupIDs = $condition->notGroupIDs;
		}
	}
	
	/**
	 * @see	\wcf\system\condition\ICondition::validate()
	 */
	public function validate() {
		$userGroups = $this->getUserGroups();
		foreach ($this->groupIDs as $groupID) {
			if (!isset($userGroups[$groupID])) {
				$this->errorMessages['groupIDs'] = 'wcf.global.form.error.noValidSelection';
				
				throw new UserInputException('groupIDs', 'noValidSelection');
			}
		}
		foreach ($this->notGroupIDs as $groupID) {
			if (!isset($userGroups[$groupID])) {
				$this->errorMessages['notGroupIDs'] = 'wcf.global.form.error.noValidSelection';
				
				throw new UserInputException('notGroupIDs', 'noValidSelection');
			}
		}
		
		if (count(array_intersect($this->notGroupIDs, $this->groupIDs))) {
			$this->errorMessages['notGroupIDs'] = 'wcf.user.condition.notGroupIDs.error.groupIDsIntersection';
			
			throw new UserInputException('notGroupIDs', 'groupIDsIntersection');
		}
	}
}