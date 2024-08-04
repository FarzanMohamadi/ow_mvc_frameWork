<?php
/**
 * Forum add topic controller
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow.ow_plugins.forum.controllers
 * @since 1.0
 */
class FORUM_CTRL_AddTopic extends OW_ActionController
{

    /**
     * Controller's default action
     *
     * @param array $params
     * @throws AuthorizationException
     * @throws AuthenticateException
     */
    public function index( array $params = null )
    {
        $groupId = isset($params['groupId']) && (int) $params['groupId'] ? (int) $params['groupId'] : 0;

        $forumService = FORUM_BOL_ForumService::getInstance();

        $forumGroup = $forumService->getGroupInfo($groupId);
        if ( $forumGroup )
        {
            if(isset($forumGroup->entityId)) {
                $section=FORUM_BOL_SectionDao::getInstance()->findById($forumGroup->sectionId);
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
            }
            $forumSection = $forumService->findSectionById($forumGroup->sectionId);
            $isHidden = $forumSection->isHidden;
        }
        else
        {
            $isHidden = false;
        }

        if ( !OW::getUser()->isAuthenticated() )
        {
            throw new AuthenticateException();
        }

        $userId = OW::getUser()->getId();

        $this->assign('authMsg', null);

        if ( $isHidden && isset($forumSection) )
        {
            $eventParams = array('entity' => $forumSection->entity, 'entityId' => $forumGroup->entityId, 'action' => 'add_topic');
            $event = new OW_Event('forum.check_permissions', $eventParams);
            OW::getEventManager()->trigger($event);

            if ( !$event->getData() )
            {
                throw new AuthorizationException();
            }
            $canAdd = $event->getData();
            if (!$canAdd)
            {
                $status = BOL_AuthorizationService::getInstance()->getActionStatus($forumSection->entity, 'add_topic');
                throw new AuthorizationException($status['msg']);
            }

            $event = new OW_Event('forum.find_forum_caption', array('entity' => $forumSection->entity, 'entityId' => $forumGroup->entityId));
            OW::getEventManager()->trigger($event);

            $eventData = $event->getData();

            /** @var OW_Component $componentForumCaption */
            $componentForumCaption = $eventData['component'];

            if ( !empty($componentForumCaption) )
            {
                $this->assign('componentForumCaption', $componentForumCaption->render());
            }
            else
            {
                $componentForumCaption = false;
                $this->assign('componentForumCaption', $componentForumCaption);
            }

            $bcItems = array(
                array(
                    'href' => OW::getRouter()->urlForRoute('group-default', array('groupId' => $forumGroup->getId())),
                    'label' => OW::getLanguage()->text($forumSection->entity, 'view_all_topics')
                )
            );

            $breadCrumbCmp = new BASE_CMP_Breadcrumb($bcItems);
            $this->addComponent('breadcrumb', $breadCrumbCmp);

            OW::getNavigation()->deactivateMenuItems(OW_Navigation::MAIN);
            OW::getNavigation()->activateMenuItem(OW_Navigation::MAIN, $forumSection->entity, $eventData['key']);

            $groupSelect = array(array('label' => $forumGroup->name, 'value' => $forumGroup->getId(), 'disabled' => false));

            OW::getDocument()->setHeading(OW::getLanguage()->text($forumSection->entity, 'create_new_topic', array('group' => $forumGroup->name)));
        }
        else
        {
            $canEdit = OW::getUser()->isAuthorized('forum', 'edit');

            if ( !$userId )
            {
                throw new AuthorizationException();
            }
            else if ( !$canEdit )
            {
                $status = BOL_AuthorizationService::getInstance()->getActionStatus('forum', 'edit');
                throw new AuthorizationException($status['msg']);
            }

            if ( !OW::getRequest()->isAjax() )
            {
                OW::getNavigation()->activateMenuItem(OW_Navigation::MAIN, 'forum', 'forum');
            }

            $groupSelect = $forumService->getGroupSelectList(0, false, $userId);

            OW::getDocument()->setHeading(OW::getLanguage()->text('forum', 'create_new_topic'));
        }

        OW::getDocument()->setDescription(OW::getLanguage()->text('forum', 'meta_description_add_topic'));
        OW::getDocument()->setTitle(OW::getLanguage()->text('forum', 'meta_title_add_topic'));
        OW::getDocument()->setHeadingIconClass('ow_ic_write');

        $this->assign('isHidden', $isHidden);

        $uid = FRMSecurityProvider::generateUniqueId();
        $form = $this->generateForm($groupSelect, $groupId, $isHidden, $uid);

        OW::getDocument()->addStyleDeclaration('
			.disabled_option {
				color: #9F9F9F;
    		}
		');

        $enableAttachments = OW::getConfig()->getValue('forum', 'enable_attachments');
        if ( $enableAttachments )
        {
            $attachmentCmp = new BASE_CLASS_FileAttachment('forum', $uid);
            $this->addComponent('attachments', $attachmentCmp);
        }
        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('forum')->getStaticJsUrl() .'forum.js');
        OW::getDocument()->addStyleSheet(OW_PluginManager::getInstance()->getPlugin("forum")->getStaticCssUrl() . 'forum.css');

        $this->assign('enableAttachments', $enableAttachments);

        if ( OW::getRequest()->isPost() && $form->isValid($_POST) )
        {
            $data = $form->getValues();

            if ( $data['group'] )
            {
                // create a new topic
                if ( !isset($forumSection) )
                {
                    $topicDto = $forumService->addTopic($forumGroup, $isHidden, $userId, $data);
                }
                else 
                {
                    $topicDto = $forumService->addTopic($forumGroup, $isHidden, $userId, $data, $forumSection);
                }

                OW::getEventManager()->trigger(new OW_Event('on.forum.group.topic.add', array(
                    "groupId" => $forumGroup->entityId,
                    "topicId" => $topicDto->id,
                    "topicTitle" => $topicDto->title
                )));
                $this->redirect(OW::getRouter()->
                        urlForRoute('topic-default', array('topicId' => $topicDto->id)));
            }
            else
            {
                $form->getElement('group')->addError(OW::getLanguage()->text('forum', 'select_group_error'));
            }
        }
    }

    /**
     * Generates Add Topic Form.
     *
     * @param array $groupSelect
     * @param int $groupId
     * @param $isHidden
     * @param $uid
     * @return Form
     */
    private function generateForm( $groupSelect, $groupId, $isHidden, $uid )
    {
        $form = new FORUM_CLASS_TopicAddForm(
            'add-topic-form', 
            $uid, 
            $groupSelect, 
            $groupId,
            false,
            $isHidden
        );

        $this->addForm($form);
        return $form;
    }
}
