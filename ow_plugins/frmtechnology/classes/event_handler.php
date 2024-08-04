<?php
/**
 * Created by PhpStorm.
 * User: Elahe
 * Date: 6/24/2018
 * Time: 1:22 PM
 */

class FRMTECHNOLOGY_CLASS_EventHandler
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

    public function addConsoleItem(BASE_CLASS_EventCollector $event)
    {
        if (OW::getUser()->isAuthorized('frmtechnology', 'manage-technology')) {
            $event->add(array('label' => OW::getLanguage()->text('frmtechnology', 'admin_technology_deactivates_page'), 'url' => OW_Router::getInstance()->urlForRoute('frmtechnology.view-list',array('listType' => 'deactivate'))));
        }

    }
    public function onCollectAuthLabels( BASE_CLASS_EventCollector $event )
    {
        $language = OW::getLanguage();
        $event->add(
            array(
                'frmtechnology' => array(
                    'label' => $language->text('frmtechnology', 'auth_frmtechnology_label'),
                    'actions' => array(
//                        'add_technology' => $language->text('frmtechnology', 'auth_action_label_add_technology'),
//                        'view_technology' => $language->text('frmtechnology', 'auth_action_label_view_technology'),
//                        'view_order' => $language->text('frmtechnology', 'auth_action_label_view_order')
                        'manage-technology' => $language->text('frmtechnology', 'auth_action_label_admin_technology')
                    )
                )
            )
        );
    }

    public function init()
    {
        $eventManager = OW::getEventManager();
        $eventManager->bind('base.add_main_console_item', array($this, 'addConsoleItem'));
        $eventManager->bind('admin.add_auth_labels', array($this, "onCollectAuthLabels"));
        $eventManager->bind('notifications.collect_actions', array($this, 'onNotifyActions'));
        $eventManager->bind(OW_EventManager::ON_BEFORE_DOCUMENT_RENDER, array($this, 'loadStaticFiles'));
    }

    public function onNotifyActions( BASE_CLASS_EventCollector $e )
    {
        $e->add(array(
            'section' => 'frmtechnology',
            'action' => 'manage-technology',
            'description' => OW::getLanguage()->text('frmtechnology', 'email_notifications_setting_comment'),
            'sectionIcon' => 'ow_ic_technology',
            'sectionLabel' => OW::getLanguage()->text('frmtechnology', 'email_notifications_section_label'),
            'selected' => true
        ));
    }
    public function loadStaticFiles(){
        OW::getDocument()->addStyleSheet(OW::getPluginManager()->getPlugin('frmtechnology')->getStaticCssUrl() . 'frmtechnology.css');
        $path = $_SERVER['REQUEST_URI'];
        if(preg_match('#^(/(index(/){0,1}){0,1}){0,1}$#', $path, $matches))
        {
            $mainPageCssFile = OW::getPluginManager()->getPlugin('frmtechnology')->getStaticCssUrl() . 'technology_mainpage.css';
            OW::getDocument()->addStyleSheet($mainPageCssFile);
        }
    }
}