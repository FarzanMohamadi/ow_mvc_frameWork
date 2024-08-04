<?php
/**
 * Forum topics widget component
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow.ow_plugins.forum.components
 * @since 1.0
 */
class FORUM_CMP_ForumTopicsWidget extends BASE_CLASS_Widget
{
    private $forumService;

    /**
     * Class constructor
     */
    public function __construct( BASE_CLASS_WidgetParameter $paramObj )
    {
        parent::__construct();

        $this->forumService = FORUM_BOL_ForumService::getInstance();

        $confTopicCount = (int) $paramObj->customParamList['topicCount'];

        $confPostLength = (int) $paramObj->customParamList['postLength'];

        if ( OW::getUser()->isAuthorized('forum') )
        {
            $excludeGroupIdList = array();
        }
        else
        {
            $excludeGroupIdList = $this->forumService->getPrivateUnavailableGroupIdList(OW::getUser()->getId());
        }
        $eventLatestTopics = new OW_Event('frmforumplus.on.get.latest.topics', array('confTopicCount'=>$confTopicCount, 'excludeGroupIdList'=>$excludeGroupIdList,'forumWidget'=>$this,'createMenu'=>true));
        OW::getEventManager()->trigger($eventLatestTopics);
        $topics = $this->forumService->getLatestTopicList($confTopicCount, $excludeGroupIdList);
        if(isset($eventLatestTopics->getData()['groupIds']) && sizeof($eventLatestTopics->getData()['groupIds'])>0)
        {
            $groupIds = $eventLatestTopics->getData()['groupIds'];
        }else{
            $groupIds = array();
        }
        if ( $topics )
        {
            $userIds = array();

            $toolbars = array();

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
            }
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

            $tb = array();
            if ( OW::getUser()->isAuthorized('forum', 'edit') || OW::getUser()->isAdmin())
            {
                $tb[] = array(
                    'label' => OW::getLanguage()->text('forum', 'add_new'),
                    'href' => OW::getRouter()->urlForRoute('add-topic-default')
                );
            }
            $tb[] = array(
                'label' => OW::getLanguage()->text('forum', 'goto_forum'),
                'href' => OW::getRouter()->urlForRoute('forum-default')
            );

            $this->setSettingValue(self::SETTING_TOOLBAR, $tb);
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
            'label' => OW::getLanguage()->text('forum', 'cmp_widget_forum_topics_count'),
            'value' => 4
        );

        $settingList['postLength'] = array(
            'presentation' => self::PRESENTATION_NUMBER,
            'label' => OW::getLanguage()->text('forum', 'cmp_widget_forum_topics_post_lenght'),
            'value' => 200
        );

        return $settingList;
    }

    public static function validateSettingList( $settingList )
    {
        parent::validateSettingList($settingList);

        $validationMessage = OW::getLanguage()->text('forum', 'cmp_widget_forum_topics_count_msg');

        if ( !preg_match('/^\d+$/', $settingList['topicCount']) )
        {
            throw new WidgetSettingValidateException($validationMessage, 'topicCount');
        }
        if ( $settingList['topicCount'] > 20 )
        {
            throw new WidgetSettingValidateException($validationMessage, 'topicCount');
        }

        $validationMessage = OW::getLanguage()->text('forum', 'cmp_widget_forum_topics_post_length_msg');

        if ( !preg_match('/^\d+$/', $settingList['postLength']) )
        {
            throw new WidgetSettingValidateException($validationMessage, 'postLength');
        }
        if ( $settingList['postLength'] > 1000 )
        {
            throw new WidgetSettingValidateException($validationMessage, 'postLength');
        }
    }

    public static function getAccess()
    {
        return self::ACCESS_ALL;
    }

    public static function getStandardSettingValueList()
    {
        return array(
            self::SETTING_TITLE => OW::getLanguage()->text('forum', 'forum_topics_widget'),
            self::SETTING_ICON => self::ICON_FILES,
            self::SETTING_SHOW_TITLE => true
        );
    }
}