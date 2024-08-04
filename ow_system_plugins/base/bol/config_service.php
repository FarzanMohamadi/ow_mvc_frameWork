<?php
/**
 * Config service.
 * 
 * @package ow_system_plugins.base.bol
 * @since 1.0
 */
final class BOL_ConfigService
{
    const EVENT_BEFORE_SAVE = "base.before_config_save";
    const EVENT_AFTER_SAVE = "base.after_config_save";
    
    const EVENT_BEFORE_REMOVE = "base.before_config_remove";
    const EVENT_AFTER_REMOVE = "base.after_config_remove";

    const CONFIG_FILE_PATH = OW_DIR_LOG . 'configs.json';
    
    /**
     * @var BOL_ConfigDao
     */
    private $configDao;
    /**
     * @var BOL_ConfigService
     */
    private static $classInstance;

    /**
     * @var boolean
     */
    private $fileMode;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return BOL_ConfigService
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    /**
     * Constructor.
     */
    private function __construct()
    {
        $this->fileMode = (!defined('CONFIG_MODE') || CONFIG_MODE=='file');
        $this->configDao = BOL_ConfigDao::getInstance();
    }

    /********************************  PART 1: Query **************************/



    /**
     * Returns all configs.
     *
     * @return array<BOL_Config>
     */
    public function findAllConfigs()
    {
        if ($this->fileMode) {
            if (file_exists(self::CONFIG_FILE_PATH)) {
                // use the file only if created in the last 10 minutes
                if (filectime(self::CONFIG_FILE_PATH) > time()-60*10) {
                    $resp = file_get_contents(self::CONFIG_FILE_PATH);
                    $resp = json_decode($resp);
                    foreach ($resp as $k => $v) {
                        $resp[$k] = new BOL_Config($resp[$k]);
                    }
                    return $resp;
                }
            }
            $resp = $this->configDao->findAll();
            @file_put_contents(self::CONFIG_FILE_PATH, json_encode($resp));
            if(!file_exists(self::CONFIG_FILE_PATH)){
                @unlink(self::CONFIG_FILE_PATH);
                $this->fileMode = false;
            }
            return $resp;
        }

        return $this->configDao->findAll();
    }


    /********************************  PART 2: UPDATE **************************/
    /**
     * Adds new config item.
     *
     * @deprecated
     * @param string $key
     * @param string $name
     * @param mixed $value
     */
    public function addConfig( $key, $name, $value, $description = null )
    {
        $this->saveConfig($key, $name, $value, $description);
    }

    /**
     * Updates config item value.
     * 
     * @param string $key
     * @param string $name
     * @param mixed $value
     * @param $description
     * @throws InvalidArgumentException
     */
    public function saveConfig( $key, $name, $value, $description=null )
    {
        $config = $this->configDao->findConfig($key, $name);

        if ($config === null) {
            $config = new BOL_Config();
            $config->setKey($key)->setName($name)->setValue($value)->setDescription($description);
            $oldValue = null;
        } else {
            if(isset($description)){
                $config->setDescription($description);
            }
            $oldValue = $config->getValue();
        }

        // save config
        $event = OW::getEventManager()->trigger(new OW_Event(self::EVENT_BEFORE_SAVE, array(
            "key" => $key,
            "name" => $name,
            "value" => $value,
            "oldValue" => $oldValue
        ), $value));

        $config->setValue($event->getData());
        $this->configDao->save($config);
        $this->clearCache();

        OW::getEventManager()->trigger(new OW_Event(self::EVENT_AFTER_SAVE, array(
            "key" => $key,
            "name" => $name,
            "value" => $value,
            "oldValue" => $oldValue
        )));

        OW::getLogger()->writeLog(OW_Log::INFO, 'config_updated', array(
            "key" => $key,
            "name" => $name,
            "value" => $value
        ));
    }

    /***
     * removes config cache file if exists
     */
    private function clearCache(){
        if ($this->fileMode && file_exists(self::CONFIG_FILE_PATH)) {
            @unlink(self::CONFIG_FILE_PATH);
        }

        OW_Config::removeInstance();
    }

    /**
     * Removes config item by provided plugin key and config name.
     * 
     * @param string $key
     * @param string $name
     */
    public function removeConfig( $key, $name )
    {
        $event = OW::getEventManager()->trigger(new OW_Event(self::EVENT_BEFORE_REMOVE, array(
            "key" => $key,
            "name" => $name
        )));
        
        if ( $event->getData() !== false )
        {
            $this->configDao->removeConfig($key, $name);
            $this->clearCache();
            
            OW::getEventManager()->trigger(new OW_Event(self::EVENT_BEFORE_REMOVE, array(
                "key" => $key,
                "name" => $name
            )));
        }
    }

    /**
     * Removes all plugin configs.
     * 
     * @param string $pluginKey
     */
    public function removePluginConfigs( $pluginKey )
    {
        $this->configDao->removeConfigs($pluginKey);
        $this->clearCache();
    }
}