<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * Date: 7/12/2017
 * Time: 12:52 PM
 */
class frmhashtagTest extends FRMUnitTestUtilites
{
    private static $TAG_PREFIX = 'test_hashtag_';
    /**
     * @var FRMHASHTAG_BOL_Service
     */
    private $hashtagService;
    /**
     * @var FRMHASHTAG_BOL_TagDao
     */
    private $tagDao;

    protected function setUp()
    {
        parent::setUp();
        $this->hashtagService = FRMHASHTAG_BOL_Service::getInstance();
        $this->tagDao = FRMHASHTAG_BOL_TagDao::getInstance();
    }

    public function test()
    {
        $entityId1 = 0;
        $entityId2 = 1;
        $entityType = 'user-status';
        $tagName1 = uniqid(self::$TAG_PREFIX);
        $this->hashtagService->add_hashtag($tagName1,$entityId1,$entityType);
        $tag = $this->getTag($tagName1);
        self::assertTrue(isset($tag));
        self::assertEquals($tagName1,$tag->tag);

        $this->hashtagService->add_hashtag($tagName1,$entityId2,$entityType);
        $tag = $this->getTag($tagName1);
        self::assertTrue(isset($tag));
        self::assertEquals($tagName1,$tag->tag);
        self::assertEquals(2,$tag->count);

        $result = $this->hashtagService->findTags($tagName1,2);
        self::assertEquals(1,sizeof($result));
        self::assertEquals($tagName1,$result[0]['tag']);
        self::assertEquals(2,$result[0]['count']);
        $entities = array();
        $list = $this->hashtagService->findEntitiesByTag($tagName1, $entityType);
        foreach ($list as $key => $item)
            $entities[] = $item['id'];
        self::assertEquals(2,sizeof($entities));
        self::assertTrue(in_array($entityId1,$entities));
        self::assertTrue(in_array($entityId2,$entities));

        $entityCount = $this->hashtagService->findEntityCountByTag($tagName1);
        self::assertEquals(1,sizeof($entityCount));
        self::assertEquals(2,$entityCount[$entityType]);

        $tagName2 = uniqid(self::$TAG_PREFIX);
        $tagName3 = uniqid(self::$TAG_PREFIX);
        $content = 'تست #'.$tagName2.' در افزونه هشتگ #'.$tagName3.' و #'.$tagName2;

        $reflector = new ReflectionObject($this->hashtagService);
        $method = $reflector->getMethod('findAndAddTagsFromContent');
        $method->setAccessible(true);
        $method->invoke($this->hashtagService,$content,$entityId1,$entityType);

        $tag = $this->getTag($tagName2);
        self::assertTrue(isset($tag));
        self::assertEquals($tagName2,$tag->tag);
        self::assertEquals(1,$tag->count);
        $tag = $this->getTag($tagName3);
        self::assertTrue(isset($tag));
        self::assertEquals($tagName3,$tag->tag);
        self::assertEquals(1,$tag->count);

        $reflector = new ReflectionObject($this->hashtagService);
        $method = $reflector->getMethod('findAndReplaceTagsFromView');
        $method->setAccessible(true);
        $replacedContent = $method->invoke($this->hashtagService,$content);

        $url1 = OW::getRouter()->urlForRoute('frmhashtag.tag', array('tag'=>$tagName2));
        $link1 = '<a class="frmhashtag_tag english_tag" href="'.$url1.'">#'.$tagName2.'</a>';
        self::assertContains($link1,$replacedContent);

        $url2 = OW::getRouter()->urlForRoute('frmhashtag.tag', array('tag'=>$tagName3));
        $link2 = '<a class="frmhashtag_tag english_tag" href="'.$url2.'">#'.$tagName3.'</a>';
        self::assertContains($link2,$replacedContent);

        $tagName4 = uniqid(self::$TAG_PREFIX.'نیم‌فاصله_');
        $this->hashtagService->add_hashtag($tagName4,$entityId1,$entityType);
        $tag = $this->getTag($tagName4);
        self::assertTrue(isset($tag));
        self::assertEquals($tagName4,$tag->tag);

        $tagName5 = uniqid(self::$TAG_PREFIX);
        $content = 'تست (#'.$tagName5.') تست با پرانتز';

        $reflector = new ReflectionObject($this->hashtagService);
        $method = $reflector->getMethod('findAndAddTagsFromContent');
        $method->setAccessible(true);
        $method->invoke($this->hashtagService,$content,$entityId1,$entityType);

        $tag = $this->getTag($tagName5);
        self::assertTrue(isset($tag));
        self::assertEquals($tagName5,$tag->tag);

    }

    private function deleteAll()
    {
        $example = new OW_Example();
        $example->andFieldLike('tag',self::$TAG_PREFIX.'%');
        $idList = $this->tagDao->findIdListByExample($example);
        $example = new OW_Example();
        $example->andFieldInArray('tagId',$idList);
        FRMHASHTAG_BOL_EntityDao::getInstance()->deleteByExample($example);
        $this->tagDao->deleteByIdList($idList);
    }

    /**
     * @param string $tagName
     * @return FRMHASHTAG_BOL_Tag
     */
    private function getTag($tagName)
    {
        $example = new OW_Example();
        $example->andFieldEqual('tag',$tagName);
        return $this->tagDao->findObjectByExample($example);
    }

    protected function tearDown()
    {
        parent::tearDown();
        $this->deleteAll();
    }

}