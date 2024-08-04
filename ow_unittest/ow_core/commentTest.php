<?php
/**
 * Class commentTest
 */
class commentTest extends FRMUnitTestUtilites
{
    /**
     * @var BOL_CommentService
     */
    private $commentService;
    /**
     * @var BOL_CommentEntityDao
     */
    private $commentEntityDao;
    /**
     * @var BOL_CommentDao
     */
    private $commentDao;

    const TEST_USER1_NAME = "CommentTestUser1";
    const TEST_PASSWORD = "testUser1-password";

    private $user1;

    private $entityId1 = 0;
    private $entityId2 = 1;
    private $entityType1 = 'user-status';
    private $entityType2 = 'main-page';
    private $message = "test comment text";

    protected function setUp()
    {
        parent::setUp();
        $this->commentService = BOL_CommentService::getInstance();
        $this->commentDao = BOL_CommentDao::getInstance();
        $this->commentEntityDao = BOL_CommentEntityDao::getInstance();
        $this->user1 = $this->createUser($this::TEST_USER1_NAME);
    }

    /**
     * addComment and FindCommentCount
     */
    public function testAddComment()
    {
        $commentCount = $this->commentDao->findCommentCount($this->entityType1, $this->entityId1);
        self::assertEquals($commentCount, 0);
        $comment = $this->commentService->addComment($this->entityType1, $this->entityId1, 'blogs', $this->user1->getId(), $this->message);
        $commentCount = $this->commentDao->findCommentCount($this->entityType1, $this->entityId1);
        self::assertEquals($commentCount, 1);
        self::assertEquals($comment->getMessage(), trim(urlencode($this->message)));
        self::assertNull($comment->getAttachment());
        $commentCountOtherEntry = $this->commentDao->findCommentCount($this->entityType2, $this->entityId1);
        self::assertEquals($commentCountOtherEntry, 0);
        $commentCountOtherEntry = $this->commentDao->findCommentCount($this->entityType1, $this->entityId2);
        self::assertEquals($commentCountOtherEntry, 0);

//      findCommentCount
        $commentCountService = $this->commentService->findCommentCount($this->entityType1, $this->entityId1);
        self::assertEquals($commentCount, $commentCountService);

//      addComment with attachment
        $attachment = 'kindaAttachment';
        $comment = $this->commentService->addComment($this->entityType1, $this->entityId1, 'blogs', $this->user1->getId(), $this->message, $attachment);
        self::assertEquals($comment->getAttachment(), "kindaAttachment");
        self::assertEquals($comment->getMessage(), trim(urlencode($this->message)));

    }

    /**
     * FindComment
     */
    public function testFindComment()
    {
        $comment = $this->commentService->addComment($this->entityType1, $this->entityId1, 'blogs', $this->user1->getId(), $this->message);
        $serviceComment = $this->commentService->findComment($comment->getId());
        self::assertEquals($this->message, $serviceComment->getMessage());
    }

    /**
     * UpdateComment
     */
    public function testUpdateComment()
    {
        $comment = $this->commentService->addComment($this->entityType1, $this->entityId1, 'blogs', $this->user1->getId(), $this->message);

        $newMessageText = 'new message text';
        $comment->setMessage($newMessageText);
        $oldCommentEntityId = $comment->getCommentEntityId();
        $commentEntity = new BOL_CommentEntity();
        $commentEntity->setEntityType(trim($this->entityType2));
        $commentEntity->setEntityId((int)$this->entityId2);
        $commentEntity->setPluginKey("groups");
        $this->commentEntityDao->save($commentEntity);
        $comment->setCommentEntityId($commentEntity->getId());
        $dbComment = $this->commentService->findComment($comment->getId());
        self::assertEquals($dbComment->getMessage(), $this->message);
        self::assertEquals($dbComment->getCommentEntityId(), $oldCommentEntityId);
        $this->commentService->updateComment($comment);
        $newComment = $this->commentService->findComment($comment->getId());
        self::assertEquals($newComment->getMessage(), $newMessageText);
        self::assertEquals($newComment->getCommentEntityId(), $commentEntity->getId());
    }

