<?php
/**
 * The class is responsible for plugin management.
 * 
 * @package ow.ow_core
 * @method static OW_PluginManager getInstance()
 * @since 1.0
 */
class OW_PluginManager
{
    use OW_Singleton;
    
    /**
     * @var BOL_PluginService
     */
    private $pluginService;

    /**
     * @var array
     */
    private $cachedObjects = array();

    /**
     * Constructor.
     */
    private function __construct()
    {
        $this->pluginService = BOL_PluginService::getInstance();
    }

    /**
     * Returns active plugin object.
     *
     * @param string $key
     * @return OW_Plugin
     */
    public function getPlugin( $key )
    {
        $plugin = $this->pluginService->findPluginByKey($key);

        if ( $plugin === null)
        {
            throw new InvalidArgumentException("There is no installed plugin with key `{$key}`!");
        }
        if (!$plugin->isActive() )
        {
            throw new InvalidArgumentException("There is no active plugin with key `{$key}`!");
        }

        if ( !array_key_exists($plugin->getKey(), $this->cachedObjects) )
        {
            $this->cachedObjects[$plugin->getKey()] = new OW_Plugin($plugin);
        }

        return $this->cachedObjects[$plugin->getKey()];
    }

    /**
     * Includes init script for all active plugins
     */
    public function initPlugins()
    {
        $plugins = $this->pluginService->findActivePlugins();

        usort($plugins,
            function( BOL_Plugin $a, BOL_Plugin $b )
        {
            if ( $a->getId() == $b->getId() )
            {
                return 0;
            }

            return ($a->getId() > $b->getId()) ? 1 : -1;
        });

        /* @var $value BOL_Plugin */
        foreach ( $plugins as $plugin )
        {
            if ( !array_key_exists($plugin->getKey(), $this->cachedObjects) )
            {
                $this->cachedObjects[$plugin->getKey()] = new OW_Plugin($plugin);
            }

            $this->initPlugin($this->cachedObjects[$plugin->getKey()]);
        }
    }

    /**
     * Includes init script for provided plugin
     */
    public function initPlugin( OW_Plugin $pluginObject )
    {
        $this->addPackagePointers($pluginObject->getDto());

        $initDirPath = $pluginObject->getRootDir();

        if ( OW::getApplication()->getContext() == OW::CONTEXT_MOBILE )
        {
            $initDirPath = $pluginObject->getMobileDir();
        }

        OW::getEventManager()->trigger(new OW_Event("core.performance_test",
            array("key" => "plugin_init.start", "pluginKey" => $pluginObject->getKey())));

        $this->pluginService->includeScript($initDirPath . BOL_PluginService::SCRIPT_INIT);

        OW::getEventManager()->trigger(new OW_Event("core.performance_test",
            array("key" => "plugin_init.end", "pluginKey" => $pluginObject->getKey())));
    }

    /**
     * Adds platform predefined package pointers
     * 
     * @param BOL_Plugin $pluginDto
     */
    public function addPackagePointers( BOL_Plugin $pluginDto )
    {
        $plugin = new OW_Plugin($pluginDto);
        $upperedKey = mb_strtoupper($plugin->getKey());
        $autoloader = OW::getAutoloader();

        $predefinedPointers = array(
            "CMP" => $plugin->getCmpDir(),
            "CTRL" => $plugin->getCtrlDir(),
            "BOL" => $plugin->getBolDir(),
            "CLASS" => $plugin->getClassesDir(),
            "MCMP" => $plugin->getMobileCmpDir(),
            "MCTRL" => $plugin->getMobileCtrlDir(),
            "MBOL" => $plugin->getMobileBolDir(),
            "MCLASS" => $plugin->getMobileClassesDir()
        );

        foreach ( $predefinedPointers as $pointer => $dirPath )
        {
            $autoloader->addPackagePointer($upperedKey . "_" . $pointer, $dirPath);
        }
    }

    /**
     * Update active plugins list for manager.
     * 
     * @deprecated since version 1.7.4
     */
    public function readPluginsList()
    {
        
    }

    /**
     * Returns plugin key for provided module name, works only for active plugins
     *
     * @param string $moduleName
     * @return string
     * @throws InvalidArgumentException
     */
    public function getPluginKey( $moduleName )
    {
        // special case of admin plugin
        if (defined('OW_ADMIN_PREFIX')) {
            if ($moduleName === OW_ADMIN_PREFIX) {
                return 'admin';
            } else if ($moduleName === 'admin') {
                throw new InvalidArgumentException("There is no plugin with module name `{$moduleName}`!");
            }
        }

        $plugins = $this->pluginService->findActivePlugins();

        /* @var $plugin BOL_Plugin */
        foreach ( $plugins as $plugin )
        {
            if ( $plugin->getModule() == $moduleName )
            {
                return $plugin->getKey();
            }
        }

        throw new InvalidArgumentException("There is no plugin with module name `{$moduleName}`!");
    }

    /**
     * Returns module name for provided plugin key
     *
     * @param string $pluginKey
     * @return string
     * @throws InvalidArgumentException
     */
    public function getModuleName( $pluginKey )
    {
        $plugin = $this->pluginService->findPluginByKey($pluginKey);

        if ( $plugin == null )
        {
            throw new InvalidArgumentException("There is no active plugin with key `{$pluginKey}`");
        }

        return $plugin->getModule();
    }

    /**
     * Checks if plugin is active
     *
     * @param string $pluginKey
     * @return boolean
     */
    public function isPluginActive( $pluginKey )
    {
        $plugin = $this->pluginService->findPluginByKey($pluginKey);

        return $plugin !== null && $plugin->isActive();
    }

    /**
     * Sets admin settings page route name for provided plugin
     *
     * @param string $pluginKey
     * @param string $routeName
     */
    public function addPluginSettingsRouteName( $pluginKey, $routeName )
    {
        $plugin = $this->pluginService->findPluginByKey(trim($pluginKey));

        if ( $plugin !== null )
        {
            $plugin->setAdminSettingsRoute($routeName);
            $this->pluginService->savePlugin($plugin);
        }
    }

    /**
     * Sets uninstall page route name for provided plugin
     *
     * @param string $key
     * @param string $routName
     */
    public function addUninstallRouteName( $key, $routName )
    {
        $plugin = $this->pluginService->findPluginByKey(trim($key));

        if ( $plugin !== null )
        {
            $plugin->setUninstallRoute($routName);
            $this->pluginService->savePlugin($plugin);
        }
    }
}
