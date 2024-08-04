<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow.plugin.forum.mobile.controllers
 * @since 1.6.0
 */
class FORUM_MCTRL_Forum extends FORUM_MCTRL_AbstractForum
{
    /**
     * Forum index
     */
    public function index()
    {
        $addTopic = false;

//        OW::getDocument()->setDescription(OW::getLanguage()->text('forum', 'meta_description_forums'));
        OW::getDocument()->setHeading(OW::getLanguage()->text('forum', 'forum_group'));
//        OW::getDocument()->setTitle(OW::getLanguage()->text('forum', 'forum_index'));

        $isModerator = OW::getUser()->isAuthorized('forum');

        if ( !empty($_GET['add_topic']) && OW::getUser()->isAuthenticated() )
        {
            $addTopic = true;
        }

        $this->assign('addTopic', $addTopic);
        $this->assign('canEdit', OW::getUser()->isAuthorized('forum', 'edit') || $isModerator ? true : false);
        $this->assign('promotion', BOL_AuthorizationService::getInstance()->getActionStatus('forum', 'edit'));

        $params = array(
            "sectionKey" => "forum",
            "entityKey" => "home",
            "title" => "forum+meta_title_home",
            "description" => "forum+meta_desc_home",
            "keywords" => "forum+meta_keywords_home"
        );

        OW::getEventManager()->trigger(new OW_Event("base.provide_page_meta_info", $params));
    }
}

