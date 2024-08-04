<?php
class searchEntityDaoTest extends FRMUnitTestUtilites
{

    public function testFindEntitiesCountByText()
    {
        $entities = array(
            array(
                'entity_type' => 'forum_post',
                'entity_id' => 1,
                'text' => 'forum post title',
                'tags' => array(
                    'forum_post'
                ),
                'active' => false
            ),
            array(
                'entity_type' => 'forum_post',
                'entity_id' => 1,
                'text' => 'forum post body',
                'tags' => array(
                    'forum_post'
                ),
                'active' => false
            ),
            array(
                'entity_type' => 'forum_topic',
                'entity_id' => 1,
                'text' => 'forum topic title',
                'tags' => array(
                    'forum_topic'
                ),
                'active' => true
            )
        );

        // add test entities
        foreach ($entities as $entitiy)
        {
            OW::getTextSearchManager()->
            addEntity($entitiy['entity_type'], $entitiy['entity_id'], $entitiy['text'],  time(), $entitiy['tags']);

            // deactivate an entity
            if (!$entitiy['active']) {
                OW::getTextSearchManager()->
                setEntitiesStatus($entitiy['entity_type'], $entitiy['entity_id'], BASE_CLASS_AbstractSearchStorage::ENTITY_STATUS_NOT_ACTIVE);
            }
        }
        // search only active entities
        //test camel case
        self::assertEquals(1, BOL_SearchEntityDao::getInstance()->findEntitiesCountByText('FORum'));
        self::assertEquals(1, BOL_SearchEntityDao::getInstance()->findEntitiesCountByText('foru'));

        //search an non existing entity
        self::assertEquals(0, BOL_SearchEntityDao::getInstance()->findEntitiesCountByText('non existing entity'));
    }

    public function testfindEntitiesByText()
    {
        $entities = array(
            array(
                'entity_type' => 'forum_post',
                'entity_id' => 1,
                'text' => 'forum post title',
                'tags' => array(
                    'forum_post'
                ),
                'active' => false
            ),
            array(
                'entity_type' => 'forum_post',
                'entity_id' => 1,
                'text' => 'forum post body',
                'tags' => array(
                    'forum_post'
                ),
                'active' => false
            ),
            array(
                'entity_type' => 'forum_topic',
                'entity_id' => 1,
                'text' => 'forum topic title',
                'tags' => array(
                    'forum_topic'
                ),
                'active' => true
            )
        );

        // add test entities
        foreach ($entities as $entitiy)
        {
            OW::getTextSearchManager()->
            addEntity($entitiy['entity_type'], $entitiy['entity_id'], $entitiy['text'],  time(), $entitiy['tags']);

            // deactivate an entity
            if (!$entitiy['active']) {
                OW::getTextSearchManager()->
                setEntitiesStatus($entitiy['entity_type'], $entitiy['entity_id'], BASE_CLASS_AbstractSearchStorage::ENTITY_STATUS_NOT_ACTIVE);
            }
        }
        //test camel case
        $entity=BOL_SearchEntityDao::getInstance()->findEntitiesByText('FORUM',0,100);
        self::assertInternalType('array', $entity);
        self::assertEquals(1,count($entity));

        $entity=BOL_SearchEntityDao::getInstance()->findEntitiesByText('FORum',0,10);
        self::assertInternalType('array', $entity);
        self::assertEquals(1, count($entity));

        $currentEntity = array_shift($entity);
        self::assertEquals('forum_topic', $currentEntity['entityType']);
        self::assertEquals('1', $currentEntity['entityId']);


        //search an non existing entity
        $entity=BOL_SearchEntityDao::getInstance()->findEntitiesByText('non existing entity',0,100);
        self::assertInternalType('array', $entity);
        self::assertEquals(0,count($entity));

    }

    /**
     * Tear down
     */
    protected function tearDown()
    {
        OW::getTextSearchManager()->deleteAllEntities();
    }
}