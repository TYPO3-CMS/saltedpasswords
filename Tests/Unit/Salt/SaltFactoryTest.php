<?php
namespace TYPO3\CMS\Saltedpasswords\Tests\Unit\Salt;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use TYPO3\CMS\Core\Crypto\Random;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * Test case
 */
class SaltFactoryTest extends UnitTestCase
{
    /**
     * Keeps instance of object to test.
     *
     * @var \TYPO3\CMS\Saltedpasswords\Salt\SaltInterface
     */
    protected $objectInstance = null;

    /**
     * Sets up the fixtures for this testcase.
     */
    protected function setUp()
    {
        $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['saltedpasswords'] = [
            'BE' => [
                'saltedPWHashingMethod' => \TYPO3\CMS\Saltedpasswords\Salt\Pbkdf2Salt::class,
                'forceSalted' => 0,
                'onlyAuthService' => 0,
                'updatePasswd' => 1,
            ],
            'FE' => [
                'saltedPWHashingMethod' => \TYPO3\CMS\Saltedpasswords\Salt\Pbkdf2Salt::class,
                'forceSalted' => 0,
                'onlyAuthService' => 0,
                'updatePasswd' => 1,
            ],
        ];
        $this->objectInstance = \TYPO3\CMS\Saltedpasswords\Salt\SaltFactory::getSaltingInstance();
    }

    /**
     * @test
     */
    public function objectInstanceNotNull()
    {
        $this->assertNotNull($this->objectInstance);
    }

    /**
     * @test
     */
    public function objectInstanceImplementsInterface()
    {
        $this->assertInstanceOf(\TYPO3\CMS\Saltedpasswords\Salt\SaltInterface::class, $this->objectInstance);
    }

    /**
     * @test
     */
    public function abstractComposedSaltBase64EncodeReturnsProperLength()
    {
        // set up an instance that extends AbstractComposedSalt first
        $saltPbkdf2 = '$pbkdf2-sha256$6400$0ZrzXitFSGltTQnBWOsdAw$Y11AchqV4b0sUisdZd0Xr97KWoymNE0LNNrnEgY4H9M';
        $this->objectInstance = \TYPO3\CMS\Saltedpasswords\Salt\SaltFactory::getSaltingInstance($saltPbkdf2);

        // 3 Bytes should result in a 6 char length base64 encoded string
        // used for MD5 and PHPass salted hashing
        $byteLength = 3;
        $reqLengthBase64 = (int)ceil($byteLength * 8 / 6);
        $randomBytes = (new Random())->generateRandomBytes($byteLength);
        $this->assertTrue(strlen($this->objectInstance->base64Encode($randomBytes, $byteLength)) == $reqLengthBase64);
        // 16 Bytes should result in a 22 char length base64 encoded string
        // used for Blowfish salted hashing
        $byteLength = 16;
        $reqLengthBase64 = (int)ceil($byteLength * 8 / 6);
        $randomBytes = (new Random())->generateRandomBytes($byteLength);
        $this->assertTrue(strlen($this->objectInstance->base64Encode($randomBytes, $byteLength)) == $reqLengthBase64);
    }

    /**
     * @test
     */
    public function objectInstanceForMD5Salts()
    {
        $saltMD5 = '$1$rasmusle$rISCgZzpwk3UhDidwXvin0';
        $this->objectInstance = \TYPO3\CMS\Saltedpasswords\Salt\SaltFactory::getSaltingInstance($saltMD5);
        $this->assertTrue(get_class($this->objectInstance) == \TYPO3\CMS\Saltedpasswords\Salt\Md5Salt::class || is_subclass_of($this->objectInstance, \TYPO3\CMS\Saltedpasswords\Salt\Md5Salt::class));
        $this->assertInstanceOf(\TYPO3\CMS\Saltedpasswords\Salt\AbstractComposedSalt::class, $this->objectInstance);
    }

    /**
     * @test
     */
    public function objectInstanceForBlowfishSalts()
    {
        $saltBlowfish = '$2a$07$abcdefghijklmnopqrstuuIdQV69PAxWYTgmnoGpe0Sk47GNS/9ZW';
        $this->objectInstance = \TYPO3\CMS\Saltedpasswords\Salt\SaltFactory::getSaltingInstance($saltBlowfish);
        $this->assertTrue(get_class($this->objectInstance) == \TYPO3\CMS\Saltedpasswords\Salt\BlowfishSalt::class || is_subclass_of($this->objectInstance, \TYPO3\CMS\Saltedpasswords\Salt\BlowfishSalt::class));
        $this->assertInstanceOf(\TYPO3\CMS\Saltedpasswords\Salt\AbstractComposedSalt::class, $this->objectInstance);
    }

