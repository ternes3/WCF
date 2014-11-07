<?php
use wcf\data\package\Package;
use wcf\system\WCF;

/**
 * @author	Alexander Ebert
 * @copyright	2001-2014 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @category	Community Framework
 */

if (Package::compareVersion(WCF_VERSION, '2.0.10') === -1) {
	if (WCF::getLanguage()->getFixedLanguageCode() == 'de') {
		throw new SystemException("Die Aktualisierung erfordert WoltLab Community Framework (com.woltlab.wcf) in Version 2.0.10 oder hoeher");
	}
	else {
		throw new SystemException("Update requires at least WoltLab Community Framework (com.woltlab.wcf) in version 2.0.10 or higher");
	}
}