    /**
     * DeleteComment
     */
    public function testDeleteComment()
    {
        $comment1 = $this->commentService->addComment($this->entityType1, $this->entityId1, 'blogs', $this->user1->getId(), $this->message);
        $comment2 = $this->commentService->addComment($this->entityType2, $this->entityId2, 'blogs', $this->user1->getId(), $this->message);
        $this->commentService->deleteComment($comment1->getId());
        $thisComment1 = $this->commentService->findComment($comment1->getId());
        self::assertNull($thisComment1);
        $thisComment2 = $this->commentService->findComment($comment2->getId());
        self::assertNotNull($thisComment2);
    }

    /**
     * FindFullCommentList
     */
    public function testFindFullCommentList()
    {

        $this->addComments();
        $list = $this->commentService->findFullCommentList($this->entityType1, $this->entityId1);
        self::assertEquals(sizeof($list), 10);
        $listEntityType2 = $this->commentService->findFullCommentList($this->entityType2, $this->entityId1);
        self::assertEquals(sizeof($listEntityType2), 5);
    }

    /**
     * FindCommentList
     */
    public function testFindCommentList()
    {
        $this->addComments();
        $list = $this->commentService->findCommentList($this->entityType1, $this->entityId1, null, 6);
        self::assertEquals(sizeof($list), 6);
    }

    /**
     * FindCommentPageCount
     */
    public function testFindCommentPageCount()
    {
        $this->addComments();
        $count = $this->commentService->findCommentPageCount($this->entityType1, $this->entityId1);
        self::assertEquals($count, 1);
        $count = $this->commentService->findCommentPageCount($this->entityType1, $this->entityId1, 3);
        self::assertEquals($count, 4);
    }

    /**
     * FindCommentedEntityCount
     */
    public function testFindCommentedEntityCount()
    {
        $this->commentService->addComment($this->entityType1, $this->entityId1, 'blogs', $this->user1->getId(), $this->message);
        $this->commentService->addComment($this->entityType1, $this->entityId2, 'blogs', $this->user1->getId(), $this->message);
        $this->commentService->addComment($this->entityType1, $this->entityId2, 'blogs', $this->user1->getId(), $this->message);
        $count = $this->commentService->findCommentedEntityCount($this->entityType2);
        self::assertEquals($count, 0);
        $count = $this->commentService->findCommentedEntityCount($this->entityType1);
        self::assertEquals($count, 2);
    }

    /**
     * DeleteEntityTypeComments
     */
    public function testDeleteEntityTypeComments()
    {
        $this->addComments();
        $comment = $this->commentService->addComment($this->entityType2, $this->entityId1, 'blogs', $this->user1->getId(), $this->message);
        $this->commentService->deleteEntityTypeComments($this->entityType2);
        $count = $this->commentEntityDao->findCommentedEntityCount($this->entityType2);
        self::assertEquals($count, 0);
        $thisComment = $this->commentService->findComment($comment->getId());
        self::assertNull($thisComment);
    }

    /**
     * DeleteEntityComments
     */
    public function testDeleteEntityComments()
    {
        $this->addComments();
        $comment = $this->commentService->addComment($this->entityType2, $this->entityId1, 'blogs', $this->user1->getId(), $this->message);
        $this->commentService->deleteEntityComments($this->entityType2, $this->entityId1);
        $listEntityType2 = $this->commentDao->findFullCommentList($this->entityType2, $this->entityId1);
        self::assertEquals(sizeof($listEntityType2), 0);
        $thisComment = $this->commentService->findComment($comment->getId());
        self::assertNull($thisComment);
        $list = $this->commentDao->findFullCommentList($this->entityType1, $this->entityId1);
        self::assertEquals(sizeof($list), 10);
    }

