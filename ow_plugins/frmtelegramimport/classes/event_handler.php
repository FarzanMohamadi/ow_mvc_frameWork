<?php
class FRMTELEGRAMIMPORT_CLASS_EventHandler{
    private static $classInstance;

    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    private function __construct()
    {
    }

    public function init(){
        if( !FRMSecurityProvider::checkPluginActive('groups', true) ){
            return;
        }
        if( !FRMSecurityProvider::checkPluginActive('frmgroupsplus', true) ){
            return;
        }
    }
}