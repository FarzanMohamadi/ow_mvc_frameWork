<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow.plugin.forum.mobile.controllers
 * @since 1.6.0
 */
class FORUM_MCTRL_Section extends FORUM_MCTRL_AbstractForum
{
    /**
     * Section index
     * 
     * @param array $params
     */
    public function index( array $params )
    {
        if ( !isset($params['sectionId']) || !($sectionId = (int) $params['sectionId']) )
        {
            throw new Redirect404Exception();
        }

        // get the section info
        $forumSection = $this->forumService->findSectionById($sectionId);
        if ( !$forumSection || $forumSection->isHidden )
        {
            throw new Redirect404Exception();
        }

        $isModerator = OW::getUser()->isAuthorized('forum');
        $canEdit = OW::getUser()->isAuthorized('forum', 'edit') || $isModerator ? true : false;

        // include js translations
        OW::getLanguage()->addKeyForJs('forum', 'post_attachment');
        OW::getLanguage()->addKeyForJs('forum', 'attached_files');
        OW::getLanguage()->addKeyForJs('forum', 'confirm_delete_all_attachments');

        // assign view variables
        $this->assign('section', $forumSection);
        $this->assign('canEdit', $canEdit);
        $this->assign('promotion', BOL_AuthorizationService::getInstance()->getActionStatus('forum', 'edit'));

        // remember the last forum page
        OW::getSession()->set('last_forum_page', OW_URL_HOME . OW::getRequest()->getRequestUri());

//        OW::getDocument()->setDescription(OW::getLanguage()->text('forum', 'meta_description_forums'));
        OW::getDocument()->setHeading(OW::getLanguage()->text('forum', 'forum_section'));
//        OW::getDocument()->setTitle(OW::getLanguage()->text('forum', 'forum_section'));

        $params = array(
            "sectionKey" => "forum",
            "entityKey" => "section",
            "title" => "forum+meta_title_section",
            "description" => "forum+meta_desc_section",
            "keywords" => "forum+meta_keywords_section",
            "vars" => array( "section_name" => $forumSection->name )
        );

        OW::getEventManager()->trigger(new OW_Event("base.provide_page_meta_info", $params));
        OW::getEventManager()->trigger(new OW_Event('frmwidgetplus.general.before.view.render', array('targetPage' => 'forum')));
    }
}