<?php
/**
 * Forum add topic controller
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow.ow_plugins.forum.mobile.controllers
 * @since 1.0
 */
class FORUM_MCTRL_AddTopic extends FORUM_MCTRL_AbstractForum
{
    /**
     * Controller's default action
     *
     * @param array $params
     * @throws AuthorizationException
     */
    public function index( array $params = null )
    {
        if ( !OW::getUser()->isAuthenticated() )
        {
            throw new AuthenticateException();
        }

        // check permissions
        if ( !OW::getUser()->isAdmin() && !OW::getUser()->isAuthorized('forum') && !OW::getUser()->isAuthorized('forum', 'edit') )
        {
            $status = BOL_AuthorizationService::getInstance()->getActionStatus('forum', 'edit');
            throw new AuthorizationException($status['msg']);
        }

        $backGroupId = isset($params['groupId']) && (int) $params['groupId'] 
            ? (int) $params['groupId'] 
            : -1;

        $userId = OW::getUser()->getId();

        $form = new FORUM_CLASS_TopicAddForm(
            "topic_form",
            FRMSecurityProvider::generateUniqueId(),
            $this->forumService->getGroupSelectList(0, false, $userId), 
            $backGroupId, 
            true
        );

        // validate the form
        if ( OW::getRequest()->isPost() && $form->isValid($_POST) )
        {
            $data = $form->getValues();

            $forumGroupId = !empty($data['group']) ? $data['group'] : -1;
            $forumGroup   = $this->forumService->getGroupInfo($forumGroupId);
            $forumSection = $forumGroup 
                ? $this->forumService->findSectionById($forumGroup->sectionId)
                : null;

            if (!empty($forumGroup )) {
                $section = FORUM_BOL_SectionDao::getInstance()->findById($forumGroup->sectionId);
            }
            if (!empty($forumGroup )&& isset($forumGroup->entityId) && isset($section) && $section->entity=='groups' && FRMSecurityProvider::checkPluginActive('groups', true)) {
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
                $groupDto = GROUPS_BOL_Service::getInstance()->findGroupById($forumGroup->entityId);
                $isModerator = GROUPS_BOL_Service::getInstance()->isCurrentUserCanEdit($groupDto);
                if (!$isModerator) {
                    if (!$isAuthorizedCreate) {
                        throw new Redirect404Exception();
                    } else if ($isAuthorizedCreate && $isChannel) {
                        throw new Redirect404Exception();
                    }
                }
            }
            // you cannot add new topics in hidden sections
            if ( !$forumGroup || $forumSection->isHidden )
            {
                //throw new Redirect404Exception();
            }

            $isHidden = $forumSection->isHidden ? true : false;            
            $topicDto = $this->forumService->addTopic($forumGroup, $isHidden, $userId, $data);

            OW::getEventManager()->trigger(new OW_Event('on.forum.group.topic.add', array(
                "groupId" => $forumGroup->entityId,
                "topicId" => $topicDto->id,
                "topicTitle" => $topicDto->title
            )));

            $this->redirect(OW::getRouter()->
                        urlForRoute('topic-default', array('topicId' => $topicDto->id)));
        }

        OW::getFeedback()->
                error(OW::getLanguage()->text('base', 'form_validate_common_error_message'));

        // an error occured
        $this->redirect(OW::getRouter()->
                        urlForRoute('group-default', array('groupId' => $backGroupId)));
    }
}
