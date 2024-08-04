<?php
/**
 * Forum edit topic class.
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow.ow_plugins.forum.mobile.components
 * @since 1.0
 */
class FORUM_MCMP_ForumEditTopic extends OW_MobileComponent
{
    /**
     * Class constructor
     * 
     * @param array $params
     *      integer topicId
     * @throws Redirect404Exception
     */
    public function __construct(array $params = array())
    {
        parent::__construct();

        $forumService = FORUM_BOL_ForumService::getInstance();
        $topicId = !empty($params['topicId']) ? $params['topicId'] : -1;

        $topicDto = $forumService->findTopicById($topicId);

        if ( !$topicDto )
        {
            throw new Redirect404Exception();
        }

        $forumGroup = $forumService->getGroupInfo($topicDto->groupId);
        $forumSection = $forumService->findSectionById($forumGroup->sectionId);
        $isHidden = $forumSection->isHidden;
        $userId = OW::getUser()->getId();

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
        $canEdit = OW::getUser()->isAuthorized('forum', 'edit') && $userId == $topicDto->userId;

        if ( !$canEdit && !$isModerator )
        {
            throw new AuthorizationException();
        }

        // first topic's post
        $postDto = $forumService->findTopicFirstPost($topicId);
        $attachmentUid = FRMSecurityProvider::generateUniqueId();

        // get a form instance
        $form = new FORUM_CLASS_TopicEditForm(
            'topic_edit_form', 
            $attachmentUid,
            $topicDto,
            $postDto,
            true
        );

        $form->setAction(OW::getRouter()->urlForRoute('edit-topic', array(
            'id' => $topicDto->id
        )));

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
        $this->assign('topicId', $topicId);

        // include js files
        OW::getDocument()->addScript(OW::
                getPluginManager()->getPlugin('forum')->getStaticJsUrl() . 'mobile_attachment.js');
    }
}