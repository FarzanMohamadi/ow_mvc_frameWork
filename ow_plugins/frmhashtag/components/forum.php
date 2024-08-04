<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmhashtag
 * @since 1.0
 */
class FRMHASHTAG_CMP_Forum extends OW_Component
{

    public function __construct( array $idList, $allCount, $page = 1)
    {
        parent::__construct();

        $forumService = FORUM_BOL_ForumService::getInstance();
        $hashtagService = FRMHASHTAG_BOL_Service::getInstance();
        $itemsResult = $hashtagService->checkForumItemsForDisplay($idList);
        $existingEntityIds = $itemsResult['existingEntityIds'];
        $postList = $itemsResult['postList'];
        $allCount = count($existingEntityIds);

        //paging
        $rpp = 10;
        $itemsCount = count($postList);
        if($page>0 && $page<=ceil($itemsCount / $rpp)) {
            $postList = array_reverse($postList);
            $paging = new BASE_CMP_Paging($page, ceil($itemsCount / $rpp), 5);
            $this->addComponent('paging', $paging);
            $first = ($page - 1) * $rpp;
            $count = $rpp;
            $postList = array_slice($postList, $first, $count);
        }else{
            $postList = array();
        }

        $countInfo = OW::getLanguage()->text('frmhashtag', 'able_to_see_text', array('num'=>$itemsCount, 'all'=>$allCount));
        $this->assign('countInfo', $countInfo);

        //------
        $iteration = 0;
        $toolbars = array();
        $userIds = array();
        $postIds = array();
        foreach ( $postList as &$post )
        {
            $content = UTIL_HtmlTag::linkify($post['text']);
            $stringRenderer = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_BEFORE_RENDER_STRING, array('string' => $content)));
            if (isset($stringRenderer->getData()['string'])) {
                $content = ($stringRenderer->getData()['string']);
            }
            $post['text'] = $content;
            $post['permalink'] = $post['postUrl'];
            $post['number'] = ($page - 1) * $forumService->getPostPerPageConfig() + $iteration + 1;

            // get list of users
            if ( !in_array($post['userId'], $userIds) )
                $userIds[$post['userId']] = $post['userId'];

            $toolbar = array();
            $label = $forumService->getTopicInfo($post['topicId'])['title'];
            if(mb_strlen($label)>100)
                $label = mb_substr($label,0, 100) . '...';
            array_push($toolbar, array('href' => $post['permalink'], 'label' => $label));

            $toolbars[$post['id']] = $toolbar;

            if ( count($post['edited']) && !in_array($post['edited']['userId'], $userIds) )
                $userIds[$post['edited']['userId']] = $post['edited']['userId'];

            $iteration++;

            array_push($postIds, $post['id']);
        }

        //----assign

        $this->assign('postList', $postList);
        $this->assign('toolbars', $toolbars);
        $avatars = BOL_AvatarService::getInstance()->getDataForUserAvatars($userIds);
        $this->assign('avatars', $avatars);
    }
}