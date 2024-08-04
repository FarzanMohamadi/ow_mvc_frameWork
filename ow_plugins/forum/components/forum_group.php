<?php
/**
 * Forum group component class.
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow.ow_plugins.forum.components
 * @since 1.5.3
 */
class FORUM_CMP_ForumGroup extends OW_Component
{
    /**
     * @var FORUM_BOL_ForumService
     */
    private $forumService;

    public function __construct( array $params )
    {
        parent::__construct();

        $this->forumService = FORUM_BOL_ForumService::getInstance();

        if ( !isset($params['groupId']) || !($groupId = (int) $params['groupId']) )
        {
            $this->setVisible(false);
            return;
        }

        $groupInfo = $this->forumService->getGroupInfo($groupId);
        if ( !$groupInfo )
        {
            $this->setVisible(false);
            return;
        }

        $forumSection = $this->forumService->findSectionById($groupInfo->sectionId);
        if ( !$forumSection )
        {
            $this->setVisible(false);
            return;
        }

        $lang = OW::getLanguage();
        $isHidden = $forumSection->isHidden;
        $userId = OW::getUser()->getId();
        $authError = $lang->text('base', 'authorization_failed_feedback');
        $forumGroup=$this->forumService->findGroupByEntityId('groups', $groupInfo->entityId);
        if ( $isHidden )
        {
            $isModerator = OW::getUser()->isAuthorized($forumSection->entity);

            $event = new OW_Event('forum.can_view', array(
                'entity' => $forumSection->entity,
                'entityId' => $groupInfo->entityId
            ), true);
            OW::getEventManager()->trigger($event);

            $canView = $event->getData();

            $eventParams = array('entity' => $forumSection->entity, 'entityId' => $groupInfo->entityId, 'action' => 'add_topic');
            $event = new OW_Event('forum.check_permissions', $eventParams);
            OW::getEventManager()->trigger($event);

            $canEdit = $event->getData();
        }
        else
        {
            $isModerator = OW::getUser()->isAuthorized('forum');

            $canView = OW::getUser()->isAuthorized('forum', 'view');
            if ( !$canView )
            {
                $viewError = BOL_AuthorizationService::getInstance()->getActionStatus('forum', 'view');
                $authError = $viewError['msg'];
            }

            $canEdit = OW::getUser()->isAuthorized('forum', 'edit');

            $canEdit = $canEdit || $isModerator ? true : false;
        }

        if (!empty($forumGroup)) {
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
            if ( empty($groupDto) ) {
                throw new Redirect404Exception();
            }

            $isModerator = GROUPS_BOL_Service::getInstance()->isCurrentUserCanEdit($groupDto);
            if (!$isModerator) {
                if (!$isAuthorizedCreate) {
                    $canEdit = false;
                } else if ($isAuthorizedCreate && $isChannel) {
                    $canEdit = false;
                }
            }
        }

        if ( $groupInfo->isPrivate )
        {
            if ( !$userId )
            {
                $this->assign('authError', $authError);
                return;
            }
            else if ( !$isModerator )
            {
                if ( !$this->forumService->isPrivateGroupAvailable($userId, json_decode($groupInfo->roles)) )
                {
                    $this->assign('authError', $authError);
                    return;
                }
            }
        }

        if ( !$canView )
        {
            $this->assign('authError', $authError);
            return;
        }

        $page = !empty($_GET['page']) && (int) $_GET['page'] ? abs((int) $_GET['page']) : 1;

        if ( !$groupInfo )
        {
            $forumUrl = OW::getRouter()->urlForRoute('forum-default');
            OW::getApplication()->redirect($forumUrl);
        }

        $sort_order = isset($_GET['order']) && strtolower($_GET['order']) == 'asc' ? 'ASC' : 'DESC';
        $this->assign('sort_order',$sort_order);
        $up_or_down = str_replace(array('ASC','DESC'), array('up','down'), $sort_order);
        $this->assign('up_or_down',$up_or_down);
        $asc_or_desc = $sort_order == 'ASC' ? 'desc' : 'asc';
        $this->assign('asc_or_desc',$asc_or_desc);
        $topicColumns = FORUM_BOL_ForumService::getInstance()->getTopicColumns();
        $column = isset($_GET['column']) && in_array($_GET['column'], $topicColumns) ? $_GET['column'] : $topicColumns[3];
        $this->assign('column',$column);
        if(sizeof($_GET) > 0 && !array_diff(array_keys($_GET), ['order','column']) == array_diff(['order','column'], array_keys($_GET)))
        {
            $this->assign('urlHasParams',true);
        }
        $this->assign('currentUrl',FORUM_BOL_ForumService::getInstance()->getCleanCurrentUrlWithoutSortParameters());

        $topicList = $this->forumService->getGroupTopicList($groupId, $page,null,array(),$column,$sort_order);
        $topicCount = $this->forumService->getGroupTopicCount($groupId);

        $topicIds = array();
        $userIds = $this->forumService->getGroupTopicAuthorList($topicList, $topicIds);

        $attachments = FORUM_BOL_PostAttachmentService::getInstance()->getAttachmentsCountByTopicIdList($topicIds);
        $this->assign('attachments', $attachments);

        $usernames = BOL_UserService::getInstance()->getUserNamesForList($userIds);
        $this->assign('usernames', $usernames);

        $displayNames = BOL_UserService::getInstance()->getDisplayNamesForList($userIds);
        $this->assign('displayNames', $displayNames);

        $perPage = $this->forumService->getTopicPerPageConfig();
        $pageCount = ($topicCount) ? ceil($topicCount / $perPage) : 1;
        $paging = new BASE_CMP_Paging($page, $pageCount, $perPage);
        $this->assign('paging', $paging->render());

        $addTopicUrl = OW::getRouter()->urlForRoute('add-topic', array('groupId' => $groupId));
        $this->assign('addTopicUrl', $addTopicUrl);

        $this->assign('topicCount',$topicCount);

        $this->assign('canEdit', $canEdit);
        $this->assign('newThemeCoreEnabled', FRMSecurityProvider::themeCoreDetector());
        $this->assign('groupId', $groupId);
        $this->assign('topicList', $topicList);
        $this->assign('isHidden', $isHidden);

        $enableAttachments = OW::getConfig()->getValue('forum', 'enable_attachments');
        $this->assign('enableAttachments', $enableAttachments);

        OW::getDocument()->addStyleSheet(OW::getPluginManager()->getPlugin('forum')->getStaticCssUrl().'forum.css');
        $showCaption = !empty($params['caption']) ? $params['caption'] : false;
        if ( $showCaption )
        {
            $groupName = htmlspecialchars($groupInfo->name);
            OW::getDocument()->setHeading(OW::getLanguage()->text('forum', 'forum_page_heading', array('forum' => $groupName)));
            OW::getDocument()->setHeadingIconClass('ow_ic_forum');

            OW::getDocument()->setTitle($groupName);
            OW::getDocument()->setDescription(
                OW::getLanguage()->text('forum', 'group_meta_description', array('group' => $groupName))
            );

            if ( $isHidden )
            {
                $event = new OW_Event('forum.find_forum_caption', array('entity' => $forumSection->entity, 'entityId' => $groupInfo->entityId));
                OW::getEventManager()->trigger($event);

                $eventData = $event->getData();

                /** @var OW_Component $componentForumCaption */
                $componentForumCaption = $eventData['component'];
                if (!empty($componentForumCaption))
                {
                    $this->assign('componentForumCaption', $componentForumCaption->render());
                }
                else
                {
                    $componentForumCaption = false;
                    $this->assign('componentForumCaption', $componentForumCaption);
                }

                OW::getNavigation()->deactivateMenuItems(OW_Navigation::MAIN);
                OW::getNavigation()->activateMenuItem(OW_Navigation::MAIN, $forumSection->entity, $eventData['key']);
            }
            else
            {
                $bcItems = array(
                    array(
                        'href' => OW::getRouter()->urlForRoute('forum-default'),
                        'label' => OW::getLanguage()->text('forum', 'forum_group')
                    ),
                    array(
                        'href' => OW::getRouter()->urlForRoute('section-default', array('sectionId' => $groupInfo->sectionId)),
                        'label' => $forumSection->name
                    ),
                    array(
                        'label' => $groupInfo->name
                    )
                );

                $breadCrumbCmp = new BASE_CMP_Breadcrumb($bcItems);
                $this->addComponent('breadcrumb', $breadCrumbCmp);
            }
        }

        $this->addComponent('search', new FORUM_CMP_ForumSearch(array('scope' => 'group', 'groupId' => $groupId)));
        $this->assign('showCaption', $showCaption);
    }
}