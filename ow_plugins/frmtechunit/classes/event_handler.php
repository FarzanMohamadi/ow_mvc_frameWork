<?php
/**
 * Created by PhpStorm.
 * User: Seyed Ismail Mirvakili
 * Date: 7/2/18
 * Time: 10:24 AM
 */

class FRMTECHUNIT_CLASS_EventHandler
{
    /**
     * Singleton instance.
     *
     * @var FRMTECHUNIT_CLASS_EventHandler
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return FRMTECHUNIT_CLASS_EventHandler
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    public function init(){
        OW::getEventManager()->bind('admin.add_auth_labels', array($this, 'onCollectAuthLabels'));
    }

    public function initMobile(){
        OW::getEventManager()->bind('admin.add_auth_labels', array($this, 'onCollectAuthLabels'));
    }

    public function onCollectAuthLabels( BASE_CLASS_EventCollector $event )
    {
        $language = OW::getLanguage();
        $event->add(
            array(
                'frmtechunit' => array(
                    'label' => $language->text('frmtechunit', 'main_menu_item'),
                    'actions' => array(
                        'add' => $language->text('frmtechunit', 'auth_action_label_add'),
                        'view' => $language->text('frmtechunit', 'auth_action_label_view'),
                    )
                )
            )
        );
    }
}