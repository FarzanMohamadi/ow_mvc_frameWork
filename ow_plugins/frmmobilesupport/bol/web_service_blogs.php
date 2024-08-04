<?php
/**
 * 
 * All rights reserved.
 */

/**
 *
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmmobilesupport.bol
 * @since 1.0
 */
class FRMMOBILESUPPORT_BOL_WebServiceBlogs
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

    public function getUserblogs()
    {
        if (!FRMSecurityProvider::checkPluginActive('blogs', true)){
            return array('valid' => false, 'message' => 'plugin_not_found');
        }

        $guestAccess = FRMMOBILESUPPORT_BOL_WebServiceGeneral::getInstance()->checkGuestAccess();
        if (!$guestAccess) {
            return array('valid' => false, 'message' => 'guest_cant_view');
        }

        $userId = null;

        if (isset($_GET['userId'])) {
            $userId = $_GET['userId'];
        }else if(OW::getUser()->isAuthorized()){
            $userId = OW::getUser()->getId();
        }
        if($userId == null){
            return array('valid' => false, 'message' => 'authentication_error');
        }
        return $this->getUserBlogsWithId($userId);
    }

    public function getUserBlogsWithId($userId){
        $blogsData=array();
        if (!FRMSecurityProvider::checkPluginActive('blogs', true)){
            return $blogsData;
        }

        $first = 0;
        $count = FRMMOBILESUPPORT_BOL_WebServiceGeneral::getInstance()->getPageSize();
        if (isset($_GET['first'])) {
            $first = (int)$_GET['first'];
        }

        $canView = FRMMOBILESUPPORT_BOL_WebServiceGeneral::getInstance()->checkPrivacyAction($userId, 'blogs_view_blog_posts', 'blogs');
        if (!$canView) {
            return array('valid' => false, 'message' => 'authentication_error');
        }
        $blogs = PostService::getInstance()->findUserPostList($userId, $first, $count);

        foreach ( $blogs as $blog ){
            $blogsData[] = $this->prepareBlogInfo($blog);
        }

        return $blogsData;
    }

    public function prepareBlogInfo($blog, $showFiles = false) {

        if($blog == null) {
            return array();
        }
        $commentable = $this->canUserCommentBlog($blog->id);
        $removable = $this->canUserEditBlog($blog->id);

        $description = FRMMOBILESUPPORT_BOL_WebServiceGeneral::getInstance()->stripString($blog->post);
        $description = FRMMOBILESUPPORT_BOL_WebServiceGeneral::getInstance()->setMentionsOnText($description);
        $tags = BOL_TagService::getInstance()->findEntityTags($blog->id,'blog-post');
        $rateInfo = FRMMOBILESUPPORT_BOL_WebServiceGeneral::getInstance()->getRateInfo($blog->id, 'blog-post');
        $canRate = BOL_RateService::getInstance()->canUserRate($blog->id, 'blog-post');


        $result = array(
            'id' => (int) $blog->id,
            'title' => FRMMOBILESUPPORT_BOL_WebServiceGeneral::getInstance()->stripString($blog->title),
            'userId' => (int) $blog->authorId,
            'user' => FRMMOBILESUPPORT_BOL_WebServiceUser::getInstance()->getUserInformationById($blog->authorId),
            'commentable' => $commentable,
            'removable' => $removable,
            'entityId' => (int) $blog->id,
            'entityType' => 'blogs',
            'rateInfo' => $rateInfo,
            "ratable" => $canRate['valid'],
            'editable' => $removable,
            'description' => $description,
            'timestamp' => $blog->timestamp,
            'flagAble' => true,
            'tags' => json_encode($tags),
        );

        if ($showFiles && isset($blog->bundleId)) {
            $files = array();
            $attachmentObjects = BOL_AttachmentService::getInstance()->getFilesByBundleName('blog', $blog->bundleId);
            foreach ($attachmentObjects as $attachmentObject) {
                $files[] = array(
                    "name" => $attachmentObject['dto']->origFileName,
                    "id" => $attachmentObject['dto']->id,
                    "size" => $attachmentObject['dto']->size,
                    "bundle" => $attachmentObject['dto']->bundle,
                    "url" => $attachmentObject['url'],
                );
            }
            $result['files'] = $files;

        }
        return $result;
    }

    public function getBlog(){
        $blogId = null;
        if(isset($_GET['id'])){
            $blogId  = $_GET['id'];
        }

        if(isset($_POST['id'])){
            $blogId  = $_POST['id'];
        }

        if($blogId == null){
            return array('valid' => false, 'message' => 'input_error');
        }

        if(!FRMSecurityProvider::checkPluginActive('blogs', true)){
            return array('valid' => false, 'message' => 'plugin_not_found');
        }
        $first=0;
        if(isset($_GET['first'])){
            $first=$_GET['first'];
        }

        $blog=PostService::getInstance()->findById($blogId);
        if ($blog == null){
            return array('valid' => false, 'message' => 'authorization_error', 'id' => $blogId);
        }

        $canView = FRMMOBILESUPPORT_BOL_WebServiceGeneral::getInstance()->checkPrivacyAction($blog->authorId, 'blogs_view_blog_posts', 'blogs');
        if (!$canView) {
            return array('valid' => false, 'message' => 'authentication_error', 'id' => $blogId);
        }

        $page=FRMMOBILESUPPORT_BOL_WebServiceGeneral::getInstance()->getPageNumber($first);
        $comments = FRMMOBILESUPPORT_BOL_WebServiceComment::getInstance()->getCommentsInformation('blog-post', $blogId, $page);

        $blogData = $this->prepareBlogInfo($blog, true);
        $blogData['comments'] = $comments;

        return $blogData;
    }

    public function getLatestBlogs(){

        if(!FRMSecurityProvider::checkPluginActive('blogs', true)){
            return array('valid' => false, 'message' => 'plugin_not_found');
        }

        $first = 0;
        $count = FRMMOBILESUPPORT_BOL_WebServiceGeneral::getInstance()->getPageSize();
        if (isset($_GET['first'])) {
            $first = (int)$_GET['first'];
        }

        $data = array();
        $blogs = PostService::getInstance()->findListByUser($first, $count);

        foreach ( $blogs as $blog ){
            $data[] = $this->prepareBlogInfo($blog);
        }

        return $data;
    }

    public function addBlog()
    {
        # check if blog plugin is available and user is authorized
        $precondition = $this->isBlogCreateUpdateConditionsSatisfied();
        if(!$precondition['valid'])
        {
            return $precondition['message'];
        }

        # strip post title and body strings
        $title = FRMMOBILESUPPORT_BOL_WebServiceGeneral::getInstance()->stripString($_POST['title'], true, true);
        $post = FRMMOBILESUPPORT_BOL_WebServiceGeneral::getInstance()->stripString($_POST['description'], true, false);

        # check files for viruses and get bundleId
        $bundle = $this->getBundleId();

        # notification
        $sendNotification = $_POST['enSentNotification']??false;

        # tags
        $tags = $_POST['tf']??array();

        # add blog
        $newBlog=PostService::getInstance()->createBlogPost($title, $post, $bundle['bundleId'],$sendNotification,$tags);

        return array('valid' => true, 'blog' => $this->prepareBlogInfo($newBlog, true), 'virus_files' => $bundle['virusFiles']);
    }

    public function editBlog(){
        # check if blog plugin is available and user is authorized
        $preconditionResult = $this->isBlogCreateUpdateConditionsSatisfied();
        if(!$preconditionResult['valid'])
        {
            return $preconditionResult;
        }

        # is user legitimate to edit following post
        if(!isset($_GET['blogId'])){
            return array('valid' => false, 'message' => 'input_error');
        }

        $blogId = $_GET['blogId'];
        if ( !$this->canUserEditBlog($blogId) ) {
            return array('valid' => false, 'message' => 'blog_was_not_edited');
        }

        # strip post title and body strings
        $title = FRMMOBILESUPPORT_BOL_WebServiceGeneral::getInstance()->stripString($_POST['title'], true, true);
        $description = FRMMOBILESUPPORT_BOL_WebServiceGeneral::getInstance()->stripString($_POST['description'], true, false);

        $post = PostService::getInstance()->findById($blogId);

        # check files for viruses and get bundleId
        $bundle = $this->getBundleId();

        # notification
        $sendNotification = $_POST['enSentNotification']??false;

        # tags
        $tags = $_POST['tf']??array();

        # update blog
        $updatedBlog = PostService::getInstance()->updateBlogPost($post, $title, $description, $bundle['bundleId'],$sendNotification,$tags);

        return array('valid' => true, 'blog' => $this->prepareBlogInfo($updatedBlog, true), 'virus_files' => $bundle['virusFiles']);
    }

    public function getBundleId()
    {
        $bundle = FRMSecurityProvider::generateUniqueId();

        $fileIndex = 0;
        $virusDetectedFiles = array();
        if (isset($_FILES)) {
            if (isset($_FILES['file'])) {
                $isFileClean = FRMMOBILESUPPORT_BOL_WebServiceGeneral::getInstance()->isFileClean($_FILES['file']['tmp_name']);
                if ($isFileClean) {
                    try{
                        BOL_AttachmentService::getInstance()->processUploadedFile('blog', $_FILES['file'], $bundle);
                    }
                    catch ( Exception $e ){
                    }
                } else {
                    $virusDetectedFiles[] = $_FILES['file']['name'];
                }
            }
            while (isset($_FILES['file' . $fileIndex])) {
                $isFileClean = FRMMOBILESUPPORT_BOL_WebServiceGeneral::getInstance()->isFileClean($_FILES['file' . $fileIndex]['tmp_name']);
                if ($isFileClean) {
                    try{
                        BOL_AttachmentService::getInstance()->processUploadedFile('blog', $_FILES['file' . $fileIndex], $bundle);
                    }
                    catch ( Exception $e ){
                    }
                } else {
                    $virusDetectedFiles[] = $_FILES['file' . $fileIndex]['name'];
                }
                $fileIndex++;
            }
        }

        return array('bundleId' => $bundle,'virusFiles' => $virusDetectedFiles);
    }

    public function isBlogCreateUpdateConditionsSatisfied()
    {
        if(!FRMSecurityProvider::checkPluginActive('blogs', true)){
            return array('valid' => false, 'message' => 'plugin_not_found');
        }
        if (!OW::getUser()->isAuthorized('blogs', 'add')){
            return array('valid' => false, 'message' => 'authentication_error');
        }

        if (!isset($_POST['title']) || !isset($_POST['description'])) {
            return array('valid' => false, 'message' => 'input_error');
        }

        return array('valid'=>true);
    }

    public function canUserCommentBlog($blogId){
        $blog = PostService::getInstance()->findById($blogId);
        if($blog == null){
            return false;
        }
        if(!OW::getUser()->isAuthorized('blogs', 'add_comment') ){
            return false;
        }
        $checkPrivacy = FRMMOBILESUPPORT_BOL_WebServiceGeneral::getInstance()->checkPrivacyAction($blog->authorId, 'blogs_comment_blog_posts', 'blogs');
        if (!$checkPrivacy){
            return false;
        }
        return true;
    }

    public function canUserEditBlog($blogId){

        $blog = PostService::getInstance()->findById($blogId);
        if($blog == null){
            return false;
        }
        if(!(OW::getUser()->isAdmin() || ( OW::getUser()->getId() == $blog->getAuthorId() || OW::getUser()->isAuthorized('blogs'))) ){
            return false;
        }
        return true;
    }

    public function canUserCreateBlog(){
        $pluginActive = FRMSecurityProvider::checkPluginActive('blogs', true);

        if(!$pluginActive){
            return false;
        }

        if ( !OW::getUser()->isAuthenticated() || !OW::getUser()->isAuthorized('blogs', 'add') )
        {
            return false;
        }

        return true;
    }

    public function removeBlog()
    {
        if(!isset($_GET['blogId'])){
            return array('valid' => false, 'message' => 'blog_id_not_set');
        }
        $blogId=$_GET['blogId'];
        if(!FRMSecurityProvider::checkPluginActive('blogs', true)){
            return array('valid' => false, 'message' => 'plugin_not_found');
        }
        if ( $this->canUserEditBlog($blogId) ) {
            PostService::getInstance()->deletePost($blogId);
            return array('valid' => true, 'id' => (int) $blogId);
        }else{
            return array('valid' => false, 'message' => 'blog_was_not_deleted');
        }
    }


}