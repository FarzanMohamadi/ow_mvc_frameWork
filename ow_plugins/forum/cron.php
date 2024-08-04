<?php
/**
 * Forum cron job.
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow.ow_plugins.forum
 * @since 1.0
 */
class FORUM_Cron extends OW_Cron
{
    const TOPICS_DELETE_LIMIT = 5;
    
    const MEDIA_DELETE_LIMIT = 10;

    const UPDATE_SEARCH_INDEX_MAX_TIME = 10;

    public function __construct()
    {
        parent::__construct();

        $this->addJob('topicsDeleteProcess', 1);
        
        $this->addJob('tempTopicsDeleteProcess', 60);

        $this->addJob('updateSearchIndex', 60);
    }

    public function run()
    {
        
    }

    /**
     * Update search index
     * 
     * @throws Exception
     * @return void
     */
    public function updateSearchIndex()
    {
        $config = OW::getConfig();

        // in process
        $maxExecutionTime = time() + self::UPDATE_SEARCH_INDEX_MAX_TIME;

        while ( time() <  $maxExecutionTime )
        {
            // do we have any queue
            if ( null == ($firstQueue = FORUM_BOL_UpdateSearchIndexService::getInstance()->findFirstQueue()) )
            {
                break;
            }

            // process queue
            switch ($firstQueue->type)
            {
                 // delete topic
                 case FORUM_BOL_UpdateSearchIndexService::DELETE_TOPIC :
                     $result = $this->deleteTopicFromSearchIndex($firstQueue->entityId);
                     break;

                 // update topic
                 case FORUM_BOL_UpdateSearchIndexService::UPDATE_TOPIC :
                     $result = $this->updateTopicInSearchIndex($firstQueue->entityId);
                     break;

                 // update topic posts
                 case FORUM_BOL_UpdateSearchIndexService::UPDATE_TOPIC_POSTS :
                     $result = $this->
                         updateTopicPostsInSearchIndex($firstQueue->entityId, $firstQueue, $maxExecutionTime);
                     break;

                 // delete group
                 case FORUM_BOL_UpdateSearchIndexService::DELETE_GROUP :
                     $result = $this->deleteGroupFromSearchIndex($firstQueue->entityId);
                     break;

                 // update group
                 case FORUM_BOL_UpdateSearchIndexService::UPDATE_GROUP :
                     $result = $this->
                         updateGroupInSearchIndex($firstQueue->entityId, $firstQueue, $maxExecutionTime);
                     break;

                 default :
                     $result = true;
             }

             if ( !$result )
             {
                 break;
             }

            // task successfully completed 
            FORUM_BOL_UpdateSearchIndexService::getInstance()->deleteQueue($firstQueue);
        }

        // Check if the job should be run again
        if (FORUM_BOL_UpdateSearchIndexService::getInstance()->findFirstQueue() != null) {
            OW::getEventManager()->trigger(new OW_Event(FORUM_BOL_ForumService::EVENT_UPDATE_SEARCH_INDEX_QUEUED));
        }
    }

    /**
     * Update group
     * 
     * @param integer $groupId
     * @param FORUM_BOL_UpdateSearchIndex $firstQueue
     * @param integer $maxExecutionTime
     * @return boolean
     */
    private function updateGroupInSearchIndex( $groupId, FORUM_BOL_UpdateSearchIndex $firstQueue, $maxExecutionTime )
    {
        $forumService = FORUM_BOL_ForumService::getInstance();

        // get the group info
        $group = $forumService->findGroupById($groupId);

        if ( $group )
        {
            $topicPage = 1;
            $lastEntityId = $firstQueue->lastEntityId; 

            // get group's topics 
            while ( time() <  $maxExecutionTime )
            {
                if ( null == ($topics = $forumService->
                        getSimpleGroupTopicList($group->id, $topicPage, $lastEntityId)) )
                {
                    return true;
                }

                // add topics into the group
                foreach ($topics as $topic)
                {
                    $this->getTextSearchService()->addTopic($topic);

                    // remember the last post id
                    $firstQueue->lastEntityId = $topic->id;
                    FORUM_BOL_UpdateSearchIndexService::getInstance()->saveQueue($firstQueue);

                    // update topic's posts
                    FORUM_BOL_UpdateSearchIndexService::getInstance()->addQueue($topic->id,
                        FORUM_BOL_UpdateSearchIndexService::UPDATE_TOPIC_POSTS, FORUM_BOL_UpdateSearchIndexService::HIGH_PRIORITY);
                }

                $topicPage++;
            }

            return false;
        }

        return true;
    }

    /**
     * Delete group from the search index
     * 
     * @param integer $groupId
     * @return boolean
     */
    private function deleteGroupFromSearchIndex( $groupId )
    {
        $this->getTextSearchService()->deleteGroup($groupId);

        FORUM_BOL_UpdateSearchIndexService::getInstance()->addQueue($groupId,
            FORUM_BOL_UpdateSearchIndexService::UPDATE_GROUP, FORUM_BOL_UpdateSearchIndexService::HIGH_PRIORITY);

        return true;
    }

