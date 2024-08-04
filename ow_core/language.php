<?php

/**
 * Base language class.
 *
 * @package ow_core
 * @method static OW_Language getInstance()
 * @since 1.0
 */
class OW_Language
{
    use OW_Singleton;
    
    /**
     * @var OW_EventManager
     */
    private $eventManager;

    /**
     * Constructor.
     *
     */
    private function __construct()
    {
        $this->eventManager = OW::getEventManager();
    }    

    public function text( $prefix, $key, array $vars = null, $defaultValue = null )
    {
        $text = null;
        if ( !empty($prefix) && !empty($key) )
        {
            try
            {
                $text = BOL_LanguageService::getInstance()->getText(BOL_LanguageService::getInstance()->getCurrent()->getId(), $prefix, $key, $vars);
            }
            catch ( Exception $e )
            {
            }
        }

        if ( $text === null )
        {
            if(!isset($defaultValue) && strpos($prefix.'+'.$key, 'base+questions_question_') === false){
                OW::getLogger()->writeLog(OW_Log::WARNING, 'translation_not_found',
                    ['key'=>$prefix . '+' . $key, 'lang'=>BOL_LanguageService::getInstance()->getCurrent()->getTag()]);
            }
            return $defaultValue === null ? $prefix . '+' . $key : $defaultValue;
        }

        return $text;
    }

    public function valueExist( $prefix, $key )
    {
        if ( empty($prefix) || empty($key) )
        {
            throw new InvalidArgumentException('Invalid parameter $prefix or $key');
        }

        try
        {
            $text = BOL_LanguageService::getInstance()->getText(BOL_LanguageService::getInstance()->getCurrent()->getId(), $prefix, $key);
        }
        catch ( Exception $e )
        {
            return false;
        }

        if ( $text === null )
        {
            return false;
        }

        return true;
    }

    public function addKeyForJs( $prefix, $key )
    {
        $text = json_encode($this->text($prefix, $key));

        OW::getDocument()->addOnloadScript("OW.registerLanguageKey('$prefix', '$key', $text);", -99);
    }

    public function getCurrentId()
    {
        return BOL_LanguageService::getInstance()->getCurrent()->getId();
    }

    /***
     * @deprecated
     * @param $path
     * @param $key
     * @param bool $refreshCache
     * @param bool $addLanguage
     */
    public function importPluginLangs( $path, $key, $refreshCache = false, $addLanguage = false )
    {
        BOL_LanguageService::getInstance()->importPrefixFromZip($path, $key, $refreshCache, $addLanguage);
    }

    /***
     * @deprecated
     * @param $path
     * @param bool $refreshCache
     * @param bool $addLanguage
     */
    public function importLangsFromZip( $path, $refreshCache = false, $addLanguage = false )
    {
        BOL_LanguageService::getInstance()->importPrefixFromZip($path, FRMSecurityProvider::generateUniqueId(), $refreshCache, $addLanguage);
    }

    public function importLangsFromDir( $path, $refreshCache = false, $addLanguage = false )
    {
        BOL_LanguageService::getInstance()->importPrefixFromDir($path, $refreshCache, $addLanguage);
    }
}
