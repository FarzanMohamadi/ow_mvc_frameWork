<?php
/**
 * 
 * All rights reserved.
 */

/**
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @since 1.0
 */
class STORY_BOL_StorySeenDao extends OW_BaseDao
{
    private static $classInstance;

    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    public function getDtoClassName()
    {
        return 'STORY_BOL_StorySeen';
    }

    public function getTableName()
    {
        return OW_DB_PREFIX . 'story_seen';
    }

    public function isUserSawStory($userId, $storyId){
        $example = new OW_Example();
        $example->andFieldEqual('storyId', $storyId);
        $example->andFieldEqual('userId', $userId);
        return $this->findObjectByExample($example);
    }

    public function seenStory($userId, $storyId){
        $userSeen = $this->isUserSawStory($userId, $storyId);
        if(isset($userSeen)){
            return null;
        }
        $storySeen = new STORY_BOL_StorySeen();
        $storySeen->userId = $userId;
        $storySeen->storyId = $storyId;
        $storySeen->createdAt = time();
        $this->save($storySeen);
        return $storySeen;
    }

    public function findUserStoriesSeen($userId){
        $ex = new OW_Example();
        $ex->andFieldEqual('userId', $userId);
        $ex->andFieldGreaterThenOrEqual('createdAt', time() - 24 * 3600);
        $ex->setOrder('`createdAt` DESC');
        return $this->findListByExample($ex);
    }

    public function findStorySeenCount($storyId)
    {
        $example = new OW_Example();
        $example->andFieldEqual('storyId', $storyId);
        return $this->countByExample($example);
    }

    public function findStorySeens($storyId, $first, $count)
    {
        $example = new OW_Example();
        $example->andFieldEqual('storyId', $storyId);
        $example->setLimitClause(($first-1)*$count, $count);

        return $this->findListByExample($example);
    }
}
