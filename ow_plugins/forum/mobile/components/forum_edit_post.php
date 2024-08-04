<?php
/**
 * Forum edit post class.
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow.ow_plugins.forum.mobile.components
 * @since 1.0
 */
class FORUM_MCMP_ForumEditPost extends OW_MobileComponent
{
    /**
     * Class constructor
     * 
     * @param array $params
     *      integer topicId
     *      integer postId
     */
    public function __construct(array $params = array())
    {
        parent::__construct();
        $forumService = FORUM_BOL_ForumService::getInstance();

        $topicId = !empty($params['topicId']) ? $params['topicId'] : -1;
        $postId = !empty($params['postId']) ? $params['postId'] : -1;

        $topicDto = $forumService->findTopicById($topicId);
        $postDto = $forumService->findPostById($postId);
        
        if ( !$topicDto || !$postDto || $topicDto->id != $postDto->topicId)
        {
            throw new Redirect404Exception();
        }

        $forumGroup = $forumService->getGroupInfo($topicDto->groupId);
        $forumSection = $forumService->findSectionById($forumGroup->sectionId);
        $isHidden = $forumSection->isHidden;
        //commented codes prevented users to edit forum's topic of a group in mobile version
        // check access permissions
/*        if ( $isHidden )
        {
            throw new Redirect404Exception();
        }*/

        $isModerator = OW::getUser()->isAuthorized('forum');
        if($isHidden) {
            if(FRMSecurityProvider::checkPluginActive('groups', true)) {
                $groupDto = GROUPS_BOL_Service::getInstance()->findGroupById($forumGroup->entityId);
                if (GROUPS_BOL_Service::getInstance()->isCurrentUserCanEdit($groupDto)) {
                    $isModerator = true;
                }
            }
        }
        $userId = OW::getUser()->getId();
        $firstPostDto = $forumService->findTopicFirstPost($topicId);

        if ( !$isModerator && ($userId != 
                $postDto->userId || $topicDto->locked) && $postDto->id != $firstPostDto->id )
        {
            throw new Redirect404Exception();
        }

        $attachmentUid = FRMSecurityProvider::generateUniqueId();

        // get a form instance
        $form = new FORUM_CLASS_PostForm(
            'post_form', 
            $attachmentUid, 
            $topicId, 
            true
        );

        $form->setAction(OW::getRouter()->urlForRoute('edit-post', array(
            'id' => $postId
        )));
        OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_BEFORE_FORUM_POST_FORM_CREATE, array('form' => $form, 'postId' => $postId)));
        $eventPostListData = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_BEFORE_FORUM_POST_RENDER, array('postId' => $postDto->id)));
        if(isset($eventPostListData->getData()['extendedText'])){
            $this->assign('extendedText', $eventPostListData->getData()['extendedText']);
        }
        $form->getElement('text')->setValue($postDto->text);
        $this->addForm($form);

        // attachments
        $enableAttachments = OW::getConfig()->getValue('forum', 'enable_attachments');

        if ( $enableAttachments )
        {
            $attachmentService = FORUM_BOL_PostAttachmentService::getInstance();
            $attachmentList = $attachmentService->findAttachmentsByPostIdList(array($postDto->id));
            $attachments = array();

            // process attachments
            if ( $attachmentList ) 
            {
                $attachmentList = array_shift($attachmentList);

                $index = 0;
                foreach($attachmentList as $attachment)
                {
                    $attachments[$index] = array(
                        'id' => $index, 
                        'name' => $attachment['fileName'], 
                        'size' => $attachment['fileSize'],
                        'dbId' => $attachment['id']
                    );

                    $index++;
                }

                $attachments = json_encode($attachments);
            }

            $this->assign('attachments', $attachments); 
            $attachmentCmp = new BASE_CLASS_FileAttachment('forum', $attachmentUid);
            $this->addComponent('attachmentsCmp', $attachmentCmp);
        }

        // assign view variables
        $this->assign('enableAttachments', $enableAttachments);
        $this->assign('attachmentUid', $attachmentUid);

        // include js files
        OW::getDocument()->addScript(OW::
                getPluginManager()->getPlugin('forum')->getStaticJsUrl() . 'mobile_attachment.js');
    }
}