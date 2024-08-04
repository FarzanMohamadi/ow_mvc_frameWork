<?php
/**
 * Forum section action controller
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow.ow_plugins.forum.controllers
 * @since 1.0
 */
class FORUM_CTRL_Section extends OW_ActionController
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
        $this->setDocumentKey("forum_section_index");

        if ( !isset($params['sectionId']) || !($sectionId = (int) $params['sectionId']) )
        {
            throw new Redirect404Exception();
        }
        
        $forumSection = $this->forumService->findSectionById($sectionId);
        if ( !$forumSection || $forumSection->isHidden )
        {
            throw new Redirect404Exception();
        }

        $userId = OW::getUser()->getId();

        $bcItems = array(
            array(
                'href' => OW::getRouter()->urlForRoute('forum-default'),
                'label' => OW::getLanguage()->text('forum', 'forum_group')
            ),
            array(
                'label' => $forumSection->name
            )
        );

        $breadCrumbCmp = new BASE_CMP_Breadcrumb($bcItems);
        $this->addComponent('breadcrumb', $breadCrumbCmp);

        $sectionGroupList = $this->forumService->getSectionGroupList($userId, $sectionId);

        $authors = $this->forumService->getSectionGroupAuthorList($sectionGroupList);
        $this->assign('sectionGroupList', $sectionGroupList);

        $userNames = BOL_UserService::getInstance()->getUserNamesForList($authors);
        $this->assign('userNames', $userNames);

        $displayNames = BOL_UserService::getInstance()->getDisplayNamesForList($authors);
        $this->assign('displayNames', $displayNames);

        $this->addComponent('search', new FORUM_CMP_ForumSearch(array('scope' => 'section', 'sectionId' => $sectionId)));

        // remember the last forum page
        OW::getSession()->set('last_forum_page', OW_URL_HOME . OW::getRequest()->getRequestUri());

        OW::getDocument()->setHeading(OW::getLanguage()->text('forum', 'forum'));
        OW::getDocument()->setHeadingIconClass('ow_ic_forum');

        $params = array(
            "sectionKey" => "forum",
            "entityKey" => "section",
            "title" => "forum+meta_title_section",
            "description" => "forum+meta_desc_section",
            "keywords" => "forum+meta_keywords_section",
            "vars" => array( "section_name" => $forumSection->name )
        );

        OW::getEventManager()->trigger(new OW_Event("base.provide_page_meta_info", $params));
    }
}
