<?php
/**
 * Created by PhpStorm.
 * User: Seyed Ismail Mirvakili
 * Date: 2/26/18
 * Time: 1:10 PM
 */
class FRMQUESTIONS_MCMP_Answers extends OW_MobileComponent
{
    const AVATARS_THRESHOLD = 3;
    /**
     * Constructor.
     *
     * @param array $idList
     * @param $totalCount
     * @param $additionalInfo
     */
    public function __construct( array $idList, $totalCount, $additionalInfo = array())
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

        $count = count($users);
        $otherCount = $totalCount - ($count > self::AVATARS_THRESHOLD ? self::AVATARS_THRESHOLD : $count);
        $otherCount = $otherCount < 0 ? 0 : $otherCount;

        $title = OW::getLanguage()->text('frmquestions','more_users_title',array('count'=>$otherCount));
        $this->assign('title', $title);

        $this->assign('otherCount', $otherCount);

        $this->assign('users', $users);

        $staticUrl = OW::getPluginManager()->getPlugin('frmquestions')->getStaticUrl();
        $this->assign('staticUrl', $staticUrl);

        $this->setTemplate(OW::getPluginManager()->getPlugin('frmquestions')->getMobileCmpViewDir() . 'answers.html');
    }
}