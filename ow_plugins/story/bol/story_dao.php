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
class STORY_BOL_StoryDao extends OW_BaseDao
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
        return 'STORY_BOL_Story';
    }

    public function getTableName()
    {
        return OW_DB_PREFIX . 'story';
    }

    public function findUserStories($userId){
        $ex = new OW_Example();
        $ex->andFieldEqual('userId', $userId);
        $ex->setOrder('`createdAt` DESC');
        return $this->findListByExample($ex);
    }

    public function findUserStoriesByCount($userId, $from = 0, $count = 50)
    {
        $sql = " SELECT * FROM `".$this->getTableName()."`
                WHERE `userId` = :userId 
                ORDER BY `createdAt` DESC
                LIMIT :from, :count";
        return $this->dbo->queryForObjectList($sql, $this->getDtoClassName(), array('userId'=>$userId, 'from'=>$from, 'count'=>$count));
    }

    public function findStoriesById($storyIds){
        $ex = new OW_Example();
        $ex->andFieldInArray('id', $storyIds);
        $ex->setOrder('`createdAt` DESC');
        return $this->findListByExample($ex);
    }
    
    public function isActiveStory($storyId){
        $ex = new OW_Example();
        $ex->andFieldEqual('id', $storyId);
        $ex->andFieldGreaterThenOrEqual('createdAt', time() - 24 * 3600);
        $story = $this->findObjectByExample($ex);
        return $story ? $story : false ;
    }

    public function saveStory($userId, $attachmentId, $costumeStyles=null,$thumbnailId=null){
        $story = new STORY_BOL_Story();
        $story->userId = $userId;
        $story->attachmentId = $attachmentId;
        $story->costumeStyles = $costumeStyles;
        $story->thumbnailId = $thumbnailId;
        $story->createdAt = time();
        $this->save($story);
        return $story;
    }

    public function deleteStory($storyId){
        $story = new STORY_BOL_Story();
        $story->id = $storyId;
        $this->delete($story);
        return true;
    }

    /***
     * @param $followingUsersIds
     * @return array
     */
    public function findFollowingStories($followingUsersIds) {
        $ex = new OW_Example();
        $ex->andFieldInArray('userId', $followingUsersIds);
        $ex->andFieldGreaterThenOrEqual('createdAt', time() - 24 * 3600);
        $ex->setOrder('`createdAt` DESC');
        return $this->findListByExample($ex);
    }
}