    /**
     * FindCommentCountForEntityList
     */
    public function testFindCommentCountForEntityList()
    {
        $this->commentService->addComment($this->entityType2, $this->entityId2, 'blogs', $this->user1->getId(), $this->message);
        $count = $this->commentService->findCommentCountForEntityList($this->entityType2, [$this->entityId1, $this->entityId2]);
        self::assertEquals($count, ["0", "1"]);
        $count = $this->commentService->findCommentCountForEntityList($this->entityType2, [$this->entityId1]);
        self::assertEquals($count, ["0"]);
    }

    /**
     * findCommentEntity and deleteCommentEntity
     */
    public function testFindDeleteCommentEntity()
    {
        $this->commentService->addComment($this->entityType2, $this->entityId2, 'blogs', $this->user1->getId(), $this->message);
        $commentEntity = $this->commentService->findCommentEntity($this->entityType2, $this->entityId2);
        $count = $this->commentDao->findCommentCount($this->entityType2, $this->entityId2);
        self::assertEquals($count, 1);
        $this->commentService->deleteCommentEntity($commentEntity->getId());
        $count = $this->commentDao->findCommentCount($this->entityType2, $this->entityId2);
        self::assertEquals($count, 0);
        $commentEntity = $this->commentService->findCommentEntity($this->entityType2, $this->entityId2);
        self::assertNull($commentEntity);
    }

    /**
     * DeletePluginComments
     */
    public function testDeletePluginComments()
    {
        $this->commentService->addComment($this->entityType2, $this->entityId2, 'news', $this->user1->getId(), $this->message);
        $this->commentService->addComment($this->entityType1, $this->entityId2, 'news', $this->user1->getId(), $this->message);
        $count = $this->commentDao->findCommentCount($this->entityType2, $this->entityId2);
        self::assertEquals($count, 1);
        $count = $this->commentDao->findCommentCount($this->entityType1, $this->entityId2);
        self::assertEquals($count, 1);
        $this->commentService->deletePluginComments('news');
        $count = $this->commentDao->findCommentCount($this->entityType2, $this->entityId2);
        self::assertEquals($count, 0);
        $count = $this->commentDao->findCommentCount($this->entityType1, $this->entityId2);
        self::assertEquals($count, 0);
    }

    /**
     * findBatchCommentsData
     */
    public function testFindBatchCommentsData()
    {
        $this->addComments();
        $entityList[] = array(
            'entityType' => $this->entityType1,
            'entityId' => $this->entityId1,
            'pluginKey' => 'blogs',
            'userId' => $this->user1->getId(),
            'countOnPage' => 2

        );
        $data = $this->commentService->findBatchCommentsData($entityList);
        self::assertArrayHasKey($this->entityType1, $data);
        $userStatus = $data[$this->entityType1][0];
        self::assertEquals($userStatus['commentsCount'], 10);
        self::assertEquals($userStatus['countOnPage'], 2);
        self::assertEquals(sizeof($userStatus['commentsList']), 2);
        $firstComment = $userStatus['commentsList'][0];
        self::assertEquals($firstComment->userId, $this->user1->getId());
        self::assertEquals($firstComment->commentEntityId, $this->commentService->findCommentEntity($this->entityType1, $this->entityId1)->getId());
        self::assertEquals($firstComment->entityType, $this->entityType1);
        self::assertEquals($firstComment->entityId, $this->entityId1);
    }

    /**
     * deleteUserComments
     */
    public function testDeleteUserComments()
    {
        $this->addComments();
        $user2 = $this->createUser("user2");
        $comment1 = $this->commentService->addComment($this->entityType1, $this->entityId1, 'blogs', $user2->getId(), $this->message);
        $comment2 = $this->commentService->addComment($this->entityType1, $this->entityId1, 'blogs', $this->user1->getId(), $this->message);
        $this->commentService->deleteUserComments($user2->getId());
        $thisComment1 = $this->commentService->findComment($comment1->getId());
        $thisComment2 = $this->commentService->findComment($comment2->getId());
        self::assertNull($thisComment1);
        self::assertNotNull($thisComment2);
        BOL_UserService::getInstance()->deleteUser($user2->getId());

    }

