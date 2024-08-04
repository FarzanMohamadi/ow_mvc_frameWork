<?php
class FRMMOBILEACCOUNT_Cron extends OW_Cron
{

    public function __construct()
    {
        parent::__construct();
        $this->addJob('defaultUsernameNotify', 60*24);
    }

    public function run()
    {

    }

    public function defaultUsernameNotify()
    {
        $username_prefix = OW::getConfig()->getValue("frmmobileaccount","username_prefix");
        $userDao = BOL_UserDao::getInstance();

        $ex = new OW_Example();
        $ex->andFieldLike('username', '%'.$username_prefix.'%');
        $userIds = $userDao->findIdListByExample($ex);

        $adminId = BOL_AuthorizationService::getInstance()->getSuperModeratorUserId();
        $avatars = BOL_AvatarService::getInstance()->getDataForUserAvatars(array($adminId));

        $params = array(
            'pluginKey' => 'frmmobileaccount',
            'entityType' => 'change-username',
            'entityId' => $adminId,
            'action' => 'register',
            'userId' => $adminId,
            'time' => time()
        );

        $data = array(
            'avatar' => $avatars[$adminId],
            'string' => array(
                'key' => 'frmmobileaccount+change_your_username_notification'
            ),
            'url' => OW::getRouter()->urlForRoute('base_edit')
        );

        $event = new OW_Event('notifications.batch.add',
            ['userIds'=>$userIds, 'params'=>$params],
            $data);
        OW::getEventManager()->trigger($event);
    }
}
