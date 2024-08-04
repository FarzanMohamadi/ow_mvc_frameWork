<?php
/**
 * Console friends section items component
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow.ow_plugins.friends.mobile.components
 * @since 1.6.0
 */
class FRIENDS_MCMP_ConsoleItems extends OW_MobileComponent
{
    /**
     * Constructor.
     */
    public function __construct(  $limit, $exclude = null )
    {
        parent::__construct();

        $service = FRIENDS_BOL_Service::getInstance();

        $userId = OW::getUser()->getId();
        $requests = $service->findRequestList($userId, time(), 0, $limit, $exclude);
        $items = self::prepareData($requests);

        $this->assign('items', $items);

        // Mark as viewed
        $service->markAllViewedByUserId($userId);

        $requestIdList = array();
        foreach ( $requests as $id => $request )
        {
            $requestIdList[] = $id;
        }

        $exclude = is_array($exclude) ? array_merge($exclude, $requestIdList) : $requestIdList;
        $loadMore = (bool) $service->count(null, $userId, FRIENDS_BOL_Service::STATUS_PENDING, null, null, $exclude);

        if ( !$loadMore )
        {
            $script = "OWM.trigger('mobile.console_hide_friends_load_more', {});";
            OW::getDocument()->addOnloadScript($script);
        }
    }

    public static function prepareData( $requests )
    {
        $userIdList = array();
        foreach ( $requests as $request )
        {
            if ( !in_array($request->userId, $userIdList) )
            {
                array_push($userIdList, $request->userId);
            }
        }

        $avatars = BOL_AvatarService::getInstance()->getDataForUserAvatars($userIdList, true, true, true, false);

        $lang = OW::getLanguage();
        $items = array();
        foreach ( $requests as $request )
        {
            $items[$request->id] = array(
                'userId' => $request->userId,
                'avatar' => $avatars[$request->userId],
                'viewed' => false,
                'string' => $lang->text(
                    'friends',
                    'console_request_item',
                    array('userUrl' => $avatars[$request->userId]['url'], 'displayName' => $avatars[$request->userId]['title'])
                )
            );
            $acceptCode='';
            $ignoreCode='';
            $frmSecuritymanagerEvent= OW::getEventManager()->trigger(new OW_Event('frmsecurityessentials.on.generate.request.manager',
                array('senderId'=>OW::getUser()->getId(),'receiverId'=> $request->userId,'isPermanent'=>true,'activityType'=>'accept_friends')));
            if(isset($frmSecuritymanagerEvent->getData()['code'])) {
                $acceptCode = (string)$frmSecuritymanagerEvent->getData()['code'];
            }
            $frmSecuritymanagerEvent= OW::getEventManager()->trigger(new OW_Event('frmsecurityessentials.on.generate.request.manager',
                array('senderId'=>OW::getUser()->getId(),'receiverId'=> $request->userId,'isPermanent'=>true,'activityType'=>'ignore_friends')));
            if(isset($frmSecuritymanagerEvent->getData()['code'])) {
                $ignoreCode =(string)$frmSecuritymanagerEvent->getData()['code'];
            }
            $items[$request->id]['acceptCode']=$acceptCode;
            $items[$request->id]['ignoreCode']=$ignoreCode;
        }

        return $items;
    }
}