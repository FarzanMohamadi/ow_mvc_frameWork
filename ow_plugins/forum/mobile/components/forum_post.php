<?php
/**
 * Forum post class.
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow.ow_plugins.forum.mobile.components
 * @since 1.0
 */
class FORUM_MCMP_ForumPost extends OW_MobileComponent
{
    /**
     * Class constructor
     * 
     * @param array $params
     *      integer page
     *      array topicInfo
     */
    public function __construct(array $params = array())
    {
        parent::__construct();

        $forumService   = FORUM_BOL_ForumService::getInstance();
        $page           = !empty($params['page']) ? $params['page'] : 1;
        $topicInfo      = !empty($params['topicInfo']) ? $params['topicInfo'] : array();

        $canEdit = !empty($params['canEdit'])     
            ? (bool) $params['canEdit'] 
            : false;

        $canPost = !empty($params['canPost'])     
            ? (bool) $params['canPost'] 
            : false;

        $reverse_sort = false;
        if (isset($_GET['reverse_sort']))
            $reverse_sort = ($_GET['reverse_sort'] == 'true') ? true : false;

        $postCount = $forumService->findTopicPostCount($topicInfo['id']);
        $fixedPosts = $forumService->getTopicPostList($topicInfo['id'], $page, $reverse_sort, true);
        $postList = $postCount
            ? $forumService->getTopicPostList($topicInfo['id'], $page,$reverse_sort)
            : array();
        $postList = array_merge($fixedPosts, $postList);

        OW::getEventManager()->trigger(new OW_Event('forum.topic_post_list', array('list' => $postList)));

        if ( !$postList )
        {
            throw new Redirect404Exception();
        }

        // process list of posts
        $userIds = array();
        $postIds = array();
        $config = OW::getConfig();
        $showConclusionPostConfig = $config->configExists('forum', 'showClosedTopicLastPostInTopSection') &&
            $config->getValue('forum', 'showClosedTopicLastPostInTopSection');
        $topicDto = $forumService->findTopicById($topicInfo['id']);
        $canCurrentUserConcludeTopic = $forumService->canCurrentUserConcludeTopic($topicInfo['id']);

        $commentsEnabled = $config->getValue('forum', 'enableCommentsForReplies', false);
        $addComments = !$topicDto->locked && ($canEdit || $canPost);
        $this->assign('commentsEnabled', $commentsEnabled);

        $iteration = 0;
        foreach ( $postList as &$post)
        {
            // only show closingPost at the top of the page
            if (OW::getConfig()->getValue("forum", "showClosedTopicLastPostInTopSection") && $iteration >= 2 && $post['isClosingPost']) {
                unset($postList[$iteration]);
                continue;
            }
            $post['text'] = UTIL_HtmlTag::autoLink($post['text']);
            $post['permalink'] = $forumService->getPostUrl($post['topicId'], $post['id'], true, $page);
            $stringRenderer = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_BEFORE_RENDER_STRING, array('string' => $post['text'])));
            if(isset($stringRenderer->getData()['string'])){
                $post['text'] = ($stringRenderer->getData()['string']);
            }

            // get list of users
            if ( !in_array($post['userId'], $userIds) )
            {
                $userIds[$post['userId']] = $post['userId'];
            }

            if ( count($post['edited']) && !in_array($post['edited']['userId'], $userIds) )
            {
                $userIds[$post['edited']['userId']] = $post['edited']['userId'];
            }
            if ($showConclusionPostConfig) {
                $addSetAsConclusionToolbar = false;
                if ($canCurrentUserConcludeTopic)
                    $addSetAsConclusionToolbar = true;
                if ($addSetAsConclusionToolbar) {
                    $setAsConclusionPostLabel = OW::getLanguage()->text('forum', 'set_as_conclusion_post');
                    $setConclusionButtonImage = OW::getPluginManager()->getPlugin('base')->getStaticCssUrl() . 'images/ic_tick_white.svg';
                    $topicConclusionPostId = $topicDto->conclusionPostId;
                    if ($topicConclusionPostId != null && $topicConclusionPostId == $post['id']) {
                        $setConclusionButtonImage = OW::getPluginManager()->getPlugin('base')->getStaticCssUrl() . 'images/ic_delete_white.svg';
                        $setAsConclusionPostLabel = OW::getLanguage()->text('forum', 'unset_as_conclusion_post');
                    }
                    if ($iteration != 0) {
                        $post['set_as_conclusion_post_url'] = OW::getRouter()->urlForRoute('set-as-topic-conclusion-post', array('topicId' => $topicDto->id, 'postId' => $post['id']));
                        $post['set_as_conclusion_post_label'] = $setAsConclusionPostLabel;
                        $post['set_as_conclusion_post_button_background_image'] = $setConclusionButtonImage;
                    }
                }
            }

