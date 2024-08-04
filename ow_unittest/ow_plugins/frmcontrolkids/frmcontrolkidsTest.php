<?php
class frmcontrolkidsTest extends FRMUnitTestUtilites
{
    public function setUp()
    {
        $accountType = BOL_QuestionService::getInstance()->getDefaultAccountType()->name;
        FRMSecurityProvider::createUser('kidtest', 'kid@test.com', '12345678', '1987/3/21', '1', $accountType,'c0de');
        FRMSecurityProvider::createUser('parenttest', 'parent@test.com', '12345678', '1987/3/21', '1', $accountType,'c0de');
    }

    /**
     * Test of blocking users by ip in frmblockingip plugin
     */
    public function testControlKids()
    {
        $service = FRMCONTROLKIDS_BOL_Service::getInstance();
        $oldValue = OW::getConfig()->getValue('frmcontrolkids','kidsAge');
        $currentYear = date("Y");
        $age = $currentYear - $oldValue;
        $year = $age-1;
        self::assertEquals(false, $service->isInChildhood('13-02-'.$year));
        $year = $age-2;
        self::assertEquals(false, $service->isInChildhood('13-02-'.$year));
        $year = $age+1;
        self::assertEquals(true, $service->isInChildhood('13-02-'.$year));
        $year = $age+2;
        self::assertEquals(true, $service->isInChildhood('13-02-'.$year));

        $kid = BOL_UserService::getInstance()->findByUsername('kidtest');
        $parent = BOL_UserService::getInstance()->findByUsername('parenttest');

        $service->addRelationship($kid->getId(), 'parent@test.com', false);
        self::assertEquals(true, $service->isParentExist($kid->getId(), $parent->getId()));

        $service->deleteRelationship($kid->getId());
        self::assertEquals(false, $service->isParentExist($kid->getId(), $parent->getId()));
    }

    public function tearDown()
    {
        FRMSecurityProvider::deleteUser('kidtest');
        FRMSecurityProvider::deleteUser('parenttest');
    }
}