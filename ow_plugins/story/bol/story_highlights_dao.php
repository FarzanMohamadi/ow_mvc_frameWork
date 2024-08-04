<?php

/**
 * 
 * All rights reserved.
 */

/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.story
 * @since 1.0
 */

class STORY_BOL_StoryHighlightsDao extends OW_BaseDao
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
        return 'STORY_BOL_StoryHighlights';
    }

    public function getTableName()
    {
        return OW_DB_PREFIX . 'story_highlights';
    }

    public function findHighlightByStoryId($storyId){
        $ex = new OW_Example();
        $ex->andFieldEqual('storyId', $storyId);
        return $this->findObjectByExample($ex);
    }


    public function addHighlight($userId, $storyId, $categoryId){

        $storyHighlight = new STORY_BOL_StoryHighlights();
        $storyHighlight->userId = $userId;
        $storyHighlight->storyId = $storyId;
        $storyHighlight->categoryId = $categoryId;
        $this->save($storyHighlight);
        return $storyHighlight;
    }

    public function findHighlightListByUserId($userId){
        $ex = new OW_Example();
        $ex->andFieldEqual('userId', $userId);
        return $this->findListByExample($ex);
    }

    public function findUserHighlightListByCategoryId($userId,$categoryId){
        $ex = new OW_Example();
        $ex->andFieldEqual('userId', $userId);
        $ex->andFieldEqual('categoryId', $categoryId);
        return $this->findListByExample($ex);
    }

    public function findHighlight($userId, $storyId, $categoryId){
        $ex = new OW_Example();
        $ex->andFieldEqual('categoryId', $categoryId);
        $ex->andFieldEqual('userId', $userId);
        $ex->andFieldEqual('storyId', $storyId);
        return $this->findObjectByExample($ex);
    }

    public function findHighlightById($highlightId){
        $ex = new OW_Example();
        $ex->andFieldEqual('id', $highlightId);
        return $this->findObjectByExample($ex);
    }

    public function removeHighlightById($highlightId){
        $ex = new OW_Example();
        $ex->andFieldEqual('id', $highlightId);
        return $this->deleteByExample($ex);
    }

    public function removeHighlightsByCategoryId($categoryId){
        $ex = new OW_Example();
        $ex->andFieldEqual('categoryId', $categoryId);
        return $this->deleteByExample($ex);
    }


}