    /**
     * @test
     */
    public function objectInstanceForPhpassSalts()
    {
        $saltPhpass = '$P$CWF13LlG/0UcAQFUjnnS4LOqyRW43c.';
        $this->objectInstance = \TYPO3\CMS\Saltedpasswords\Salt\SaltFactory::getSaltingInstance($saltPhpass);
        $this->assertTrue(get_class($this->objectInstance) == \TYPO3\CMS\Saltedpasswords\Salt\PhpassSalt::class || is_subclass_of($this->objectInstance, \TYPO3\CMS\Saltedpasswords\Salt\PhpassSalt::class));
        $this->assertInstanceOf(\TYPO3\CMS\Saltedpasswords\Salt\AbstractComposedSalt::class, $this->objectInstance);
    }

    /**
     * @test
     */
    public function objectInstanceForPbkdf2Salts()
    {
        $saltPbkdf2 = '$pbkdf2-sha256$6400$0ZrzXitFSGltTQnBWOsdAw$Y11AchqV4b0sUisdZd0Xr97KWoymNE0LNNrnEgY4H9M';
        $this->objectInstance = \TYPO3\CMS\Saltedpasswords\Salt\SaltFactory::getSaltingInstance($saltPbkdf2);
        $this->assertTrue(get_class($this->objectInstance) == \TYPO3\CMS\Saltedpasswords\Salt\Pbkdf2Salt::class || is_subclass_of($this->objectInstance, \TYPO3\CMS\Saltedpasswords\Salt\Pbkdf2Salt::class));
        $this->assertInstanceOf(\TYPO3\CMS\Saltedpasswords\Salt\AbstractComposedSalt::class, $this->objectInstance);
    }

    /**
     * @test
     */
    public function objectInstanceForPhpPasswordHashBcryptSalts()
    {
        $saltBcrypt = '$2y$12$Tz.al0seuEgRt61u0bzqAOWu67PgG2ThG25oATJJ0oS5KLCPCgBOe';
        $this->objectInstance = \TYPO3\CMS\Saltedpasswords\Salt\SaltFactory::getSaltingInstance($saltBcrypt);
        $this->assertInstanceOf(\TYPO3\CMS\Saltedpasswords\Salt\BcryptSalt::class, $this->objectInstance);
    }

    /**
     * @test
     */
    public function objectInstanceForPhpPasswordHashArgon2iSalts()
    {
        $saltArgon2i = '$argon2i$v=19$m=8,t=1,p=1$djZiNkdEa3lOZm1SSmZsdQ$9iiRjpLZAT7kfHwS1xU9cqSU7+nXy275qpB/eKjI1ig';
        $this->objectInstance = \TYPO3\CMS\Saltedpasswords\Salt\SaltFactory::getSaltingInstance($saltArgon2i);
        $this->assertInstanceOf(\TYPO3\CMS\Saltedpasswords\Salt\Argon2iSalt::class, $this->objectInstance);
    }

    /**
     * @test
     */
    public function resettingFactoryInstanceSucceeds()
    {
        $defaultClassNameToUse = \TYPO3\CMS\Saltedpasswords\Utility\SaltedPasswordsUtility::getDefaultSaltingHashingMethod();
        if ($defaultClassNameToUse == \TYPO3\CMS\Saltedpasswords\Salt\Md5Salt::class) {
            $saltedPW = '$P$CWF13LlG/0UcAQFUjnnS4LOqyRW43c.';
        } else {
            $saltedPW = '$1$rasmusle$rISCgZzpwk3UhDidwXvin0';
        }
        $this->objectInstance = \TYPO3\CMS\Saltedpasswords\Salt\SaltFactory::getSaltingInstance($saltedPW);
        // resetting
        $this->objectInstance = \TYPO3\CMS\Saltedpasswords\Salt\SaltFactory::getSaltingInstance(null);
        $this->assertTrue(get_class($this->objectInstance) == $defaultClassNameToUse || is_subclass_of($this->objectInstance, $defaultClassNameToUse));
    }
}
