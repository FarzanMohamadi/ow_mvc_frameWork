<?php
/**
 * @package ow_system_plugins.base.bol
 * @since 1.0
 */
class BOL_Plugin extends BOL_StoreItem
{
    /**
     * @var string
     */
    public $module;

    /**
     * @var boolean
     */
    public $isSystem;

    /**
     * @var boolean
     */
    public $isActive;

    /**
     * @var string
     */
    public $adminSettingsRoute;

    /**
     * @var string
     */
    public $uninstallRoute;

    /**
     * @return boolean
     */
    public function isActive()
    {
        return (bool) $this->isActive;
    }

    /**
     * @return boolean
     */
    public function isSystem()
    {
        return (bool) $this->isSystem;
    }

    /**
     * @return string
     */
    public function getModule()
    {
        return $this->module;
    }

    /**
     * @param boolean $isActive
     * @return BOL_Plugin
     */
    public function setIsActive( $isActive )
    {
        $this->isActive = (boolean) $isActive;

        return $this;
    }

    /**
     * @param string $module
     * @return BOL_Plugin
     */
    public function setModule( $module )
    {
        $this->module = trim($module);

        return $this;
    }

    /**
     * @param boolean $isSystem
     * @return BOL_Plugin
     */
    public function setIsSystem( $isSystem )
    {
        $this->isSystem = $isSystem;

        return $this;
    }

    /**
     * @return string
     */
    public function getAdminSettingsRoute()
    {
        return $this->adminSettingsRoute;
    }

    /**
     * @param string $adminSettingsRoute
     * @return BOL_Plugin
     */
    public function setAdminSettingsRoute( $adminSettingsRoute )
    {
        $this->adminSettingsRoute = $adminSettingsRoute;

        return $this;
    }

    /**
     * @return string
     */
    public function getUninstallRoute()
    {
        return $this->uninstallRoute;
    }

    /**
     * @param string $uninstallRoute
     * @return BOL_Plugin
     */
    public function setUninstallRoute( $uninstallRoute )
    {
        $this->uninstallRoute = $uninstallRoute;

        return $this;
    }
}
