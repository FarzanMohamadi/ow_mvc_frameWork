<?php
/**
 * Forum edit topic controller
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow.ow_plugins.forum.mobile.controllers
 * @since 1.0
 */
class FORUM_MCTRL_EditTopic extends FORUM_MCTRL_AbstractForum
{
    /**
     * Controller's default action
     *
     * @param array $params
     * @throws AuthorizationException|Redirect404Exception|AuthenticateException
     */
    public function index( array $params = null )
    {
        if ( !isset($params['id']) || !($topicId = (int) $params['id']) )
        {
            throw new Redirect404Exception();
        }

        if ( !OW::getUser()->isAuthenticated() )
        {
            throw new AuthenticateException();
        }

        // check permissions
        if ( !OW::getUser()->isAuthorized('forum') && !OW::getUser()->isAdmin() && !OW::getUser()->isAuthorized('forum', 'edit') )
        {
            $status = BOL_AuthorizationService::getInstance()->getActionStatus('forum', 'edit');
            throw new AuthorizationException($status['msg']);
        }

        $forumService = FORUM_BOL_ForumService::getInstance();
        $topicDto = $forumService->findTopicById($topicId);
        $postDto = $forumService->findTopicFirstPost($topicId);

        if ( !$topicDto || !$postDto )
        {
            throw new Redirect404Exception();
        }

        $forumGroup = $forumService->getGroupInfo($topicDto->groupId);
        $forumSection = $forumService->findSectionById($forumGroup->sectionId);
        //commented codes prevented users to edit forum's topic of a group in mobile version
/*        if ( $forumSection->isHidden )
        {
            throw new Redirect404Exception();
        }*/

        $userId = OW::getUser()->getId();
        $isModerator = OW::getUser()->isAuthorized('forum');
        $isHidden = $forumSection->isHidden;
        if($isHidden) {
            if(FRMSecurityProvider::checkPluginActive('groups', true) && isset($forumGroup->entityId)) {
                $isChannel = false;
                $channelEvent = OW::getEventManager()->trigger(new OW_Event('frmgroupsplus.on.channel.add.widget',
                    array('groupId' => $forumGroup->entityId)));
                $isChannelParticipant = $channelEvent->getData()['channelParticipant'];
                if (isset($isChannelParticipant) && $isChannelParticipant) {
                    $isChannel = true;
                }

                $isAuthorizedCreate = true;
                $groupSettingEvent = OW::getEventManager()->trigger(new OW_Event('can.create.topic',
                    array('groupId' => $forumGroup->entityId)));
                if (isset($groupSettingEvent->getData()['accessCreateTopic'])) {
                    $isAuthorizedCreate = $groupSettingEvent->getData()['accessCreateTopic'];
                }
                if (!$isModerator) {
                    if (!$isAuthorizedCreate) {
                        throw new Redirect404Exception();
                    } else if ($isAuthorizedCreate && $isChannel) {
                        throw new Redirect404Exception();
                    }
                }
            }
        }
        $canEdit = OW::getUser()->isAuthorized('forum', 'edit') && $userId == $topicDto->userId;

        if ( !$canEdit && !$isModerator )
        {
            throw new AuthorizationException();
        }

        $attachmentUid = FRMSecurityProvider::generateUniqueId();

        // get a form instance
        $form = new FORUM_CLASS_TopicEditForm(
            'topic_edit_form', 
            $attachmentUid,
            $topicDto,
            $postDto,
            true
        );

        // validate the form
        if ( OW::getRequest()->isPost() && $form->isValid($_POST) )
        {
            $data = $form->getValues();

            // update the topic
            $this->forumService->
                    editTopic($userId, $data, $topicDto, $postDto, $forumSection, $forumGroup);

            $this->redirect(OW::getRouter()->
                    urlForRoute('topic-default', array('topicId' => $topicId)));
        }

        OW::getFeedback()->
                error(OW::getLanguage()->text('base', 'form_validate_common_error_message'));

        // an error occured
        $this->redirect(OW::getRouter()->
                        urlForRoute('topic-default', array('topicId' => $topicId)));
    }
}
