<?php
class FRMFORUMPLUS_CMP_TopicGroupWidget extends BASE_CLASS_Widget
{
    private $forumService;

    /**
     * Class constructor
     */
    public function __construct( BASE_CLASS_WidgetParameter $paramObj )
    {
        if(!OW::getUser()->isAuthenticated())
        {
            throw new Redirect404Exception();
        }

        if (!FRMSecurityProvider::checkPluginActive('groups', true)) {
            return;
        }
        if (!FRMSecurityProvider::checkPluginActive('forum', true)) {
            return;
        }
        parent::__construct();

        OW::getEventManager()->trigger(new OW_Event('on.load.post.list.in.forum'));
        $this->forumService = FORUM_BOL_ForumService::getInstance();

        $confTopicCount = (int) $paramObj->customParamList['topicCount'];

        $confPostLength = (int) $paramObj->customParamList['postLength'];

        $selectedGroupIds = null;
        if(isset($paramObj->customParamList['groupTopics']) && $paramObj->customParamList['groupTopics'] ==FRMFORUMPLUS_BOL_Service::SELECTED_GROUPS_TOPICS)
        {
            $config = OW::getConfig();
            if($config->configExists('frmforumplus','selected_groups_forums')) {
                $selectedGroupIds = json_decode($config->getValue('frmforumplus', 'selected_groups_forums'), true);
            }
        }

        if ( OW::getUser()->isAuthorized('forum') )
        {
            $excludeGroupIdList = array();
        }
        else
        {
            $excludeGroupIdList = $this->forumService->getPrivateUnavailableGroupIdList(OW::getUser()->getId());
        }
        $groupIds = array();
        $userId = OW::getUser()->getId();
        $topics = array();
        if( is_array($selectedGroupIds) && sizeof($selectedGroupIds)>1)
        {
            foreach ($selectedGroupIds as $selectedGroupId) {
                $topics = array_merge($topics, $this->forumService->getUserLatestGroupsTopicList($confTopicCount, $excludeGroupIdList, $userId, array($selectedGroupId)));
            }
        }else {
            $topics = $this->forumService->getUserLatestGroupsTopicList($confTopicCount, $excludeGroupIdList, $userId, $selectedGroupIds);
        }

        if ( OW::getConfig()->configExists('frmforumplus', 'headerForumGroupWidgetHtml') )
        {
            $headerForumGroupWidgetHtml = OW::getConfig()->getValue('frmforumplus', 'headerForumGroupWidgetHtml');
            if(isset($headerForumGroupWidgetHtml))
            {
                $this->assign('headerForumGroupWidgetHtml',$headerForumGroupWidgetHtml);
            }
        }
        if ( sizeof($topics)>0 )
        {
            usort($topics, function($firstItem, $secondItem) {
                if (isset($firstItem['lastPostId']) && isset($secondItem['lastPostId'])) {
                    return (int) $secondItem['lastPostId'] - $firstItem['lastPostId'];
                }
                return (int) $secondItem['id'] - $firstItem['id'];
            });

            $userIds = array();

            $toolbars = array();

            // this block code provide data for sorting topics by their group
            //  Start:
            $topicEntity = array();
            $groupEntityIds = array_unique(array_column( $topics, 'groupEntityId'));
            rsort($groupEntityIds);
            foreach ($groupEntityIds as $groupEntityId){
                $topicEntity[$groupEntityId] = array();
            }
            // End

            foreach ( $topics as &$topic )
            {
                $stringRenderer = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_BEFORE_RENDER_STRING, array('string' => $topic['lastPost']['text'])));
                if(isset($stringRenderer->getData()['string'])){
                    $topic['lastPost']['text'] = ($stringRenderer->getData()['string']);
                }
                if ( !in_array($topic['lastPost']['userId'], $userIds) )
                {
                    array_push($userIds, $topic['lastPost']['userId']);
                }

                if ( !in_array($topic['groupId'], $groupIds) )
                {
                    array_push($groupIds, $topic['groupId']);
                }
                $topicEntity[$topic['groupEntityId']] = array_merge($topicEntity[$topic['groupEntityId']],array($topic));
            }

