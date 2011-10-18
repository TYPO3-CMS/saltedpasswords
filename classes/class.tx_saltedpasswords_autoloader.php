<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2011 Helmut Hummel <helmut.hummel@typo3.org>
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
 * Autoloader included from Install Tool that lets saltedpasswords load itself
 *
 * @author Helmut Hummel <helmut.hummel@typo3.org>
 *
 * @package TYPO3
 * @subpackage saltedpasswords
 */
class tx_saltedpasswords_autoloader {

	/**
	 * Activates saltedpasswords if it is supported.
	 *
	 * @param tx_install $instObj
	 * @return void
	 */
	public function execute(tx_install $instObj) {
		switch ($instObj->step) {
			case 4:
				if (!t3lib_extMgm::isLoaded('saltedpasswords') && $this->isSaltedPasswordsSupported()) {
					$this->activateSaltedPasswords();
				}
				break;
		}
	}

	/**
	 * Checks whether the OpenSSL PHP extension is working properly.
	 *
	 * Before automatically enabling saltedpasswords, we check for a working OpenSSL PHP extension. As we enable rsaauth
	 * in the process of automatically enabling saltedpasswords, working OpenSSL is a requirement for this.
	 * Availability of the command line openssl binary is not checked here, thus saltedpasswords is NOT enabled
	 * automatically in this case.
	 *
	 * @return boolean TRUE, in case of OpenSSL works and requirements for saltedpasswords are met.
	 * @see tx_rsaauth_php_backend
	 */
	protected function isSaltedPasswordsSupported() {
		$isSupported = FALSE;

		if (is_callable('openssl_pkey_new')) {
			$testKey = @openssl_pkey_new();
			if (is_resource($testKey)) {
				openssl_free_key($testKey);
				$isSupported = TRUE;
			}
		}

		return $isSupported;
	}

	/**
	 * Activates saltedpasswords.
	 *
	 * @return void
	 */
	protected function activateSaltedPasswords() {
		$extList = t3lib_div::trimExplode(',', $GLOBALS['TYPO3_CONF_VARS']['EXT']['extList']);
		if (!t3lib_div::inArray($extList, 'rsaauth')) {
			$extList[] = 'rsaauth';
		}
		if (!t3lib_div::inArray($extList, 'saltedpasswords')) {
			$extList[] = 'saltedpasswords';
		}
		$this->updateExtensionList(implode(',', $extList));
		$GLOBALS['typo3CacheManager']->getCache('cache_phpcode')->flushByTag('t3lib_autoloader');
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
		$saltedPasswordDefaultConfiguration =
				'a:2:{s:3:"FE.";a:2:{s:7:"enabled";s:1:"1";s:21:"saltedPWHashingMethod";s:28:"tx_saltedpasswords_salts_md5";}s:3:"BE.";a:2:{s:7:"enabled";s:1:"1";s:21:"saltedPWHashingMethod";s:28:"tx_saltedpasswords_salts_md5";}}';

		$instObj->setValueInLocalconfFile($lines, '$TYPO3_CONF_VARS[\'EXT\'][\'extList\']', $newExtList);
		$instObj->setValueInLocalconfFile($lines, '$TYPO3_CONF_VARS[\'EXT\'][\'extConf\'][\'saltedpasswords\']', $saltedPasswordDefaultConfiguration);
		$instObj->setValueInLocalconfFile($lines, '$TYPO3_CONF_VARS[\'BE\'][\'loginSecurityLevel\'] ', 'rsa');
		$instObj->setValueInLocalconfFile($lines, '$TYPO3_CONF_VARS[\'FE\'][\'loginSecurityLevel\'] ', 'rsa');

		$result = $instObj->writeToLocalconf_control($lines);
		if ($result === 'nochange') {
			$message = 'Saltedpasswords was not loaded.';
			if (!@is_writable(PATH_typo3conf)) {
				$message .= ' ' . PATH_typo3conf . ' is not writable!';
			}
			throw new RuntimeException($message, 1310931362);
		}

		$GLOBALS['TYPO3_CONF_VARS']['EXT']['extList'] = $newExtList;

			// Make sure to get cache file for backend, not frontend
		$cacheFilePrefix = t3lib_extMgm::getCacheFilePrefix();
		$GLOBALS['TYPO3_LOADED_EXT']['_CACHEFILE'] = str_replace('temp_CACHED_FE', 'temp_CACHED', $cacheFilePrefix);
		t3lib_extMgm::removeCacheFiles();
	}

}


if (defined('TYPO3_MODE') && isset($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/saltedpasswords/classes/class.tx_saltedpasswords_autoloader.php'])) {
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/saltedpasswords/classes/class.tx_saltedpasswords_autoloader.php']);
}

// Make instance:
$SOBE = t3lib_div::makeInstance('tx_saltedpasswords_autoloader');
$SOBE->execute($this);
?>