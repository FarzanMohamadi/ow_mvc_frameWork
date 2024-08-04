<?php
/**
 * @package ow_system_plugins.admin.classes
 * @since 1.8.4
 */
class ADMIN_CLASS_SeoMetaForm extends Form
{
    /**
     * @var array
     */
    private $entities = array();

    /**
     * @var array
     */
    private $data;

    /**
     * @var array
     */
    private $seoData;

    /**
     * @var BOL_SeoService
     */
    private $seoService;

    /**
     * ADMIN_CLASS_SeoMetaForm constructor.
     * @param array $data
     */
    public function __construct( array $data )
    {
        parent::__construct("meta_form");
        $this->seoService = BOL_SeoService::getInstance();
        $this->data = $data;
        $this->seoData = $this->seoService->getMetaData();
        $language = OW::getLanguage();
        $langService = BOL_LanguageService::getInstance();
        $langId = $langService->getCurrent()->getId();

        $disabledItems = isset($this->seoData["disabledEntities"][current($this->data)["sectionKey"]]) ? $this->seoData["disabledEntities"][current($this->data)["sectionKey"]] : array();

        $prefixes = array();
        $prefixesIds = array();
        $keys = array();

        foreach( $this->data as $item ){
            list($prefix, $key) = explode("+",$item["langs"]["description"]);
            $prefixes[] = $prefix;
            $keys[] = $key;

            list($prefix, $key) = explode("+", $item["langs"]["title"]);
            $prefixes[] = $prefix;
            $keys[] = $key;

            list($prefix, $key) = explode("+",$item["langs"]["keywords"]);
            $prefixes[] = $prefix;
            $keys[] = $key;
        }

        $prefixes = array_unique($prefixes);

        $prefixesDto = BOL_LanguagePrefixDao::getInstance()->findByPrefixes($prefixes);
        $prefixes = array();
        foreach ( $prefixesDto as $prefixDto ) {
            $prefixesIds[] = $prefixDto->id;
            $prefixes[$prefixDto->id] = $prefixDto->prefix;
        }

        $keyIdsObjects = BOL_LanguageKeyDao::getInstance()->findKeyIds($prefixesIds, $keys);
        $keyIds = array();
        $cachedPrefixKeyId = array();
        foreach ($keyIdsObjects as $keyIdsObject) {
            $keyIds[] = $keyIdsObject->id;
            $cachedPrefixKeyId[$prefixes[$keyIdsObject->prefixId] . '-' . $keyIdsObject->key] = $keyIdsObject->id;
        }

        $langValuesObjects = BOL_LanguageValueDao::getInstance()->findValues(array($langId), $keyIds);
        $cachedLangValuesObjects = array();
        foreach ($langValuesObjects as $langValuesObject) {
            $cachedLangValuesObjects[$langValuesObject->languageId . '-' . $langValuesObject->keyId] = $langValuesObject;
        }

        foreach( $this->data as $item ){

            $title = new TextField("seo_title_{$item["entityKey"]}");
            list($prefix, $key) = explode("+", $item["langs"]["title"]);
            $keyId = null;
            $valDto = null;
            if (isset($cachedPrefixKeyId[$prefix . '-' . $key])) {
                $keyId = $cachedPrefixKeyId[$prefix . '-' . $key];
            }
            if ($keyId != null && isset($cachedLangValuesObjects[$langId . '-' . $keyId])) {
                $valDto = $cachedLangValuesObjects[$langId . '-' . $keyId];
            }

            $title->setValue($valDto ? $valDto->getValue() : $prefix ."+". $key);
            $title->setLabel($language->text("base", "seo_meta_form_element_title_label"));
            $title->setDescription($language->text("base", "seo_meta_form_element_title_desc"));
            $title->addValidator(new MetaInfoValidator());
            $this->addElement($title);

            $desc = new Textarea("seo_description_{$item["entityKey"]}");
            list($prefix, $key) = explode("+",$item["langs"]["description"]);
            $keyId = null;
            $valDto = null;
            if (isset($cachedPrefixKeyId[$prefix . '-' . $key])) {
                $keyId = $cachedPrefixKeyId[$prefix . '-' . $key];
            }
            if ($keyId != null && isset($cachedLangValuesObjects[$langId . '-' . $keyId])) {
                $valDto = $cachedLangValuesObjects[$langId . '-' . $keyId];
            }

            $desc->setValue($valDto ? $valDto->getValue() : $prefix ."+". $key);
            $desc->setLabel($language->text("base", "seo_meta_form_element_desc_label"));
            $desc->setDescription($language->text("base", "seo_meta_form_element_desc_desc"));
            $desc->addValidator(new MetaInfoValidator());
            $this->addElement($desc);

            $keywords = new Textarea("seo_keywords_{$item["entityKey"]}");
            list($prefix, $key) = explode("+",$item["langs"]["keywords"]);

            $keyId = null;
            $valDto = null;
            if (isset($cachedPrefixKeyId[$prefix . '-' . $key])) {
                $keyId = $cachedPrefixKeyId[$prefix . '-' . $key];
            }
            if ($keyId != null && isset($cachedLangValuesObjects[$langId . '-' . $keyId])) {
                $valDto = $cachedLangValuesObjects[$langId . '-' . $keyId];
            }

            $keywords->setValue($valDto ? $valDto->getValue() : $prefix ."+". $key);
            $keywords->setLabel($language->text("base", "seo_meta_form_element_keywords_label"));
            $keywords->addValidator(new MetaInfoValidator());
            $this->addElement($keywords);

            $indexCheckbox = new CheckboxField("seo_index_{$item["entityKey"]}");
            $indexCheckbox->setValue(!in_array($item["entityKey"], $disabledItems));
            $indexCheckbox->setLabel($language->text("base", "seo_meta_form_element_index_label"));
            $this->addElement($indexCheckbox);

            $this->entities[$item["entityKey"]] = array(
                "label" => $item["entityLabel"],
                "iconClass" => empty($item["iconClass"]) ? "" : $item["iconClass"],
                "title" => array(
                    "length" => mb_strlen($title->getValue()),
                    "max" => BOL_SeoService::META_TITLE_MAX_LENGTH,
                    "isRed" => mb_strlen($title->getValue()) > BOL_SeoService::META_TITLE_MAX_LENGTH
                ),
                "desc" => array(
                    "length" => mb_strlen($desc->getValue()),
                    "max" => BOL_SeoService::META_DESC_MAX_LENGTH,
                    "isRed" => mb_strlen($desc->getValue()) > BOL_SeoService::META_DESC_MAX_LENGTH
                )
            );
        }

        $submit = new Submit("save");
        $submit->setValue(OW::getLanguage()->text("base", "edit_button"));
        $this->addElement($submit);
    }

