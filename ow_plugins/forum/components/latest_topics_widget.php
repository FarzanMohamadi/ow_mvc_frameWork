<?php
/**
 * Latest for Forum Group Topics Widget
 *
 * @package ow_plugins.forum.components
 * @since 1.0
 */
class FORUM_CMP_LatestTopicsWidget extends BASE_CLASS_Widget
{
    private $entity;
    private $entityId;

    /**
     * @param BASE_CLASS_WidgetParameter $paramObj
     * @return FORUM_CMP_LatestTopicsWidget
     */
    public function __construct( BASE_CLASS_WidgetParameter $paramObj )
    {
        parent::__construct();

        $confTopicCount = (int) $paramObj->customParamList['topicCount'];

        $this->entityId = (int) $paramObj->additionalParamList['entityId'];
        $this->entity = $paramObj->additionalParamList['entity'];
        $groupDto = null;
        if (isset($paramObj->additionalParamList['group'])) {
            $groupDto = $paramObj->additionalParamList['group'];
        }

        $forumService = FORUM_BOL_ForumService::getInstance();
        $forumGroup = $forumService->findGroupByEntityId($this->entity, $this->entityId);
        if ( empty($forumGroup) )
        {
            $this->setVisible(false);
            return;
        }

        $topicList = $forumService->getGroupTopicList($forumGroup->getId(), 1, $confTopicCount);
        // get usernames list
        $userIds = array();
        $topicIds = array();

        foreach ( $topicList as $topic )
        {
            array_push($topicIds, $topic['id']);

            if ( isset($topic['lastPost']) && !in_array($topic['lastPost']['userId'], $userIds) )
            {
                array_push($userIds, $topic['lastPost']['userId']);
            }
        }

        $addTopicUrl = OW::getRouter()->urlForRoute('add-topic', array('groupId' => $forumGroup->getId()));
        $this->assign('addTopicUrl', $addTopicUrl);

        $additionalInfo = array();
        if (isset($paramObj->additionalParamList)) {
            $additionalInfo = $paramObj->additionalParamList;
        }
        $params = array('info' => array('group_object' => $groupDto),'entity' => $this->entity, 'entityId' => $this->entityId, 'action' => 'add_topic', 'additionalInfo' => $additionalInfo);
        $event = new OW_Event('forum.check_permissions', $params);
        OW::getEventManager()->trigger($event);
        $canAdd = $event->getData();
        if (!empty($forumGroup )) {
            $section = FORUM_BOL_SectionDao::getInstance()->findById($forumGroup->sectionId);
        }
        if (!empty($forumGroup )&& isset($forumGroup->entityId) && isset($section) && $section->entity=='groups' && FRMSecurityProvider::checkPluginActive('groups', true)) {
            $isChannel = false;
            if (isset($paramObj->additionalParamList['isChannel'])) {
                $isChannel = $paramObj->additionalParamList['isChannel'];
            } else {
                $channelEvent = OW::getEventManager()->trigger(new OW_Event('frmgroupsplus.on.channel.add.widget', array('groupId' => $forumGroup->entityId, 'group' => $groupDto)));
                if (isset($channelEvent->getData()['channelParticipant'])) {
                    $isChannelParticipant = $channelEvent->getData()['channelParticipant'];
                    if (isset($isChannelParticipant) && $isChannelParticipant) {
                        $isChannel = true;
                    }
                }
            }
            $isManager = false;
            if (isset($paramObj->additionalParamList['currentUserIsManager'])) {
                $isManager = $paramObj->additionalParamList['currentUserIsManager'];
            }
            if ((($groupDto != null && $groupDto->userId == OW::getUser()->getId()) || $isManager) && $isChannel) {
                $isChannel = false;
            }

            $isAuthorizedCreate = true;
            $groupSettingEvent = OW::getEventManager()->trigger(new OW_Event('can.create.topic', array('groupId' => $forumGroup->entityId, 'additionalInfo' => $paramObj->additionalParamList)));
            if (isset($groupSettingEvent->getData()['accessCreateTopic'])) {
                $isAuthorizedCreate = $groupSettingEvent->getData()['accessCreateTopic'];
            }
            if ($groupDto == null) {
                $groupDto = GROUPS_BOL_Service::getInstance()->findGroupById($forumGroup->entityId);
            }

            $canEdit = false;
            if (isset($paramObj->additionalParamList['currentUserIsManager'])) {
                $isCurrentUserManager = $paramObj->additionalParamList['currentUserIsManager'];
                $canEdit = $isCurrentUserManager || GROUPS_BOL_Service::getInstance()->isCurrentUserCanEdit($groupDto, false);
            } else {
                $canEdit = GROUPS_BOL_Service::getInstance()->isCurrentUserCanEdit($groupDto);
            }

            if (!$canEdit) {
                if (!$isAuthorizedCreate) {
                    $canAdd = false;
                } else if ($isAuthorizedCreate && $isChannel) {
                    $canAdd = false;
                }
            }
        }
        $this->assign('canAdd', $canAdd);

        $attachments = FORUM_BOL_PostAttachmentService::getInstance()->getAttachmentsCountByTopicIdList($topicIds);
        $this->assign('attachments', $attachments);

        $usernames = BOL_UserService::getInstance()->getUserNamesForList($userIds);
        $this->assign('usernames', $usernames);

        $displayNames = BOL_UserService::getInstance()->getDisplayNamesForList($userIds);
        $this->assign('displayNames', $displayNames);

        $this->assign('topicList', $topicList);

        if ( $canAdd )
        {
            $this->setSettingValue(
                self::SETTING_TOOLBAR,
                array(
                    array(
                        'label' => OW::getLanguage()->text('base', 'view_all'),
                        'href' => OW::getRouter()->urlForRoute('group-default', array('groupId' => $forumGroup->getId()))
                    ),
                    array(
                        'label' => OW::getLanguage()->text('forum', 'add_new'),
                        'href' => OW::getRouter()->urlForRoute('add-topic', array('groupId' => $forumGroup->getId()))
                    )
                )
            );

            $settingForumEvent = OW::getEventManager()->trigger(new OW_Event('on.load.group.forum.widget',
                array('groupId' => $forumGroup->entityId,'setting'=>$this->getRunTimeSettingList())));
            if (isset($settingForumEvent->getData()['toolbar'])) {
                $this->setSettingValue(self::SETTING_TOOLBAR,$settingForumEvent->getData()['toolbar']);
            }
        }
        else
        {
            $this->setSettingValue(
                self::SETTING_TOOLBAR,
                array(
                    array(
                        'label' => OW::getLanguage()->text('base', 'view_all'),
                        'href' => OW::getRouter()->urlForRoute('group-default', array('groupId' => $forumGroup->getId()))
                    )
                )
            );
        }
    }

    public static function getStandardSettingValueList()
    {
        return array(
            self::SETTING_SHOW_TITLE => true,
            self::SETTING_TITLE => OW::getLanguage()->text('forum', 'widget_latest_topics_label'),
            self::SETTING_WRAP_IN_BOX => false,
            self::SETTING_ICON => self::ICON_FILES
        );
    }

    public static function getSettingList()
    {
        $settingList = array();

        $settingList['topicCount'] = array(
            'presentation' => self::PRESENTATION_NUMBER,
            'label' => OW::getLanguage()->text('forum', 'cmp_widget_forum_topics_count'),
            'value' => 4
        );

        return $settingList;
    }

    public static function getAccess()
    {
        return self::ACCESS_ALL;
    }
}