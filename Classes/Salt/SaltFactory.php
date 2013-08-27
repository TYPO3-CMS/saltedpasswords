<?php
namespace TYPO3\CMS\Saltedpasswords\Salt;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2009-2013 Marcus Krause <marcus#exp2009@t3sec.info>
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
 * Class that implements Blowfish salted hashing based on PHP's
 * crypt() function.
 *
 * @author Marcus Krause <marcus#exp2009@t3sec.info>
 */
class SaltFactory {

	/**
	 * An instance of the salted hashing method.
	 * This member is set in the getSaltingInstance() function.
	 *
	 * @var \TYPO3\CMS\Saltedpasswords\Salt\AbstractSalt
	 */
	static protected $instance = NULL;

	/**
	 * Returns list of all registered hashing methods. Used eg. in
	 * extension configuration to select the default hashing method.
	 *
	 * @return array
	 */
	static public function getRegisteredSaltedHashingMethods() {
		return array_merge(
			static::getDefaultSaltMethods(),
			(array) $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/saltedpasswords']['saltMethods']
		);
	}

	/**
	 * Returns an array with default salt methods.
	 *
	 * @return array
	 */
	static protected function getDefaultSaltMethods() {
		return array(
			'TYPO3\\CMS\\Saltedpasswords\\Salt\\Md5Salt' => 'TYPO3\\CMS\\Saltedpasswords\\Salt\\Md5Salt',
			'TYPO3\\CMS\\Saltedpasswords\\Salt\\BlowfishSalt' => 'TYPO3\\CMS\\Saltedpasswords\\Salt\\BlowfishSalt',
			'TYPO3\\CMS\\Saltedpasswords\\Salt\\PhpassSalt' => 'TYPO3\\CMS\\Saltedpasswords\\Salt\\PhpassSalt'
		);
	}


	/**
	 * Obtains a salting hashing method instance.
	 *
	 * This function will return an instance of a class that implements
	 * \TYPO3\CMS\Saltedpasswords\Salt\AbstractSalt
	 *
	 * Use parameter NULL to reset the factory!
	 *
	 * @param string $saltedHash (optional) Salted hashed password to determine the type of used method from or NULL to reset the factory
	 * @param string $mode (optional) The TYPO3 mode (FE or BE) saltedpasswords shall be used for
	 * @return \TYPO3\CMS\Saltedpasswords\Salt\AbstractSalt An instance of salting hashing method object
	 */
	static public function getSaltingInstance($saltedHash = '', $mode = TYPO3_MODE) {
		// Creating new instance when
		// * no instance existing
		// * a salted hash given to determine salted hashing method from
		// * a NULL parameter given to reset instance back to default method
		if (!is_object(self::$instance) || !empty($saltedHash) || is_NULL($saltedHash)) {
			// Determine method by checking the given hash
			if (!empty($saltedHash)) {
				$result = self::determineSaltingHashingMethod($saltedHash);
				if (!$result) {
					self::$instance = NULL;
				}
			} else {
				$classNameToUse = \TYPO3\CMS\Saltedpasswords\Utility\SaltedPasswordsUtility::getDefaultSaltingHashingMethod($mode);
				$availableClasses = static::getRegisteredSaltedHashingMethods();
				self::$instance = \TYPO3\CMS\Core\Utility\GeneralUtility::getUserObj($availableClasses[$classNameToUse]);
			}
		}
		return self::$instance;
	}

	/**
	 * Method tries to determine the salting hashing method used for given salt.
	 *
	 * Method implicitly sets the instance of the found method object in the class property when found.
	 *
	 * @param string $saltedHash
	 * @return boolean TRUE, if salting hashing method has been found, otherwise FALSE
	 */
	static public function determineSaltingHashingMethod($saltedHash) {
		$methodFound = FALSE;
		$registeredMethods = static::getRegisteredSaltedHashingMethods();
		foreach ($registeredMethods as $method) {
			$objectInstance = \TYPO3\CMS\Core\Utility\GeneralUtility::getUserObj($method);
			if ($objectInstance instanceof \TYPO3\CMS\Saltedpasswords\Salt\SaltInterface) {
				$methodFound = $objectInstance->isValidSaltedPW($saltedHash);
				if ($methodFound) {
					self::$instance = $objectInstance;
					break;
				}
			}
		}
		return $methodFound;
	}

	/**
	 * Method sets a custom salting hashing method class.
	 *
	 * @param string $resource Object resource to use (e.g. 'TYPO3\\CMS\\Saltedpasswords\\Salt\\BlowfishSalt')
	 * @return \TYPO3\CMS\Saltedpasswords\Salt\AbstractSalt An instance of salting hashing method object
	 */
	static public function setPreferredHashingMethod($resource) {
		self::$instance = NULL;
		$objectInstance = \TYPO3\CMS\Core\Utility\GeneralUtility::getUserObj($resource);
		if (is_object($objectInstance) && is_subclass_of($objectInstance, 'TYPO3\\CMS\\Saltedpasswords\\Salt\\AbstractSalt')) {
			self::$instance = $objectInstance;
		}
		return self::$instance;
	}
}
?>