<?php
/**
 * Console invitations section items component
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow.ow_system_plugins.base.mobile.components
 * @since 1.6.0
 */
class BASE_MCMP_ConsoleInvitations extends OW_MobileComponent
{
    /**
     * Constructor.
     */
    public function __construct(  $limit, $exclude = null )
    {
        parent::__construct();

        $service = BOL_InvitationService::getInstance();
        $userId = OW::getUser()->getId();
        $invitations = $service->findInvitationList($userId, time(), $exclude, $limit);
        $items = $this->prepareData($invitations);
        $this->assign('items', $items);

        $invitationIdList = array();
        foreach ( $items as $id => $item )
        {
            $invitationIdList[] = $id;
        }

        // Mark as viewed
        $service->markViewedByUserId($userId);

        $exclude = is_array($exclude) ? array_merge($exclude, $invitationIdList) : $invitationIdList;
        $loadMore = (bool) $service->findInvitationCount($userId, null, $exclude);
        if ( !$loadMore )
        {
            $script = "OWM.trigger('mobile.console_hide_invitations_load_more', {});";
            OW::getDocument()->addOnloadScript($script);
        }
    }

    public static function prepareData( $invitations )
    {
        $avatars = array();
        $router = OW::getRouter();
        foreach ( $invitations as $invitation )
        {
            $data = json_decode($invitation->data, true);
            $avatars[$invitation->id] = array(
                'src' => $data['avatar']['src'],
                'title' => $data['avatar']['title'],
                'url' => isset($data['avatar']['urlInfo']) ?
                    $router->urlForRoute($data['avatar']['urlInfo']['routeName'], $data['avatar']['urlInfo']['vars']) : null
            );
        }

        $items = array();

        foreach ( $invitations as $invitation )
        {
            // backward compatibility: row will be not clickable
            $disabled = true;

            /** @var $invitation BOL_Invitation  */
            $item=BASE_CLASS_InvitationEventHandler::getInstance()->getEditedData($invitation->pluginKey,$invitation->entityId,$invitation->entityType, $invitation->getData());
            $itemEvent = new OW_Event('mobile.invitations.on_item_render', array(
                'entityType' => $invitation->entityType,
                'entityId' => $invitation->entityId,
                'pluginKey' => $invitation->pluginKey,
                'userId' => $invitation->userId,
                'data' => $item
            ));

            OW::getEventManager()->trigger($itemEvent);
            $eData = $itemEvent->getData();

            if ( $eData )
            {
                if ( !empty($eData) )
                {
                    $item = $eData;
                    $disabled = false;
                }
            }

            $item['avatar'] = $avatars[$invitation->id];
            $item['entityId'] = $invitation->entityId;

            if ( !empty($item['string']) && is_array($item['string']) )
            {
                $key = explode('+', $item['string']['key']);
                $vars = empty($item['string']['vars']) ? array() : $item['string']['vars'];
                $item['string'] = OW::getLanguage()->text($key[0], $key[1], $vars);
                if ( $disabled )
                {
                    $item['string'] = strip_tags($item['string']);
                }
            }

            if ( !empty($item['contentImage']) )
            {
                $item['contentImage'] = is_string($item['contentImage'])
                    ? array( 'src' => $item['contentImage'] )
                    : $item['contentImage'];
            }
            else
            {
                $item['contentImage'] = null;
            }
            if ( $item['contentImage'] != null )
            {
                if( $invitation->pluginKey == 'groups' && isset($invitation->entityId)
                    && OW::getPluginManager()->isPluginActive('groups')){
                    $group = GROUPS_BOL_Service::getInstance()->findGroupById($invitation->entityId);
                    $item['contentImage']['src'] = GROUPS_BOL_Service::getInstance()->getGroupImageUrl($group);
                }
                else if($invitation->pluginKey == 'event' && isset($invitation->entityId)
                    && OW::getPluginManager()->isPluginActive('event')){
                    $event = EVENT_BOL_EventService::getInstance()->findEvent($invitation->entityId);
                    $item['contentImage']['src'] = $event->getImage() ? EVENT_BOL_EventService::getInstance()->generateImageUrl($event->getImage(),true) : $item['contentImage']['src'];
                }
            }

            $item['viewed'] = (bool) $invitation->viewed;
            $item['disabled'] = $disabled;
            $items[$invitation->id] = $item;
        }

        return $items;
    }
}