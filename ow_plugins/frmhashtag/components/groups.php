<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmhashtag
 * @since 1.0
 */
class FRMHASHTAG_CMP_Groups extends OW_Component
{

    public function __construct( array $idList, $allCount, $page = 1)
    {
        parent::__construct();
        $hashtagService = FRMHASHTAG_BOL_Service::getInstance();
        $idList = $hashtagService->checkGroupItemsForDisplay($idList);

        //paging
        $rpp = 10;
        $itemsCount = count($idList);
        if($page>0 && $page<=ceil($itemsCount / $rpp)) {
            $paging = new BASE_CMP_Paging($page, ceil($itemsCount / $rpp), 5);
            $this->addComponent('paging', $paging);
            $first = ($page - 1) * $rpp;
            $count = $rpp;
            $idList = array_slice($idList, $first, $count);
        }else{
            $idList = array();
        }

        $countInfo = OW::getLanguage()->text('frmhashtag', 'able_to_see_text', array('num'=>$itemsCount, 'all'=>$allCount));
        $this->assign('countInfo', $countInfo);

        $out = array();

        $avatarService = BOL_AvatarService::getInstance();
        foreach ( $idList as $key=>$fullItem )
        {
            $item = $fullItem['obj'];
            /* @var $item GROUPS_BOL_Group */
            $url = OW::getRouter()->urlForRoute('groups-view', array('groupId' => $item->id));
            $userCount = GROUPS_BOL_Service::getInstance()->findUserListCount($item->id);

            $feedId = null;
            $imageInfo = null;
            if($fullItem['type']=='groups-feed') {
                $feedId = $fullItem['id'];
            }
            if(isset($fullItem['context']) && ($fullItem['type']=='photo_comments' || $fullItem['type']=='multiple_photo_upload')) {
                $feedId = $fullItem['feed']->id;
            }
            if(isset($feedId)){
                $userId = $hashtagService->findUserIdByActionId($feedId);
                $toolbar = array(
                    array('label' => OW::getLanguage()->text('newsfeed','newsfeed_feed'), 'href' => OW::getRouter()->urlForRoute('newsfeed_view_item', array('actionId' => $feedId))),
                    array('label' => '-'),
                    array('label' => strip_tags($item->title), 'href' => $url)
                );
                $item->description = $hashtagService->findActionStatusByActionId($feedId);
                $title = BOL_UserService::getInstance()->getDisplayName($userId);
                $url = BOL_UserService::getInstance()->getUserUrl($userId);
                $imgSrc = $avatarService->getAvatarUrl($userId);
                $imageInfo = BOL_AvatarService::getInstance()->getAvatarInfo($userId, $imgSrc);
            }else{
                $title = strip_tags($item->title);
                $imgSrc = GROUPS_BOL_Service::getInstance()->getGroupImageUrl($item);
                $toolbar = array(
                    array(
                        'label' => OW::getLanguage()->text('groups', 'listing_users_label', array(
                            'count' => $userCount
                        ))
                    )
                );
            }

            $content = UTIL_String::truncate(strip_tags($item->description), 300, '...');
            $stringRenderer = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_BEFORE_RENDER_STRING, array('string' => $content)));
            if (isset($stringRenderer->getData()['string'])) {
                $content = ($stringRenderer->getData()['string']);
            }

            $out[$key] = array(
                'isFeed' => ($fullItem['type']=='groups-feed'),
                'feedId' => $fullItem['id'],
                'id' => $item->id,
                'url' => $url,
                'title' => $title,
                'imageTitle' => $title,
                'content' => $content,
                'time' => UTIL_DateTime::formatDate($item->timeStamp),
                'imageSrc' => $imgSrc,
                'imageInfo' => $imageInfo,
                'users' => $userCount,
                'toolbar' => $toolbar
            );
        }

        $this->assign('list', $out);
    }
}