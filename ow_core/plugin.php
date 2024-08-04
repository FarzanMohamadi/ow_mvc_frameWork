<?php
/**
 * Base plugin object.
 *
 * @package ow_core
 * @since 1.0
 */
class OW_Plugin
{
    /**
     * Plugin dir/module name.
     *
     * @var string
     */
    protected $dirName;

    /**
     * Plugin unique key.
     *
     * @var string
     */
    protected $key;

    /**
     * @var boolean
     */
    protected $active;

    /**
     * @var BOL_Plugin
     */
    protected $dto;

    /**
     * Constructor.
     *
     * @param array $params
     */
    public function __construct( BOL_Plugin $plugin )
    {
        $this->dirName = trim($plugin->getModule());
        $this->key = trim($plugin->getKey());
        $this->active = (bool) $plugin->isActive;
        $this->dto = $plugin;
    }

    /**
     * Returns plugin dir/module name.
     *
     * @return string
     */
    public function getDirName()
    {
        return $this->dirName;
    }

    /**
     * Returns plugin unique key.
     *
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * Checks if plugin is active.
     *
     * @return boolean
     */
    public function isActive()
    {
        return $this->active;
    }

    /**
     * Returns plugin data transfer object.
     *
     * @return BOL_Plugin
     */
    public function getDto()
    {
        return $this->dto;
    }

    public function getUserFilesDir()
    {
        return OW_DIR_PLUGIN_USERFILES . $this->getDirName() . DS;
    }

    public function getInnerUserFilesDir()
    {
        return $this->getRootDir() . "userfiles" . DS;
    }

    public function getUserFilesUrl()
    {
        return OW_URL_PLUGIN_USERFILES . $this->getDirName() . "/";
    }

    public function getPluginFilesDir()
    {
        return OW_DIR_PLUGINFILES . $this->getDirName() . DS;
    }

    public function getInnerPluginFilesDir()
    {
        return $this->getRootDir() . "pluginfiles" . DS;
    }

    public function getRootDir()
    {
        return ($this->dto->isSystem() ? OW_DIR_SYSTEM_PLUGIN : OW_DIR_PLUGIN) . $this->getDirName() . DS;
    }

    public function getMobileDir()
    {
        return $this->getRootDir() . "mobile" . DS;
    }

    public function getCmpDir()
    {
        return $this->getRootDir() . "components" . DS;
    }

    public function getMobileCmpDir()
    {
        return $this->getMobileDir() . "components" . DS;
    }

    public function getViewDir()
    {
        return $this->getRootDir() . "views" . DS;
    }

    public function getMobileViewDir()
    {
        return $this->getMobileDir() . "views" . DS;
    }

    public function getCmpViewDir()
    {
        return $this->getViewDir() . "components" . DS;
    }

    public function getMobileCmpViewDir()
    {
        return $this->getMobileViewDir() . "components" . DS;
    }

    public function getCtrlViewDir()
    {
        return $this->getViewDir() . "controllers" . DS;
    }

    public function getMobileCtrlViewDir()
    {
        return $this->getMobileViewDir() . "controllers" . DS;
    }

    public function getCtrlDir()
    {
        return $this->getRootDir() . "controllers" . DS;
    }

    public function getMobileCtrlDir()
    {
        return $this->getMobileDir() . "controllers" . DS;
    }

    public function getDecoratorDir()
    {
        return $this->getRootDir() . "decorators" . DS;
    }

    public function getMobileDecoratorDir()
    {
        return $this->getMobileDir() . "decorators" . DS;
    }

    public function getStaticDir()
    {
        return $this->getRootDir() . "static" . DS;
    }

    public function getPublicStaticDir()
    {
        return OW_DIR_STATIC_PLUGIN . $this->getModuleName() . DS;
    }

    public function getBolDir()
    {
        return $this->getRootDir() . "bol" . DS;
    }

    public function getMobileBolDir()
    {
        return $this->getMobileDir() . "bol" . DS;
    }

    public function getClassesDir()
    {
        return $this->getRootDir() . "classes" . DS;
    }

    public function getMobileClassesDir()
    {
        return $this->getMobileDir() . "classes" . DS;
    }

    public function getStaticJsDir()
    {
        return $this->getStaticDir() . "js" . DS;
    }

    public function getModuleName()
    {
        return $this->dirName;
    }

    public function getPluginRoutePath()
    {
        if ($this->getKey() === 'admin') {
            return defined('OW_ADMIN_PREFIX') ? OW_ADMIN_PREFIX : 'admin';
        }
        return $this->dirName;
    }

    public function getStaticUrl()
    {
        return OW_URL_STATIC_PLUGINS . $this->getModuleName() . "/";
    }

    public function getStaticJsUrl()
    {
        return $this->getStaticUrl() . "js/";
    }

    public function getStaticCssUrl()
    {
        return $this->getStaticUrl() . "css/";
    }
}
