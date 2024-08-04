<?php
class frmPasswordStrengthMeterTest extends FRMUnitTestUtilites
{
    public function setUp()
    {
        parent::setUp();
    }

    /**
     * Test of validating password's security
     */
    public function testValidatingPasswordSecurity()
    {

        $handler = FRMPASSWORDSTRENGTHMETER_BOL_Service::getInstance();
        $minimumCharacter = 8;

        $acceptablePasswordForExcelentStrength = array('test1234A', 'Atest1234', '1234test!', '@12345678A', 'salamtest123A', 'CapitalPassword123');
        $acceptablePasswordForGoodStrength = array_merge($acceptablePasswordForExcelentStrength, array('test1234', 'a12345678', '1234test', '12345678a', '@12345678', '1234test!'));
        $acceptablePasswordForWeakStrength = array_merge($acceptablePasswordForGoodStrength, array('testtesttesttest', 'testtest123', 'test1234', '12345678', 'THISISONLYCAPITALCHARACTER'));
        $acceptablePasswordForPoorStrength = array_merge($acceptablePasswordForWeakStrength, array('test', 'testtest', 'thisisatestforpoortype'));

        $unacceptablePasswordForWeakStrength = array('testtest', 'testtesttest', 'test', '1234567', 'test123');
        $unacceptablePasswordForGoodStrength = array_merge($unacceptablePasswordForWeakStrength, array('testtesttesttest', 'testtesttesttesttesttesttesttest', '12345678', 'THISISONLYCAPITALCHARACTER'));
        $unacceptablePasswordForExcelentStrength = array_merge($unacceptablePasswordForGoodStrength, array('@12345678', '!12345678', '@12345678@'));

        //Checking Poor Type
        //acceptablePasswordForPoorStrength
        $minimumRequirementPasswordStrength = 1;
        for ($i = 0; $i < sizeof($acceptablePasswordForPoorStrength); $i++) {
            self::assertEquals(true, $handler->isPasswordSecure($acceptablePasswordForPoorStrength[$i], $minimumRequirementPasswordStrength, $minimumCharacter));
        }

        //Checking Weak Type
        //unacceptablePasswordForWeakStrength
        $minimumRequirementPasswordStrength = 2;
        for ($i = 0; $i < sizeof($unacceptablePasswordForWeakStrength); $i++) {
            self::assertEquals(false, $handler->isPasswordSecure($unacceptablePasswordForWeakStrength[$i], $minimumRequirementPasswordStrength, $minimumCharacter));
        }

        //acceptablePasswordForWeakStrength
        $minimumRequirementPasswordStrength = 2;
        for ($i = 0; $i < sizeof($acceptablePasswordForWeakStrength); $i++) {
            self::assertEquals(true, $handler->isPasswordSecure($acceptablePasswordForWeakStrength[$i], $minimumRequirementPasswordStrength, $minimumCharacter));
        }


        //Checking Good Type
        //unacceptablePasswordForGoodStrength
        $minimumRequirementPasswordStrength = 3;
        for ($i = 0; $i < sizeof($unacceptablePasswordForGoodStrength); $i++) {
            self::assertEquals(false, $handler->isPasswordSecure($unacceptablePasswordForGoodStrength[$i], $minimumRequirementPasswordStrength, $minimumCharacter));
        }

        //acceptablePasswordForGoodStrength
        $minimumRequirementPasswordStrength = 3;
        for ($i = 0; $i < sizeof($acceptablePasswordForGoodStrength); $i++) {
            self::assertEquals(true, $handler->isPasswordSecure($acceptablePasswordForGoodStrength[$i], $minimumRequirementPasswordStrength, $minimumCharacter));
        }

        //Checking Excellent Type
        //unacceptablePasswordForExcelentStrength
        $minimumRequirementPasswordStrength = 4;
        for ($i = 0; $i < sizeof($unacceptablePasswordForExcelentStrength); $i++) {
            self::assertEquals(false, $handler->isPasswordSecure($unacceptablePasswordForExcelentStrength[$i], $minimumRequirementPasswordStrength, $minimumCharacter));
        }

        //acceptablePasswordForExcelentStrength
        $minimumRequirementPasswordStrength = 4;
        for ($i = 0; $i < sizeof($acceptablePasswordForExcelentStrength); $i++) {
            self::assertEquals(true, $handler->isPasswordSecure($acceptablePasswordForExcelentStrength[$i], $minimumRequirementPasswordStrength, $minimumCharacter));
        }
    }
}