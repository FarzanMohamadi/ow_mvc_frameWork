<?php
/**
 * Forum base action controller
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow.ow_plugins.forum.controllers
 * @since 1.0
 */
class FORUM_CTRL_Index extends OW_ActionController
{

    /**
     * Controller's default action
     */
    public function index()
    {
        $isModerator = OW::getUser()->isAuthorized('forum');
        $viewPermissions = OW::getUser()->isAuthorized('forum', 'view');

        if ( !$viewPermissions && !$isModerator )
        {
            $status = BOL_AuthorizationService::getInstance()->getActionStatus('forum', 'view');
            throw new AuthorizationException($status['msg']);
        }

        $forumService = FORUM_BOL_ForumService::getInstance();

        $this->assign('customizeUrl', OW::getRouter()->urlForRoute('customize-default'));
        $this->assign('isModerator', $isModerator);

        $userId = OW::getUser()->getId();
        $sectionGroupList = $forumService->getSectionGroupList($userId);
                
        $singleForumMode = $forumService->isSingleForumMode($sectionGroupList);
        $this->assign('singleMode', $singleForumMode);

        if ( $singleForumMode )
        {
            $firstSection = array_shift($sectionGroupList);
            $firstGroup = $firstSection['groups'][0];
            $groupId = $firstGroup['id'];

            $this->addComponent('groupCmp', new FORUM_CMP_ForumGroup(array('groupId' => $groupId, 'caption' => false)));

            $groupName = htmlspecialchars($firstGroup['name']);
            OW::getDocument()->setHeading(OW::getLanguage()->text('forum', 'forum_page_heading', array('forum' => $groupName)));
//            OW::getDocument()->setTitle($groupName);
//            OW::getDocument()->setDescription(htmlspecialchars($firstGroup['description']));

            $params = array(
                "sectionKey" => "forum",
                "entityKey" => "home",
                "title" => "forum+meta_title_home",
                "description" => "forum+meta_desc_home",
                "keywords" => "forum+meta_keywords_home"
            );

            OW::getEventManager()->trigger(new OW_Event("base.provide_page_meta_info", $params));

        }
        else
        {
            $authors = $forumService->getSectionGroupAuthorList($sectionGroupList);
            $this->assign('sectionGroupList', $sectionGroupList);

            $userNames = BOL_UserService::getInstance()->getUserNamesForList($authors);
            $this->assign('userNames', $userNames);

            $displayNames = BOL_UserService::getInstance()->getDisplayNamesForList($authors);
            $this->assign('displayNames', $displayNames);

            OW::getDocument()->setHeading(OW::getLanguage()->text('forum', 'forum'));
            $this->addComponent('search', new FORUM_CMP_ForumSearch(array('scope' => 'all_forum')));
        }

        $plugin = OW::getPluginManager()->getPlugin('forum');
        $template = $plugin->getCtrlViewDir() . 'index.html';
        $this->setTemplate($template);
        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('forum')->getStaticJsUrl() .'forum.js');
        OW::getDocument()->addStyleSheet(OW_PluginManager::getInstance()->getPlugin("forum")->getStaticCssUrl() .'forum.css');

        OW::getDocument()->setHeadingIconClass('ow_ic_forum');
        OW::getDocument()->setDescription(OW::getLanguage()->text('forum', 'meta_description_forums'));

        $this->setDocumentKey("forum_list_index");
    }
}