    /**
     * @return array
     */
    public function getEntities()
    {
        return $this->entities;
    }

    public function processData( $post )
    {
        $langService = BOL_LanguageService::getInstance();

        if( $this->isValid($post) ){
            $values = $this->getValues();
            $dataToUpdate = array();
            reset($this->data);

            $this->seoData["disabledEntities"][current($this->data)["sectionKey"]] = array();

            foreach( $values as $key => $val )
            {
                if( strstr($key, "seo") )
                {
                    $arr = explode("_", $key);
                    array_shift($arr);
                    $attribute = array_shift($arr);
                    $entity = implode("_", $arr);

                    if( !isset($dataToUpdate[$entity]) )
                    {
                        $dataToUpdate[$entity] = array();
                    }

                    $dataToUpdate[$entity][$attribute] = $val;
                }
            }

            foreach ( $dataToUpdate as $entity => $items )
            {
                if(empty($items["index"]))
                {
                    $this->seoData["disabledEntities"][current($this->data)["sectionKey"]][] = $entity;
                }
            }

            $this->seoService->setMetaData($this->seoData);

            foreach ($this->data as $item)
            {
                if( empty($dataToUpdate[$item["entityKey"]]) )
                {
                    continue;
                }

                foreach ( $item["langs"] as $type => $langKey )
                {
                    if( empty($dataToUpdate[$item["entityKey"]][$type]) )
                    {
                        $dataToUpdate[$item["entityKey"]][$type] = "";
                    }

                    list($prefix, $key) = explode("+", $langKey);

                    $keyDto = $langService->findKey($prefix, $key);

                    if( $keyDto === null )
                    {
                        $prefixDto = $langService->findPrefix($prefix);

                        if( $prefixDto == null )
                        {
                            continue;
                        }

                        $keyDto = new BOL_LanguageKey();
                        $keyDto->setKey($key);
                        $keyDto->setPrefixId($prefixDto->getId());
                        $langService->saveKey($keyDto);
                    }

                    $valueDto = $langService->findValue($langService->getCurrent()->getId(), $keyDto->getId());

                    if ( $valueDto === null )
                    {
                        $valueDto = new BOL_LanguageValue();
                        $valueDto->setKeyId($keyDto->getId());
                        $valueDto->setLanguageId($langService->getCurrent()->getId());
                    }

                    $valueDto->setValue($dataToUpdate[$item["entityKey"]][$type]);
                    $langService->saveValue($valueDto);

                }
            }

            OW_DeveloperTools::getInstance()->clearLanguagesCache();

            return true;
        }

        return false;
    }
}

class MetaInfoValidator extends OW_Validator
{
    /**
     * Class constructor
     *
     * @param array $predefinedValues
     */
    public function __construct()
    {
        $this->setErrorMessage(OW::getLanguage()->text("base", "invalid_meta_error_message"));
    }

    /**
     * Is data valid
     *
     * @param mixed $value
     * @return boolean
     */
    public function isValid( $value )
    {
        return strip_tags(trim($value)) == trim($value);
    }

    /**
     * Get js validator
     *
     * @return string
     */
    public function getJsValidator()
    {
        $js = "{
            validate : function( value )
        	{       	
        	    var a = document.createElement('div');
                a.innerHTML = value;
                for (var c = a.childNodes, i = c.length; i--; ) {
                    if (c[i].nodeType == 1){
                        throw " . json_encode($this->getError()) . ";    
                    }
                }
        	
        	    return true;
        	},

        	getErrorMessage : function()
        	{
        		return " . json_encode($this->getError()) . "
    		}
        }";

        return $js;
    }
}