    /**
     * setEntityStatus
     */
    public function testSetEntityStatus()
    {
        $comment = $this->commentService->addComment($this->entityType1, $this->entityId1, 'blogs', $this->user1->getId(), $this->message);
        $commentEntity = $this->commentService->findCommentEntityById($comment->getCommentEntityId());
        self::assertEquals($commentEntity->isActive(), 1);
        $this->commentService->setEntityStatus($this->entityType1, $this->entityId1);
        $commentEntity = $this->commentService->findCommentEntityById($comment->getCommentEntityId());
        self::assertEquals($commentEntity->isActive(), 1);
        $this->commentService->setEntityStatus($this->entityType1, $this->entityId1, false);
        $commentEntity = $this->commentService->findCommentEntityById($comment->getCommentEntityId());
        self::assertEquals($commentEntity->isActive(), 0);
    }

    /**
     * deleteCommentListByIds
     */
    public function testDeleteCommentListByIds()
    {
        $comment1 = $this->commentService->addComment($this->entityType1, $this->entityId1, 'blogs', $this->user1->getId(), $this->message);
        $comment2 = $this->commentService->addComment($this->entityType2, $this->entityId1, 'blogs', $this->user1->getId(), $this->message);
        $this->commentService->deleteCommentListByIds([$comment1->getId(), $comment2->getId()]);
        $thisComment1 = $this->commentService->findComment($comment1->getId());
        self::assertNull($thisComment1);
        $thisComment2 = $this->commentService->findComment($comment2->getId());
        self::assertNull($thisComment2);
    }

    /**
     * findCommentEntityById
     */
    public function testFindCommentEntityById()
    {
        $comment = $this->commentService->addComment($this->entityType1, $this->entityId1, 'blogs', $this->user1->getId(), $this->message);
        $commentEntityId = $comment->getCommentEntityId();
        $commentEntity = $this->commentService->findCommentEntityById($commentEntityId);
        self::assertEquals($commentEntity->entityId, $this->entityId1);
        self::assertEquals($commentEntity->entityType, $this->entityType1);
    }


    /**
     * @param $userName
     * @return BOL_User
     */
    private function createUser($userName)
    {
        $userService = BOL_UserService::getInstance();
        $accountType = BOL_QuestionService::getInstance()->getDefaultAccountType()->name;
        $userService->createUser($userName, $this::TEST_PASSWORD, "{$userName}@gmail.com", $accountType, "1");
        return BOL_UserService::getInstance()->findByUsername($userName);
    }


    protected function tearDown()
    {
        parent::tearDown();
        $userService = BOL_UserService::getInstance();
        $user = $userService->findByUsername($this::TEST_USER1_NAME);
        $userService->deleteUser($user->getId());
        $this->commentDao->deleteEntityTypeComments($this->entityType2);
        $this->commentDao->deleteEntityTypeComments($this->entityType1);
        $this->commentEntityDao->deleteByEntityType($this->entityType2);
        $this->commentEntityDao->deleteByEntityType($this->entityType1);
    }

    /**
     * add 10 comments in entity1 and 5 in entity2
     */
    private function addComments()
    {
        for ($index = 1; $index <= 10; $index += 1) {
            $message = "message text for comment {$index}";
            $this->commentService->addComment($this->entityType1, $this->entityId1, 'blogs', $this->user1->getId(), $message);
        }
        for ($index = 1; $index <= 5; $index += 1) {
            $message = "message text for comment {$index} for another entity";
            $this->commentService->addComment($this->entityType2, $this->entityId1, 'blogs', $this->user1->getId(), $message);
        }
        $commentCount = $this->commentDao->findCommentCount($this->entityType1, $this->entityId1);
        self::assertEquals($commentCount, 10);
        $commentCountEntityType2 = $this->commentDao->findCommentCount($this->entityType2, $this->entityId1);
        self::assertEquals($commentCountEntityType2, 5);
    }

}