            if ($commentsEnabled && $iteration != 0) {
                // comment components
                $cmpParams = new BASE_CommentsParams('forum', 'forum-post');
                $cmpParams->setEntityId($post['id'])
                    ->setOwnerId($post['userId'])
                    ->setDisplayType(BASE_CommentsParams::DISPLAY_TYPE_WITH_LOAD_LIST_MINI)
                    ->setWrapInBox(false)
                    ->setShowEmptyList(false)
                    ->setAddComment($addComments);
                $this->addComponent('comments' . $post['id'], new BASE_MCMP_Comments($cmpParams));
            }

            $iteration++;
            array_push($postIds, $post['id']);
        }

        $enableAttachments = OW::getConfig()->getValue('forum', 'enable_attachments');

        // paginate
        $perPage = $forumService->getPostPerPageConfig();
        $pageCount = ($postCount) ? ceil($postCount / $perPage) : 1;
        $paging = new BASE_CMP_PagingMobile($page, $pageCount, $perPage);
        
        // assign view variables
        $this->assign('topicInfo', $topicInfo);
        $eventPostListData = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_BEFORE_FORUM_POST_RENDER, array('postList' => $postList)));
        if(isset($eventPostListData->getData()['postList'])){
            $postList = $eventPostListData->getData()['postList'];
        }

        $showClosingPostInInfoBoxConfig = false;
        $config = OW::getConfig();
        if ($config->configExists('forum', 'showClosedTopicLastPostInTopSection') &&
            $config->getValue('forum', 'showClosedTopicLastPostInTopSection')){
            $showClosingPostInInfoBoxConfig = true;
        }

        $topicHasConclusionPost = false;
        if ($topicDto->conclusionPostId != null)
            $topicHasConclusionPost = true;
        $this->assign('reversePostsShow', isset($_GET['reverse_sort']) ? ($_GET['reverse_sort'] == 'true' ? true : false) : false);
        $this->assign('topicHasConclusionPost', $topicHasConclusionPost);
        $this->assign('showClosingPostInInfoBoxConfig', $showClosingPostInInfoBoxConfig);
        $this->assign('postList', $postList);
        $this->assign('onlineUsers', BOL_UserService::getInstance()->findOnlineStatusForUserList($userIds));
        $this->assign('avatars', BOL_AvatarService::getInstance()->getDataForUserAvatars($userIds));
        $this->assign('enableAttachments', $enableAttachments);        
        $this->assign('paging', $paging->render());
        $this->assign('firstTopic', $forumService->findTopicFirstPost($topicInfo['id']));
        $this->assign('canEdit', $canEdit);
        $this->assign('canPost', $canPost);
        $this->assign('postEnityType', FORUM_CLASS_ContentProvider::POST_ENTITY_TYPE);
        $this->assign('topicEnityType', FORUM_CLASS_ContentProvider::ENTITY_TYPE);
        $this->assign('postsCount', $forumService->findPostCountListByUserIds($userIds));

        if (FRMSecurityProvider::checkPluginActive('groups', true))
            $this->assign("frmmenu_active", true);

        if ( $enableAttachments )
        {
            $this->assign('attachments',
                    FORUM_BOL_PostAttachmentService::getInstance()->findAttachmentsByPostIdList($postIds));

            /* ======== This block aims for attachment icons in forum posts using FRMFORUMPLUS plugin ======= */
            $attachments = FORUM_BOL_PostAttachmentService::getInstance()->findAttachmentsByPostIdList($postIds);
            $attachmentsEvent=OW::getEventManager()->trigger(new OW_Event('frm.on.before.attachments.icon.render', array('attachments' => $attachments)));
            $iconEnable=$attachmentsEvent->getData();
            $attachmentIcons=false;
            if(isset($iconEnable)){
                $attachments=$attachmentsEvent->getData();
                $this->assign('attachments', $attachments);
                $attachmentIcons=true;
                $this->assign('attachmentIcons', $attachmentIcons);
            }
            /* =============================================== End =========================================== */
        }
    }
}