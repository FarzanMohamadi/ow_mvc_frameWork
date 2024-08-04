<?php
 /**
 * @package ow_plugins.friends.components
 * @since 1.0
 */
class FRIENDS_CMP_ConsoleFriendRequests extends BASE_CMP_ConsoleDropdownList
{
    public function __construct()
    {
        parent::__construct( OW::getLanguage()->text('friends', 'console_requests_title'), 'friend_requests' );


        $this->addClass('ow_friend_request_list');
    }

    public function initJs()
    {
        parent::initJs();

        $jsUrl = OW::getPluginManager()->getPlugin('friends')->getStaticJsUrl() . 'friend_request.js';
        OW::getDocument()->addScript($jsUrl);

        $js = UTIL_JsGenerator::newInstance();
        $js->addScript('OW.FriendRequest = new OW_FriendRequest({$key}, {$params});', array(
            'key' => $this->getKey(),
            'params' => array(
                'rsp' => OW::getRouter()->urlFor('FRIENDS_CTRL_Action', 'ajax')
            )
        ));

        OW::getDocument()->addOnloadScript($js);
    }
}