            // this block code aims for sorting topics by their group
            //  Start:
            $topics =array();
            foreach ($topicEntity as $topicItem){
                $topics=array_merge($topics,$topicItem);
            }
            // end

            $this->assign('topics', $topics);
            $avatars = BOL_AvatarService::getInstance()->getDataForUserAvatars($userIds, true, false);
            foreach ( $avatars as $avatar )
            {
                $userId = $avatar['userId'];
                $avatars[$userId]['url'] = BOL_UserService::getInstance()->getUserUrl($userId);
            }

            $this->assign('avatars', $avatars);

            $urls = BOL_UserService::getInstance()->getUserUrlsForList($userIds);

            // toolbars
            foreach ( $topics as $key => $value )
            {
                $userId = $value['lastPost']['userId'];
                $toolbars[$value['lastPost']['postId']]['user'] = array(
                    'class' => 'ow_icon_control ow_ic_user',
                    'href' => !empty($urls[$userId]) ? $urls[$userId] : '#',
                    'label' => !empty($avatars[$userId]['title']) ? $avatars[$userId]['title'] : ''
                );

                $toolbars[$value['lastPost']['postId']]['date'] = array(
                    'label' => $value['lastPost']['createStamp'],
                    'class' => 'ow_ipc_date'
                );
            }
            $this->assign('toolbars', $toolbars);

            $this->assign('postLength', $confPostLength);

            $groups = $this->forumService->findGroupByIdList($groupIds);

            $groupList = array();

            $sectionIds = array();

            foreach ( $groups as $group )
            {
                $groupList[$group->id] = $group;

                if ( !in_array($group->sectionId, $sectionIds) )
                {
                    array_push($sectionIds, $group->sectionId);
                }
            }
            $this->assign('groups', $groupList);

            $sectionList = $this->forumService->findSectionsByIdList($sectionIds);
            $this->assign('sections', $sectionList);
        }
        else
        {
            if (!OW::getUser()->isAuthorized('forum') && !OW::getUser()->isAuthorized('forum', 'edit') || OW::getUser()->isAdmin() )
            {
                $this->setVisible(false);

                return;
            }

            $this->assign('topics', null);
        }
    }

    public static function getSettingList()
    {
        $settingList = array();

        $settingList['topicCount'] = array(
            'presentation' => self::PRESENTATION_NUMBER,
            'label' => OW::getLanguage()->text('frmforumplus', 'cmp_widget_forum_topics_count'),
            'value' => 5
        );

        $settingList['postLength'] = array(
            'presentation' => self::PRESENTATION_NUMBER,
            'label' => OW::getLanguage()->text('frmforumplus', 'cmp_widget_forum_topics_post_lenght'),
            'value' => 200
        );

        $settingList['groupTopics'] = array(
            'presentation' => self::PRESENTATION_SELECT,
            'label' => OW::getLanguage()->text('frmforumplus', 'view_topic_settings'),
            'value' => BOL_SiteStatisticService::PERIOD_TYPE_LAST_7_DAYS,
            'optionList' => array(
                FRMFORUMPLUS_BOL_Service::LATEST_TOPICS => OW::getLanguage()->text('frmforumplus', 'view_latest_groups_topics'),
                FRMFORUMPLUS_BOL_Service::SELECTED_GROUPS_TOPICS => OW::getLanguage()->text('frmforumplus', 'view_selected_groups_topics'),
            )
        );

        return $settingList;
    }


    public static function getAccess()
    {
        return self::ACCESS_MEMBER;
    }

    public static function getStandardSettingValueList()
    {
        return array(
            self::SETTING_TITLE => OW::getLanguage()->text('frmforumplus', 'group_topics_widget'),
            self::SETTING_ICON => self::ICON_FILES,
            self::SETTING_SHOW_TITLE => true
        );
    }
}