    /**
     * Delete topic from the search index
     * 
     * @param integer $topicId
     * @return boolean
     */
    private function deleteTopicFromSearchIndex( $topicId )
    {
        $this->getTextSearchService()->deleteTopic($topicId);

        FORUM_BOL_UpdateSearchIndexService::getInstance()->addQueue($topicId,
            FORUM_BOL_UpdateSearchIndexService::UPDATE_TOPIC, FORUM_BOL_UpdateSearchIndexService::HIGH_PRIORITY);

        return true;
    }

    /**
     * Update topic
     * 
     * @param integer $topicId
     * @return boolean
     */
    private function updateTopicInSearchIndex( $topicId )
    {
        $forumService = FORUM_BOL_ForumService::getInstance();

        // get the topic info
        $topic = $forumService->findTopicById($topicId);

        if ( $topic )
        {
            // add the topic
            $this->getTextSearchService()->addTopic($topic);

            FORUM_BOL_UpdateSearchIndexService::getInstance()->addQueue($topicId,
                FORUM_BOL_UpdateSearchIndexService::UPDATE_TOPIC_POSTS, FORUM_BOL_UpdateSearchIndexService::HIGH_PRIORITY);
        }

        return true;
    }

    /**
     * Update topic posts
     * 
     * @param integer $topicId
     * @param FORUM_BOL_UpdateSearchIndex $firstQueue
     * @param integer $maxExecutionTime
     * @return boolean
     */
    private function updateTopicPostsInSearchIndex( $topicId, FORUM_BOL_UpdateSearchIndex $firstQueue, $maxExecutionTime )
    {
        $forumService = FORUM_BOL_ForumService::getInstance();

        // get the topic info
        $topic = $forumService->findTopicById($topicId);

        if ( $topic )
        {
            $postPage = 1;
            $lastEntityId = $firstQueue->lastEntityId;

            // get topic's post list
            while ( time() <  $maxExecutionTime )
            {
                if ( null == ($posts = $forumService->
                        getSimpleTopicPostList($topic->id, $postPage, $lastEntityId)) )
                {
                    return true;
                }

                // add posts into the topic
                foreach ($posts as $post)
                {
                    $this->getTextSearchService()->saveOrUpdatePost($post, true);

                    // remember the last post id
                    $firstQueue->lastEntityId = $post->id;
                    FORUM_BOL_UpdateSearchIndexService::getInstance()->saveQueue($firstQueue);
                }

                $postPage++;
            }

            return false;
        }

        return true;
    }

    /**
     * Get text search service
     * 
     * @return FORUM_BOL_TextSearchService
     */
    private function getTextSearchService()
    {
        return FORUM_BOL_TextSearchService::getInstance();
    }

    public function tempTopicsDeleteProcess()
    {
        $forumService = FORUM_BOL_ForumService::getInstance();
        
        $tmpTopics = $forumService->findTemporaryTopics(self::TOPICS_DELETE_LIMIT);
        
        if ( !$tmpTopics )
        {
            return;
        }
        
        foreach ( $tmpTopics as $topic )
        {
            $forumService->deleteTopic($topic['id']);
        }

        // Check if the job should be run again
        if (count($tmpTopics) == self::TOPICS_DELETE_LIMIT) {
            OW::getEventManager()->trigger(new OW_Event(FORUM_BOL_ForumService::EVENT_TEMP_TOPICS_DELETE_INCOMPLETE));
        }
    }

    public function topicsDeleteProcess()
    {
        $config = OW::getConfig();
        
        // check if uninstall is in progress
        if ( !$config->getValue('forum', 'uninstall_inprogress') )
        {
            return;
        }
        
        // check if cron queue is not busy
        if ( $config->getValue('forum', 'uninstall_cron_busy') )
        {
            return;
        }
 
        if ( !$config->getValue('forum', 'delete_search_index_cron') )
        {
            $this->getTextSearchService()->deleteAllEntities();
            $config->saveConfig('forum', 'delete_search_index_cron', 1, null, false);
        }

        $config->saveConfig('forum', 'uninstall_cron_busy', 1, null, false);
        
        $forumService = FORUM_BOL_ForumService::getInstance();
        $forumService->deleteTopics(self::TOPICS_DELETE_LIMIT);
        
        $mediaPanelService = BOL_MediaPanelService::getInstance();
        $mediaPanelService->deleteImages('forum', self::MEDIA_DELETE_LIMIT);
        
        $config->saveConfig('forum', 'uninstall_cron_busy', 0, null, false);
        
        if ( (int) $forumService->countAllTopics() + (int) $mediaPanelService->countGalleryImages('forum') == 0 )
        {
            $config->saveConfig('forum', 'uninstall_inprogress', 0, null, false);
            BOL_PluginService::getInstance()->uninstall('forum');

            FORUM_BOL_ForumService::getInstance()->setMaintenanceMode(false);
        } else {
            // The job should be run again
            OW::getEventManager()->trigger(new OW_Event(FORUM_BOL_ForumService::EVENT_UNINSTALL_IN_PROGRESS));
        }
    }
}