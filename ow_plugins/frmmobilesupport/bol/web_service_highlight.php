<?php

/**
 * 
 * All rights reserved.
 */

/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.story.highlight
 * @since 1.0
 */

class FRMMOBILESUPPORT_BOL_WebServiceHighlight
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

    private function __construct()
    {
    }

    /***
     * @return array
     */
    public function addNewHighlightCategory() {
        if(!OW::getUser()->isAuthenticated()){
            return array('valid' => false, 'message' => 'authorization_error');
        }

        $user = OW::getUser()->getId();
        if(!isset($user)){
            return array('valid' => false, 'message' => 'authorization_error');
        }

        $label = null;
        if(isset($_POST['label'])){
            $label = strip_tags($_POST['label']);
        }else{
            return array('valid' => false, 'message' => 'Undefined_highlight_category_label');
        }

        $attachmentId = $this->categoryAvatarAttachmentIdGenerator();
        if( isset($attachmentId['valid']) && $attachmentId['valid'] == false ){
            return array('valid' => false, 'message' => $attachmentId['message']);
        }

        $category = STORY_BOL_StoryHighlightCategoriesDao::getInstance()->addNewHighlightCategory(OW::getUser()->getId(), $label, $attachmentId);
        return array('status' => true, 'category_id' => $category->id);
    }

    public function categoryAvatarAttachmentIdGenerator(){
        $attachmentId = null;
        if(isset($_FILES['categoryAvatarFile'])){
            $isFileClean = FRMMOBILESUPPORT_BOL_WebServiceGeneral::getInstance()->isFileClean($_FILES['categoryAvatarFile']['tmp_name']);
            if ($isFileClean) {
                $attachmentId = FRMMOBILESUPPORT_BOL_WebServiceStory::getInstance()->manageStoryAttachment(OW::getUser()->getId(), $_FILES['categoryAvatarFile']);
            }else{
                return array('valid' => false, 'message' => 'avatar_file_is_dangerous');
            }
        }elseif( isset($_POST['categoryAvatarStoryId']) ) {
            $stories = STORY_BOL_StoryDao::getInstance()->findStoriesById(array($_POST['categoryAvatarStoryId']));
            $attachmentId = array_column( (array) $stories, 'attachmentId')[0];
        }elseif( isset($_POST['categoryThumbnailId']) ) {
            $attachmentId = (int) $_POST['categoryThumbnailId'];
        }else{
            return array('valid' => false, 'message' => 'highlight_category_avatar_file_not_found');
        }
        return $attachmentId;
    }

    public function addNewHighlightCollection(){
        if(!OW::getUser()->isAuthenticated()){
            return array('valid' => false, 'message' => 'authorization_error');
        }
        $userId = OW::getUser()->getId();
        $categoryLabel = null;
        if(isset($_POST['categoryLabel'])){
            $categoryLabel = strip_tags($_POST['categoryLabel']);
        }else{
            return array('valid' => false, 'message' => 'Undefined_highlight_category_label');
        }
        $attachmentId = $this->categoryAvatarAttachmentIdGenerator();
        if( isset($attachmentId['valid']) && $attachmentId['valid'] == false ){
            return array('valid' => false, 'message' => $attachmentId['message']);
        }

        $list = null;
        if(isset($_POST['list'])){
            $list = (array) json_decode(strip_tags($_POST['list']));
        }else{
            return array('valid' => false, 'message' => 'Undefined_highlight_category_list');
        }
        $highlightCategory = STORY_BOL_Service::getInstance()->createNewHighlightCategoryWithHighlights($userId,$categoryLabel,$attachmentId,$list);
        return array('status' => true, 'category_id' => $highlightCategory->id);
    }

    public function editHighlightCollection(){

        if(!OW::getUser()->isAuthenticated()){
            return array('valid' => false, 'message' => 'authorization_error');
        }

        $categoryId = null;
        if(isset($_POST['categoryId'])){
            $categoryId = $_POST['categoryId'];
        }else{
            return array('valid' => false, 'message' => 'Undefined_highlight_category_id');
        }
        $categoryTitle = null;
        if(isset($_POST['categoryTitle'])){
            $categoryTitle = $_POST['categoryTitle'];
        }

        $list = null;
        if(isset($_POST['list'])){
            $list = json_decode(strip_tags($_POST['list']));
        }else{
            return array('valid' => false, 'message' => 'Undefined_highlight_category_list');
        }

        $attachmentId = $this->categoryAvatarAttachmentIdGenerator();
        if( isset($attachmentId['valid']) && $attachmentId['valid'] == false ){
            return array('valid' => false, 'message' => $attachmentId['message']);
        }

        $highlightCategory = STORY_BOL_Service::getInstance()->editHighlightCategoryWithHighlights($categoryId,$list,$categoryTitle,$attachmentId);
        if ($highlightCategory == False){
            return array('status' => true, 'message' => 'Undefined_highlight_category_id');
        }
        return array('status' => true, 'success');

    }



    /***
     * @return array
     */
    public function addNewHighlightCategoryAvatar() {
        if(!OW::getUser()->isAuthenticated()){
            return array('valid' => false, 'message' => 'authorization_error');
        }

        $user = OW::getUser()->getId();
        if(!isset($user)){
            return array('valid' => false, 'message' => 'authorization_error');
        }

        $categoryId = null;
        if(isset($_POST['categoryId'])){
            $categoryId = $_POST['categoryId'];
        }else{
            return array('valid' => false, 'message' => 'Undefined_highlight_category_id');
        }

        $attachmentId = $this->categoryAvatarAttachmentIdGenerator();
        if( isset($attachmentId['valid']) && $attachmentId['valid'] == false ){
            return array('valid' => false, 'message' => $attachmentId['message']);
        }

        $category = STORY_BOL_StoryHighlightCategoriesDao::getInstance()->assignHighlightCategoryAvatar(OW::getUser()->getId(), $categoryId, $attachmentId );
        if(!isset($category)){
            return array('valid' => false, 'message' => 'Undefined_highlight_category_id');
        }
        return array('status' => true, 'category_id' => $category->id,'category_avatar_id' => $category->categoryAvatar);
    }

    /***
     * @return array
     */
    public function getUserHighlightCategories(){

        if(!OW::getUser()->isAuthenticated()){
            return array('valid' => false, 'message' => 'authorization_error');
        }

        if(isset($_POST['userId'])){
            $userId = $_POST['userId'];
        }else{
            $userId = OW::getUser()->getId();
        }

        $categoryList = STORY_BOL_StoryHighlightCategoriesDao::getInstance()->findCategoryListByUserId( $userId );
        $newCategoryList = FRMMOBILESUPPORT_BOL_WebServiceStory::getInstance()->appendStoryUrl($categoryList);
        return array('status' => true, 'categories' => $newCategoryList);

    }

    /***
     * @return array
     */
    public function removeHighlightCategory(){
        if(!OW::getUser()->isAuthenticated()){
            return array('valid' => false, 'message' => 'authorization_error');
        }

        $categoryId = null;
        if(isset($_POST['categoryId'])){
            $categoryId = $_POST['categoryId'];
        }else{
            return array('valid' => false, 'message' => 'Undefined_highlight_category_id');
        }

        $categoryObject = STORY_BOL_StoryHighlightCategoriesDao::getInstance()->findById($categoryId);
        if(!isset($categoryObject)){
            return array('valid' => false, 'message' => 'Undefined_highlight_category_id');
        }
        if(OW::getUser()->getId() == $categoryObject->userId){
            STORY_BOL_StoryHighlightsDao::getInstance()->removeHighlightsByCategoryId($categoryId);
            STORY_BOL_StoryHighlightCategoriesDao::getInstance()->removeCategoryById( $categoryId );
            return array('status' => true, 'success');
        }else{
            return array('valid' => false, 'message' => 'this_user_cannot_remove_other_user_category');
        }

    }

    /***
     * @return array
     */
    public function addHighlight(){
        if(!OW::getUser()->isAuthenticated()){
            return array('valid' => false, 'message' => 'authorization_error');
        }

        $userId = OW::getUser()->getId();
        if(!isset($userId)){
            return array('valid' => false, 'message' => 'authorization_error');
        }

        $categoryId = null;
        if(isset($_POST['categoryId'])){
            $categoryId = $_POST['categoryId'];
        }else{
            return array('valid' => false, 'message' => 'Undefined_highlight_category_id');
        }

        $storyId = null;
        if(isset($_POST['storyId'])){
            $storyId = $_POST['storyId'];
        }else{
            return array('valid' => false, 'message' => 'Undefined_highlight_story_id');
        }

        STORY_BOL_StoryHighlightsDao::getInstance()->addHighlight($userId, $storyId, $categoryId);
        return array('status' => true, 'success');
    }

    /***
     * @return array
     */
    public function removeHighlight(){
        if(!OW::getUser()->isAuthenticated()){
            return array('valid' => false, 'message' => 'authorization_error');
        }

        $userId = OW::getUser()->getId();
        if(!isset($userId)){
            return array('valid' => false, 'message' => 'authorization_error');
        }

        $list = null;
        if(isset($_POST['list'])){
            $list = json_decode(strip_tags($_POST['list']));
        }else{
            return array('valid' => false, 'message' => 'Undefined_highlight_category_list');
        }
        foreach ($list as $highlightId){
            $highlight = STORY_BOL_StoryHighlightsDao::getInstance()->findHighlightById($highlightId);
            if(!isset($highlight)){
                return array('valid' => false, 'message' => 'Undefined_highlight_id');
            }
            if($highlight->userId == $userId){
                STORY_BOL_StoryHighlightsDao::getInstance()->removeHighlightById($highlightId);
            }
        }
        return array('status' => true, 'success');
    }

    /***
     * @return array
     */
    public function getUserHighlightsList( $userId=null ){
        if(!OW::getUser()->isAuthenticated()){
            return array('valid' => false, 'message' => 'authorization_error');
        }

        if( $userId==null && isset($_POST['userId']) ){
            $userId = $_POST['userId'];
        }

        $highlightsList = STORY_BOL_StoryHighlightsDao::getInstance()->findHighlightListByUserId($userId);
        $newHighlightsList = FRMMOBILESUPPORT_BOL_WebServiceStory::getInstance()->appendStoryUrl($highlightsList);

        $categories = STORY_BOL_StoryHighlightCategoriesDao::getInstance()->findCategoryListByUserId($userId);
        $newCategoryList = FRMMOBILESUPPORT_BOL_WebServiceStory::getInstance()->appendStoryUrl($categories);
        return array('status' => true, 'highlightsList'=>$newHighlightsList,'categories'=>$newCategoryList);
    }

    /***
     * @return array
     */
    public function getHighlightsListByCategoryId(){
        if(!OW::getUser()->isAuthenticated()){
            return array('valid' => false, 'message' => 'authorization_error');
        }

        if(isset($_POST['userId'])){
            $userId = $_POST['userId'];
        }else{
            $userId = OW::getUser()->getId();
        }

        $categoryId = null;
        if(isset($_POST['categoryId'])){
            $categoryId = $_POST['categoryId'];
        }else{
            return array('valid' => false, 'message' => 'Undefined_highlight_category_id');
        }

        $highlightsList = STORY_BOL_StoryHighlightsDao::getInstance()->findUserHighlightListByCategoryId($userId,$categoryId);
        if( $highlightsList == False){
            return array('valid' => false, 'message' => 'Undefined_highlight_category_id');
        }
        $newHighlightsList = FRMMOBILESUPPORT_BOL_WebServiceStory::getInstance()->appendStoryUrl($highlightsList);

        $categoryObject = STORY_BOL_StoryHighlightCategoriesDao::getInstance()->findHighlightCategoryById( $categoryId );
        $newCategoryList = FRMMOBILESUPPORT_BOL_WebServiceStory::getInstance()->appendStoryUrl(array($categoryObject))[0];
        return array('status' => true, 'highlightsList'=>$newHighlightsList, 'category'=>$newCategoryList);
    }


    /***
     * @return array
     */
    public function getHighlight(){
        if(!OW::getUser()->isAuthenticated()){
            return array('valid' => false, 'message' => 'authorization_error');
        }

        $userId = OW::getUser()->getId();
        if(!isset($userId)){
            return array('valid' => false, 'message' => 'authorization_error');
        }

        $categoryId = null;
        if(isset($_POST['categoryId'])){
            $categoryId = $_POST['categoryId'];
        }else{
            return array('valid' => false, 'message' => 'Undefined_highlight_category_id');
        }

        $storyId = null;
        if(isset($_POST['storyId'])){
            $storyId = $_POST['storyId'];
        }else{
            return array('valid' => false, 'message' => 'Undefined_highlight_story_id');
        }

        $highlightsList = STORY_BOL_StoryHighlightsDao::getInstance()->findHighlight($userId,$storyId,$categoryId);
        return array('status' => true, 'highlightsList'=>$highlightsList);
    }


}
