<?php
class TagServiceTest extends FRMUnitTestUtilites
{

    private $tagService;
    private $tag;
    private $tag2;
    private $entityTag;
    private $firstEntityId;
    private $firstEntityType;
    private $secondEntityId;
    private $secondEntityType;
    protected function setUp()
    {
        parent::setUp();

        // Create one tag for two entityTag and save them to DB

        $this->tagService = BOL_TagService::getInstance();

        $this->firstEntityId = 1;
        $this->firstEntityType = "tag-entityType1";
        $this->secondEntityId = 2;
        $this->secondEntityType = "tag-entityType2";

        $this->tag = new BOL_Tag;
        $this->tag->setLabel("test-taglabel");
        $this->tagService->saveTag($this->tag);

        $this->tag2 = new BOL_Tag;
        $this->tag2->setLabel("test-taglabel2");
        $this->tagService->saveTag($this->tag2);

        $this->createEntityTagData($this->firstEntityId, $this->firstEntityType);
        $this->createEntityTagData($this->secondEntityId, $this->secondEntityType);
    }

    public function createEntityTagData($entityId, $entityType)
    {
        $this->entityTag = new BOL_EntityTag;
        $this->entityTag->setEntityId($entityId);
        $this->entityTag->setEntityType($entityType);
        $this->entityTag->setTagId($this->tag->getId());
        $this->tagService->saveEntityTag($this->entityTag);
    }

    public function createEntityTag2Data($entityId, $entityType)
    {
        $this->entityTag = new BOL_EntityTag;
        $this->entityTag->setEntityId($entityId);
        $this->entityTag->setEntityType($entityType);
        $this->entityTag->setTagId($this->tag2->getId());
        $this->tagService->saveEntityTag($this->entityTag);
    }

    public function testTagService()
    {
        $tags = $this->tagService->findEntityTags($this->firstEntityId, $this->firstEntityType);
        self::assertGreaterThanOrEqual(1, count($tags));

        $tags = $this->tagService->findEntityTags($this->secondEntityId, $this->secondEntityType);
        self::assertGreaterThanOrEqual(1, count($tags));

        $this->tagService->deleteEntityTags($this->firstEntityId, $this->firstEntityType);
        $tags = $this->tagService->findEntityTags($this->firstEntityId, $this->firstEntityType);
        self::assertEquals(0, count($tags));

        $this->tagService->deleteEntityTypeTags($this->secondEntityType);
        $tags = $this->tagService->findEntityTags($this->firstEntityId, $this->firstEntityType);
        self::assertEquals(0, count($tags));

        $this->createEntityTagData($this->firstEntityId, $this->firstEntityType);
        $this->tagService->setEntityStatus($this->firstEntityType, $this->firstEntityId, false);
        $entityCount = $this->tagService->findEntityCountByTag($this->firstEntityType, $this->tag->getLabel());
        self::assertEquals(0, $entityCount);
        $this->tagService->setEntityStatus($this->firstEntityType, $this->firstEntityId, true);

        $this->createEntityTagData($this->secondEntityId, $this->secondEntityType);
        $this->createEntityTagData($this->firstEntityId, $this->secondEntityType);
        $entityList =  $this->tagService->findEntityListByTag($this->firstEntityType, $this->tag->getLabel(), 0, 10);
        self::assertEquals(1, count($entityList));
        $entityList =  $this->tagService->findEntityListByTag($this->secondEntityType, $this->tag->getLabel(), 0, 10);
        self::assertEquals(2, count($entityList));
        $this->tagService->deleteEntityTypeTags($this->secondEntityType);

        $this->tagService->updateEntityTags($this->firstEntityId, $this->firstEntityType, array($this->tag->getLabel() . 'edited'));
        $tags = $this->tagService->findEntityTags($this->firstEntityId, $this->firstEntityType);
        self::assertEquals($this->tag->getLabel() . 'edited', $tags[0]->getLabel());
        $this->tagService->deleteEntityTypeTags($this->firstEntityType);

        $this->createEntityTagData($this->firstEntityId, $this->firstEntityType);
        $this->createEntityTagData($this->secondEntityId, $this->firstEntityType);
        $tags = $this->tagService->findTagListByEntityIdList($this->firstEntityType, array($this->firstEntityId, $this->secondEntityId));
        self::assertEquals($this->tag->getLabel(), $tags[$this->firstEntityId][0]);
        self::assertEquals($this->tag->getLabel(), $tags[$this->secondEntityId][0]);
        self::assertEquals(2, count($tags));

        $this->createEntityTag2Data($this->firstEntityId, $this->firstEntityType);
        $tags = $this->tagService->findMostPopularTags($this->firstEntityType, 1);
        self::assertEquals($this->tag->getLabel(), $tags[0]['label']);
        $tags = $this->tagService->findEntityTagsWithPopularity($this->secondEntityId, $this->firstEntityType);
        self::assertEquals($this->tag->getLabel(), $tags[0]['label']);
    }

    public function tearDown()
    {
        $this->tagService->deleteEntityTags($this->firstEntityId, $this->firstEntityType);
        $this->tagService->deleteEntityTags($this->secondEntityId, $this->firstEntityType);
        BOL_TagDao::getInstance()->deleteById($this->tag->getId());
        BOL_TagDao::getInstance()->deleteById($this->tag2->getId());
    }
}