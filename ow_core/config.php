<?php
/**
 * The class works with config system.
 * 
 * @package ow_core
 * @method static OW_Config getInstance()
 * @since 1.0
 */
class OW_Config
{
    use OW_Singleton;
    
    /**
     * @var BOL_ConfigService
     */
    private $configService;
    /**
     * @var array
     */
    private $cachedConfigs;

    /**
     * Constructor.
     */
    private function __construct()
    {
        $this->configService = BOL_ConfigService::getInstance();

        $this->generateCache();
    }

    public static function removeInstance(){
        self::$instance = null;
    }

    public function generateCache()
    {
        try {
            $configs = $this->configService->findAllConfigs();
        }
        catch (Exception $ex){
            return;
        }

        $this->cachedConfigs = array();
        
        /* @var $config BOL_Config */
        foreach ( $configs as $config )
        {
            if ( !isset($this->cachedConfigs[$config->getKey()]) )
            {
                $this->cachedConfigs[$config->getKey()] = array();
            }

            $this->cachedConfigs[$config->getKey()][$config->getName()] = $config->getValue();
        }
    }

    /**
     * Returns config value for provided plugin key and config name.
     * 
     * @param string $key
     * @param string $name
     * @param $default
     * @return string|null
     */
    public function getValue( $key, $name, $default=null )
    {
        return ( isset($this->cachedConfigs[$key][$name]) ) ? $this->cachedConfigs[$key][$name] : $default;
    }

    /**
     * Returns all config values for plugin key.
     * 
     * @param string $key
     * @return array
     */
    public function getValues( $key )
    {
        return ( isset($this->cachedConfigs[$key]) ) ? $this->cachedConfigs[$key] : array();
    }

    /**
     * Adds plugin config.
     *
     * @deprecated Use saveConfig instead for add and update
     * @param string $key
     * @param string $name
     * @param mixed $value
     * @param $description
     * @param $codeChange
     */
    public function addConfig( $key, $name, $value, $description = null, $codeChange = true)
    {
        $this->saveConfig($key, $name, $value, $description, $codeChange);
    }

    /**
     * Deletes config by provided plugin key and config name.
     * 
     * @param string $key
     * @param string $name
     */
    public function deleteConfig( $key, $name )
    {
        $this->configService->removeConfig($key, $name);
        OW::getEventManager()->trigger(new OW_Event('base.code.change', array('config_reset' => true)));
    }

    /**
     * Removes all plugin configs.
     * 
     * @param string $key
     */
    public function deletePluginConfigs( $key )
    {
        $this->configService->removePluginConfigs($key);
    }

    /**
     * Checks if config exists.
     *
     * @param string $key
     * @param string $name
     * @return boolean
     */
    public function configExists( $key, $name )
    {
        return array_key_exists($key, $this->cachedConfigs) && array_key_exists($name, $this->cachedConfigs[$key]);
    }

    /**
     * Updates config value.
     * 
     * @param string $key
     * @param string $name
     * @param mixed $value
     * @param $description
     * @param $codeChange
     */
    public function saveConfig( $key, $name, $value, $description=null, $codeChange = true)
    {
        $this->configService->saveConfig($key, $name, $value, $description);
        if ($codeChange) {
            OW::getEventManager()->trigger(new OW_Event('base.code.change', array('config_reset' => true)));
        }
    }
}