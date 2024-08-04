<?php
class frmtermsTest extends FRMUnitTestUtilites
{
    public function setUp()
    {
        parent::setUp();
    }

    /**
     * Test of creating items and versions in frmterms plugin
     */
    public function testVersioning()
    {
        $service = FRMTERMS_BOL_Service::getInstance();
        $sectionId = 6;

        //Remove any items
        $service->deleteItemsBySectionId($sectionId);

        $items = array(
            array(
                'sectionId' => $sectionId,
                'header' => 'header1',
                'description' => 'description1',
                'use' => 1,
                'notification' => 1,
                'email' => 1
            ),array(
                'sectionId' => $sectionId,
                'header' => 'header2',
                'description' => 'description2',
                'use' => 1,
                'notification' => 1,
                'email' => 0
            ),array(
                'sectionId' => $sectionId,
                'header' => 'header3',
                'description' => 'description3',
                'use' => 1,
                'notification' => 0,
                'email' => 1
            ),array(
                'sectionId' => $sectionId,
                'header' => 'header4',
                'description' => 'description4',
                'use' => 1,
                'notification' => 0,
                'email' => 0
            ),array(
                'sectionId' => $sectionId,
                'header' => 'header5',
                'description' => 'description5',
                'use' => 0,
                'notification' => 1,
                'email' => 1
            ),array(
                'sectionId' => $sectionId,
                'header' => 'header6',
                'description' => 'description6',
                'use' => 0,
                'notification' => 1,
                'email' => 0
            ),array(
                'sectionId' => $sectionId,
                'header' => 'header7',
                'description' => 'description7',
                'use' => 0,
                'notification' => 0,
                'email' => 1
            ),array(
                'sectionId' => $sectionId,
                'header' => 'header8',
                'description' => 'description8',
                'use' => 0,
                'notification' => 0,
                'email' => 0
            ),
        );

        //Store items in db
        foreach ($items as $itrm)
        {
            $service->addItem($itrm['sectionId'],$itrm['header'],$itrm['description'],$itrm['use'],$itrm['notification'],$itrm['email']);
        }

        //Get all items stored from db
        $allItemsStored = $service->getAllItemSorted($sectionId);
        self::assertEquals(8, count($allItemsStored));

        //Get all active items
        $activeItems = $service->getItemsUsingStatus(true, $sectionId);
        self::assertEquals(4, count($activeItems));

        //Add version by active items
        $service->addVersion($sectionId, $activeItems, false);
        $maxVersionBeforeRemove = $service->getMaxVersion($sectionId);
        $versionedItems = $service->getItemsUsingVersion($maxVersionBeforeRemove, $sectionId);
        self::assertEquals(4, count($versionedItems));

        //Remove all items that versioned
        $service->deleteVersion($sectionId, $maxVersionBeforeRemove);
        $maxVersionAfterRemove = $service->getMaxVersion($sectionId);
        self::assertEquals($maxVersionAfterRemove, $maxVersionBeforeRemove-1);

        //Remove all items that stored in db
        $service->deleteItemsBySectionId($sectionId);
        $allItemsBeforeRemove = $service->getAllItemSorted($sectionId);
        self::assertEquals(0, count($allItemsBeforeRemove));
    }
}