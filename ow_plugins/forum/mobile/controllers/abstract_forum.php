<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow.plugin.forum.mobile.controllers
 * @since 1.6.0
 */
abstract class FORUM_MCTRL_AbstractForum extends OW_MobileActionController
{
    /**
     * Forum service
     * 
     * @var FORUM_BOL_ForumService 
     */
    protected $forumService;

    public function __construct()
    {
        parent::__construct();

        // check autorization
        $isModerator = OW::getUser()->isAuthorized('forum');
        $viewPermissions = OW::getUser()->isAuthorized('forum', 'view');

        if ( !$viewPermissions && !$isModerator )
        {
            $status = BOL_AuthorizationService::getInstance()->getActionStatus('forum', 'view');
            throw new AuthorizationException($status['msg']);
        }

        $this->forumService = FORUM_BOL_ForumService::getInstance();
    }
}

