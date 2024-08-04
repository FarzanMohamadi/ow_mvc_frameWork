<?php
/**
 * 
 * All rights reserved.
 */

/**
 *
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmgraph.classes
 * @since 1.0
 */
class FRMGRAPH_CLASS_EventHandler
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

    public function addGraphConsoleItem( BASE_CLASS_EventCollector $event )
    {
        $service = FRMGRAPH_BOL_Service::getInstance();
        if($service->checkUserPermission() && OW::getUser()->isAuthenticated()) {
            $event->add(array('label' => OW::getLanguage()->text('frmgraph', 'graph_index'), 'url' => OW_Router::getInstance()->urlForRoute('frmgraph.graph')));
        }
    }

    public function init()
    {
        OW::getEventManager()->bind('base.add_main_console_item', array($this, 'addGraphConsoleItem'));
        OW::getEventManager()->bind('admin.add_auth_labels', array($this, "onCollectAuthLabels"));
    }

    public function onCollectAuthLabels( BASE_CLASS_EventCollector $event )
    {
        $language = OW::getLanguage();
        $event->add(
            array(
                'frmgraph' => array(
                    'label' => $language->text('frmgraph', 'auth_frmgraph_label'),
                    'actions' => array(
                        'graphshow' => $language->text('frmgraph', 'auth_action_label_graph')
                    )
                )
            )
        );
    }


}