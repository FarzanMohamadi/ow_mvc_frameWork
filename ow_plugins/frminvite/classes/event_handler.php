<?php
/**
 * 
 * All rights reserved.
 */

/**
 *
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frminvite.classes
 * @since 1.0
 */
class FRMINVITE_CLASS_EventHandler
{
    private static $classInstance;

    public static function getInstance()
    {
        if (self::$classInstance === null) {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }


    private function __construct()
    {
    }

    public function addConsoleItem( BASE_CLASS_EventCollector $event )
    {
        $service = FRMINVITE_BOL_Service::getInstance();
        if($service->checkUserPermission() && OW::getUser()->isAuthenticated()) {
            $event->add(array('label' => OW::getLanguage()->text('frminvite', 'invite_index'), 'url' => OW_Router::getInstance()->urlForRoute('invite_index')));
        }
    }

    public function init()
    {
        OW::getEventManager()->bind('base.add_main_console_item', array($this, 'addConsoleItem'));
        OW::getEventManager()->bind('admin.add_auth_labels', array($this, "onCollectAuthLabels"));
        $service = FRMINVITE_BOL_Service::getInstance();
        OW::getEventManager()->bind(FRMINVITE_BOL_Service::ON_SEND_INVITATION, array( $service, "onSendInvitation"));
    }

    public function onCollectAuthLabels( BASE_CLASS_EventCollector $event )
    {
        $language = OW::getLanguage();
        $event->add(
            array(
                'frminvite' => array(
                    'label' => $language->text('frminvite', 'auth_frminvite_label'),
                    'actions' => array(
                        'invite' => $language->text('frminvite', 'auth_action_label_invite')
                    )
                )
            )
        );
    }

}