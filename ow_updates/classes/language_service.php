<?php
class UPDATE_LanguageService
{
    private static $classInstance;

    private $service;

    /**
     *
     * @param <type> $includeCache
     * @return UPDATE_LanguageService
     */
    public static function getInstance()
    {
        if (!isset(self::$classInstance)) {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    /***
     * @deprecated
     * @param $path
     * @param $key
     * @param bool $updateValues
     */
    public function importPrefixFromZip($path, $key, $updateValues = true)
    {
        if ( OW::getStorage()->fileExists($path) )
        {
            $this->service->importPrefixFromZip($path, $key, false, false, $updateValues);
        }
        else if ( OW::getStorage()->fileExists(OW::getPluginManager()->getPlugin($key)->getRootDir() . 'langs') )
        {
            Updater::getLanguageService()->updatePrefixForPlugin($key);
        }
    }

    public function replaceLangValue($prefix, $key, $value, $generateCache = false, $lang = 'en')
    {
        $this->service->replaceLangValue($prefix, $key, $value, $lang, $generateCache);
    }

    public function importPrefixFromDir($path, $updateValues = true)
    {
        $this->service->importPrefixFromDir($path, false, false, $updateValues);
    }

    public function updatePrefixForPlugin($pluginKey, $updateValues = true)
    {
        $this->service->updatePrefixForPlugin($pluginKey, $updateValues);
    }

    public function getCurrent()
    {
        return $this->service->getCurrent();
    }
    
    public function getLanguages()
    {
        return $this->service->getLanguages();
    }
    
    public function findKey($prefix, $key)
    {
        return $this->service->findKey($prefix, $key);
    }
    
    public function findPrefix($prefix)
    {
        return $this->service->findPrefix($prefix);
    }
    
    public function addKey( $prefixId, $key )
    {
        return $this->service->addKey($prefixId, $key);
    }
    
    public function findValue( $languageId, $keyId )
    {
        return $this->service->findValue($languageId, $keyId);
    }
    
    public function addOrUpdateValue( $languageId, $prefix, $key, $value )
    {
        return $this->service->addOrUpdateValue($languageId, $prefix, $key, $value, false);
    }
    
    public function deleteLangKey($prefix, $key)
    {
        $langKey = $this->service->findKey($prefix, $key);

        if ( !empty($langKey) )
        {
            $this->service->deleteKey($langKey->id, false);
        }
    }
    
    public function addValue( $prefix, $key, $value )
    {
        $languages = $this->service->getLanguages();
        
        if ( !empty($languages) )
        {
            foreach ( $languages as $language )
            {
                /* @var $language BOL_Language */
                if ( $language->tag == 'en' )
                {
                    $this->service->addValue($language->id, $prefix, $key, $value, false);
                    return;
                }
            }
        }
    }
    
    public function findPrefixId( $prefix )
    {
        return $this->service->findPrefixId($prefix);
    }

    private function __construct()
    {
        $this->service = BOL_LanguageService::getInstance();
    }

    public function addOrUpdateValueByLanguageTag( $languageTag, $prefix, $key, $value )
    {
        $languages = $this->getLanguages();
        $languageId = null;
        foreach ($languages as $lang) {
            if ($lang->tag == $languageTag) {
                $languageId = $lang->id;
            }
        }
        if ($languageId != null) {
            return $this->service->addOrUpdateValue($languageId, $prefix, $key, $value, false);
        }
        return null;
    }
}
