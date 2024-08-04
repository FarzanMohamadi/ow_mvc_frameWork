<?php
class FRMJCSE_CLASS_EventHandler
{
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return FRMJCSE_CLASS_EventHandler
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }
    public function genericInit()
    {
        $service = FRMJCSE_BOL_Service::getInstance();

        OW::getEventManager()->bind('admin.add_auth_labels', array($service, 'onCollectAuthLabels'));
    }
}