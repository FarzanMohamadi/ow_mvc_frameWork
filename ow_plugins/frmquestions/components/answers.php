<?php
/**
 * Created by PhpStorm.
 * User: Seyed Ismail Mirvakili
 * Date: 2/26/18
 * Time: 1:10 PM
 */
class FRMQUESTIONS_CMP_Answers extends OW_Component
{
    const AVATARS_THRESHOLD = 3;
    /**
     * Constructor.
     *
     * @param array $idList
     * @param $totalCount
     * @param $additionalInfo
     */
    public function __construct( array $idList, $totalCount, $additionalInfo = array() )
    {
        parent::__construct();

        $userId = OW::getUser()->getId();
        $hiddenUser = false;
        if ( $userId && !in_array($userId, $idList) )
        {
            $hiddenUser = $userId;
            $idList[] = $userId;
        }

        $remainUserIds = array();
        $users = array();
        if (isset($additionalInfo['cache']['users_info'])) {
            foreach ($idList as $userIdAvatar) {
                if (isset($additionalInfo['cache']['users_info'][$userIdAvatar])) {
                    $users[$userIdAvatar] = $additionalInfo['cache']['users_info'][$userIdAvatar];
                } else {
                    $remainUserIds[] = $userIdAvatar;
                }
            }
        } else {
            $remainUserIds = $idList;
        }

        if (sizeof($remainUserIds) > 0) {
            $usersInfo = BOL_AvatarService::getInstance()->getDataForUserAvatars($remainUserIds, true, true, true, false);
            foreach ($usersInfo as $userIdAvatar) {
                $users[$userIdAvatar['userId']] = $userIdAvatar;
            }
        }

        if ($hiddenUser)
        {
            $users[$hiddenUser]['id'] = $hiddenUser;
            $this->assign('hiddenUser', $users[$hiddenUser]);
            unset($users[$hiddenUser]);
        }

        $count = isset($users) ? count($users) : 0;
        $otherCount = $totalCount - ($count > self::AVATARS_THRESHOLD ? self::AVATARS_THRESHOLD : $count);
        $otherCount = $otherCount < 0 ? 0 : $otherCount;

        $title = OW::getLanguage()->text('frmquestions','more_users_title',array('count'=>$otherCount));
        $this->assign('title', $title);

        $this->assign('otherCount', $otherCount);

        $this->assign('users', $users);
        $userIds = array();
        foreach ($idList as $item)
            $userIds[] = (int) $item;
        $showUsers = 'javascript: OW.showUsers('.json_encode($userIds).')';
        $this->assign('userIds', $showUsers);

        $staticUrl = OW::getPluginManager()->getPlugin('frmquestions')->getStaticUrl();
        $this->assign('staticUrl', $staticUrl);
    }
}