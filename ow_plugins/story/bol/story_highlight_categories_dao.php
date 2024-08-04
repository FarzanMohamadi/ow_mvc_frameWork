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

class STORY_BOL_StoryHighlightCategoriesDao extends OW_BaseDao
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
        return 'STORY_BOL_StoryHighlightCategories';
    }

    public function getTableName()
    {
        return OW_DB_PREFIX . 'story_highlight_categories';
    }


    public function findHighlightCategoryById($id){
        $ex = new OW_Example();
        $ex->andFieldEqual('id', $id);
        return $this->findObjectByExample($ex);
    }


    public function addNewHighlightCategory($userId, $categoryTitle, $avatar){
        $highlightCategory = new STORY_BOL_StoryHighlightCategories();
        $highlightCategory->userId = $userId;
        $highlightCategory->categoryTitle = $categoryTitle;
        $highlightCategory->categoryAvatar = $avatar;
        $highlightCategory->createTime = time();
        $this->save($highlightCategory);
        return $highlightCategory;
    }

    public function assignHighlightCategoryAvatar($userId, $categoryId, $categoryAvatarId){
        $category = $this->findHighlightCategoryById($categoryId);
        if($userId == $category->userId){
            $category->categoryAvatar = $categoryAvatarId;
            $this->save($category);
            return $category;
        }else{
            return false;
        }

    }

    public function findCategoryListByUserId($userId){
        $ex = new OW_Example();
        $ex->andFieldEqual('userId', $userId);
        return $this->findListByExample($ex);
    }


    public function removeCategoryById($categoryId){
        $ex = new OW_Example();
        $ex->andFieldEqual('id', $categoryId);
        return $this->deleteByExample($ex);
    }
}

