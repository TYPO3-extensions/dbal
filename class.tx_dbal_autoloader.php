<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2010 Xavier Perseguers <typo3@perseguers.ch>
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*  A copy is found in the textfile GPL.txt and important notices to the license
*  from the author is found in LICENSE.txt distributed with these scripts.
*
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

/**
 * Autoloader included from Install Tool that lets DBAL load itself
 * if it makes sense.
 *
 * $Id$
 *
 * @author Xavier Perseguers <typo3@perseguers.ch>
 *
 * @package TYPO3
 * @subpackage dbal
 */
class tx_dbal_autoloader {

	/**
	 * Activates DBAL if it is supported.
	 *
	 * @param integer $step
	 * @return void
	 */
	public function execute($step) {
		t3lib_div::debug($step, 'current step');
		switch ($step) {
			case 1:
				if (!t3lib_extMgm::isLoaded('dbal') && $this->isDbalSupported()) {
					t3lib_div::debug('activated dbal');
					$this->activateDbal();
				}
				break;
		}
	}

	/**
	 * Returns TRUE if PHP modules to run DBAL are loaded.
	 *
	 * @return boolean
	 */
	protected function isDbalSupported() {
		return extension_loaded('odbc')
			|| extension_loaded('pdo')
			|| extension_loaded('oci8');
	}

	/**
	 * Activates DBAL.
	 *
	 * @return void
	 */
	protected function activateDbal() {
		$extList = t3lib_div::trimExplode(',', $GLOBALS['TYPO3_CONF_VARS']['EXT']['extList']);
		if (!t3lib_div::inArray($extList, 'adodb')) {
			$extList[] = 'adodb';
		}
		if (!t3lib_div::inArray($extList, 'dbal')) {
			$extList[] = 'dbal';
		}
		$this->updateExtensionList(implode(',', $extList));
	}

	/**
	 * Updates the list of extensions.
	 *
	 * @param string $newExtList
	 * @return void
	 */
	protected function updateExtensionList($newExtList) {
			// Instance of install tool
		$instObj = t3lib_div::makeInstance('t3lib_install');
		$instObj->allowUpdateLocalConf = 1;
		$instObj->updateIdentity = 'TYPO3 Core Update Manager';

			// Get lines from localconf file
		$lines = $instObj->writeToLocalconf_control();
		$instObj->setValueInLocalconfFile($lines, '$TYPO3_CONF_VARS[\'EXT\'][\'extList\']', $newExtList);
		$instObj->writeToLocalconf_control($lines);

		$GLOBALS['TYPO3_CONF_VARS']['EXT']['extList'] = $newExtList;
			// Make sure to get cache file for backend, not frontend
		$cacheFilePrefix = $GLOBALS['TYPO3_LOADED_EXT']['_CACHEFILE'];
		$GLOBALS['TYPO3_LOADED_EXT']['_CACHEFILE'] = str_replace('temp_CACHED_FE', 'temp_CACHED', $cacheFilePrefix);
		t3lib_extMgm::removeCacheFiles();
	}

}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/dbal/class.tx_dbal_autoloader.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/dbal/class.tx_dbal_autoloader.php']);
}

$installStep = $this->step;

	// Make instance:
$SOBE = t3lib_div::makeInstance('tx_dbal_autoloader');
$SOBE->execute($installStep);
?>