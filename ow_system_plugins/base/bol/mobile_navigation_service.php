<?php
/**
 *
 * @package ow_system_plugins.base.bol
 * @since 1.0
 */
class BOL_MobileNavigationService
{
    const MENU_TYPE_TOP = BOL_NavigationService::MENU_TYPE_MOBILE_TOP;
    const MENU_TYPE_BOTTOM = BOL_NavigationService::MENU_TYPE_MOBILE_BOTTOM;
    const MENU_TYPE_HIDDEN = BOL_NavigationService::MENU_TYPE_MOBILE_HIDDEN;
    
    const LANG_PREFIX = "ow_custom";
    
    const MENU_PREFIX = self::LANG_PREFIX;
    
    const SETTING_TYPE = "type";
    const SETTING_URL = "url";
    const SETTING_LABEL = "label";
    const SETTING_TITLE = "title";
    const SETTING_CONTENT = "content";
    const SETTING_DESC = "meta_desc";
    const SETTING_KEYWORDS = "meta_keywords";
    const SETTING_VISIBLE_FOR = "visibleFor";
    
    /**
     * @var BOL_MobileNavigationService
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return BOL_MobileNavigationService
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
     *
     * @var BOL_NavigationService
     */
    private $navigationService;
    
    /**
     * Constructor.
     */
    private function __construct()
    {
        $this->navigationService = BOL_NavigationService::getInstance();
    }
    
    
    private function deleteLanguageKeyIfExists( $prefix, $key )
    {
        $languageService = BOL_LanguageService::getInstance();
        
        $keyDto = $languageService->findKey($prefix, $key);
        
        if ( $keyDto !== null )
        {
            $languageService->deleteKey($keyDto->id);
        }
    }
    
    /**
     * 
     * @param string $menu
     * @param int $order
     * @return BOL_MenuItem
     */
    public function createEmptyItem( $menu, $order )
    {
        $menuItem = new BOL_MenuItem();
        $documentKey = UTIL_HtmlTag::generateAutoId('mobile_page');
        
        $menuItem->setDocumentKey($documentKey);
        $menuItem->setPrefix(self::MENU_PREFIX);
        $menuItem->setKey($documentKey);

        $menuItem->setType($menu);
        $menuItem->setOrder($order);
        
        $this->navigationService->saveMenuItem($menuItem);
        
        $document = new BOL_Document();
        $document->isStatic = true;
        $document->isMobile = true;
        $document->setKey($menuItem->getKey());
        $document->setUri("cp-" . $document->getId());

        $this->navigationService->saveDocument($document);

        $this->editItem($menuItem, array(
            self::SETTING_LABEL => OW::getLanguage()->text("mobile", "admin_nav_default_menu_name"),
            self::SETTING_TITLE => OW::getLanguage()->text("mobile", "admin_nav_default_page_title"),
            self::SETTING_CONTENT => OW::getLanguage()->text("mobile", "admin_nav_default_page_content"),
            self::SETTING_DESC =>  OW::getLanguage()->text("mobile", 'page_default_description'),
            self::SETTING_KEYWORDS => OW::getLanguage()->text("nav", 'page_default_keywords'),
            self::SETTING_VISIBLE_FOR => 3,
            self::SETTING_TYPE => "local",
            self::SETTING_URL => null
        ));
        
        return $menuItem;
    }
    
    public function deleteItem( BOL_MenuItem $item )
    {
        $document = $this->navigationService->findDocumentByKey($item->getDocumentKey());
        $this->navigationService->deleteDocument($document);
        $this->navigationService->deleteMenuItem($item);

        $this->deleteLanguageKeyIfExists($item->getPrefix(), $item->getKey());
        $this->deleteLanguageKeyIfExists(self::LANG_PREFIX, $item->getKey() . "_title");
        $this->deleteLanguageKeyIfExists(self::LANG_PREFIX, $item->getKey() . "_content");
    }
    
    public function editItem( BOL_MenuItem $item, $settings )
    {

        $languageService = BOL_LanguageService::getInstance();
        $currentLanguageId = $languageService->getCurrent()->getId();
        $documentId = explode(":", $settings['key'])[1];

        // Menu Item Name
        if ( isset($settings[self::SETTING_LABEL]) )
        {
            $languageService->addOrUpdateValue($currentLanguageId, $item->prefix, $item->key, $settings[self::SETTING_LABEL], false);
        }

        // Page Title
        if ( isset($settings[self::SETTING_TITLE]) )
        {
            $languageService->addOrUpdateValue($currentLanguageId, $item->prefix, $item->key . "_title", $settings[self::SETTING_TITLE], false);
        }

        if ( isset($settings[self::SETTING_VISIBLE_FOR]) )
        {
            $item->visibleFor = is_array($settings[self::SETTING_VISIBLE_FOR]) ? array_sum($settings[self::SETTING_VISIBLE_FOR]) : (int) $settings[self::SETTING_VISIBLE_FOR];
        }
        
        if ( isset($settings[self::SETTING_TYPE]) && $settings[self::SETTING_TYPE] == "local" )
        {
            if ( !empty($settings[self::SETTING_URL]) )
            {
                $localURL = preg_replace("/[^A-Za-z0-9_\-]/", '', $settings[self::SETTING_URL]);
                $document = $this->navigationService->findDocumentByKey($documentId);
                if(isset($document)) {
                    $document->setUri($localURL);
                    $this->navigationService->saveDocument($document);
                }
            }
            $settings[self::SETTING_URL] = null;
            $item->externalUrl = null;

            // Page Meta Keywords
            if ( isset($settings[self::SETTING_DESC]) )
            {
                $content = $settings[self::SETTING_DESC];
                $languageService->addOrUpdateValue($currentLanguageId, $item->prefix, $item->key . "_desc", $content, false);
            }

            // Page Meta Keywords
            if ( isset($settings[self::SETTING_KEYWORDS]) )
            {
                $content = $settings[self::SETTING_KEYWORDS];
                $languageService->addOrUpdateValue($currentLanguageId, $item->prefix, $item->key . "_keywords", $content, false);
            }

            // Page Content
            if ( isset($settings[self::SETTING_CONTENT]) )
            {
                $content = $settings[self::SETTING_CONTENT];
                $languageService->addOrUpdateValue($currentLanguageId, $item->prefix, $item->key . "_content", $content, false);
            }
        }
        else{
            if ( isset($settings[self::SETTING_URL]) )
            {
                $item->externalUrl = $settings[self::SETTING_URL];
            }
        }

        $this->navigationService->saveMenuItem($item);
        $languageService->generateCache($currentLanguageId);
    }
    
    public function getItemSettings( BOL_MenuItem $item )
    {
        $language = OW::getLanguage();
        
        return array(
            self::SETTING_LABEL => $language->text($item->prefix, $item->key),
            self::SETTING_TITLE => $language->text($item->prefix, $item->key . "_title"),
            self::SETTING_CONTENT => $language->text($item->prefix, $item->key . "_content"),
            self::SETTING_DESC => $language->text($item->prefix, $item->key . "_desc"),
            self::SETTING_KEYWORDS => $language->text($item->prefix, $item->key . "_keywords"),
            self::SETTING_VISIBLE_FOR => (int) $item->visibleFor,
            self::SETTING_TYPE => empty($item->externalUrl) ? "local" : "external",
            self::SETTING_URL => $item->externalUrl
        );
    }
    
    public function getItemSettingsByPrefixAndKey( $prefix, $key )
    {
        $item = $this->navigationService->findMenuItem($prefix, $key);
        
        if ( $item === null ) 
        {
            return array();
        }
        
        return $this->getItemSettings($item);
    }
}