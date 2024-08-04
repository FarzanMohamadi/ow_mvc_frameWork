<?php
/**
 * Forum section class.
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow.ow_plugins.forum.mobile.components
 * @since 1.0
 */
class FORUM_MCMP_ForumSection extends OW_MobileComponent
{
    /**
     * Class constructor
     * 
     * @param array $params
     */
    public function __construct(array $params = array())
    {
        parent::__construct();

        $sectionId = !empty($params['sectionId']) 
            ? (int) $params['sectionId'] 
            : null;

        $forumService = FORUM_BOL_ForumService::getInstance();
        $userId = OW::getUser()->getId();

        $sectionGroupList = $forumService->getSectionGroupList($userId, $sectionId);
        $authors = $forumService->getSectionGroupAuthorList($sectionGroupList);

        // assign view variables
        $this->assign('singleMode', null != $sectionId);
        $this->assign('sectionGroupList', $sectionGroupList);
        $this->assign('displayNames', BOL_UserService::getInstance()->getDisplayNamesForList($authors));
    }
}