<?php
/**
 * Forum group action controller
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow.ow_plugins.forum.controllers
 * @since 1.0
 */
class FORUM_CTRL_Group extends OW_ActionController
{
    /**
     * @var FORUM_BOL_ForumService
     */
    private $forumService;

    /**
     * Class constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->forumService = FORUM_BOL_ForumService::getInstance();

        if ( !OW::getRequest()->isAjax() )
        {
            OW::getNavigation()->activateMenuItem(OW_Navigation::MAIN, 'forum', 'forum');
        }
    }

    /**
     * Controller's default action
     *
     * @param array $params
     * @throws Redirect404Exception
     */
    public function index( array $params )
    {
        if ( !isset($params['groupId']) || !($groupId = (int) $params['groupId']) )
        {
            throw new Redirect404Exception();
        }

        $groupInfo = $this->forumService->getGroupInfo($groupId);
        if ( !$groupInfo )
        {
            throw new Redirect404Exception();
        }
        
        $forumSection = $this->forumService->findSectionById($groupInfo->sectionId);
        if ( !$forumSection )
        {
            throw new Redirect404Exception();
        }

        // remember the last forum page
        OW::getSession()->set('last_forum_page', OW_URL_HOME . OW::getRequest()->getRequestUri());
        $this->addComponent('groupCmp', new FORUM_CMP_ForumGroup(array('groupId' => $params['groupId'], 'caption' => true)));

        $params = array(
            "sectionKey" => "forum",
            "entityKey" => "group",
            "title" => "forum+meta_title_group",
            "description" => "forum+meta_desc_group",
            "keywords" => "forum+meta_keywords_group",
            "vars" => array( "group_name" => $groupInfo->name, "group_description" => $groupInfo->description )
        );

        OW::getEventManager()->trigger(new OW_Event("base.provide_page_meta_info", $params));

        $this->setDocumentKey("forum_index");
    }
}
