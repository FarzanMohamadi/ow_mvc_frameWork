<?php
/**
 * Base class for HTML document object,
 * handles rendered output context (html document includes, headers, styles, etc).
 *
 * @package ow.ow_core
 * @since 1.0
 */
class OW_HtmlDocument extends OW_Document
{
    const META_CONTENT_TYPE = 'Content-type';
    const META_CONTENT_LANGUAGE = 'Content-language';
    const META_EXPIRES = 'expires';
    const META_REFRESH = 'refresh';
    const META_AUTHOR = 'author';
    const META_GENERATOR = 'generator';
    const META_COPYRIGHT = 'copyright';
    const META_ROBOTS = 'robots';
    const META_DOCUMENT_STATE = 'document-state';
    const META_URL = 'url';
    const META_RESOURCE_TYPE = 'resource-type';
    const META_PICS_LABEL = 'pics-label';
    const META_REPLY_TO = 'reply-to';

    /**
     * Included stylesheet file urls.
     *
     * @var array
     */
    protected $styleSheets = array('added' => array(), 'items' => array());

    /**
     * Appended style declarations.
     *
     * @var array
     */
    protected $styleDeclarations = array('hash' => array(), 'items' => array());

    /**
     * Included javascript files.
     *
     * @var array
     */
    protected $javaScripts = array('added' => array(), 'items' => array());

    /**
     * Appended javascript code.
     *
     * @var array
     */
    protected $javaScriptDeclarations = array('hash' => array(), 'items' => array());

    /**
     * Javascript code added before script file includes.
     *
     * @var array
     */
    protected $preIncludeJavaScriptDeclarations = array();

    /**
     * Appended onload javascript.
     *
     * @var array
     */
    protected $onloadJavaScript = array('hash' => array(), 'items' => array());

    /**
     * Added head area links.
     *
     * @var array
     */
    private $links = array();

    /**
     * Added meta tags.
     *
     * @var array
     */
    private $meta = array();

    /**
     * Custom head info.
     *
     * @var array
     */
    private $customHeadInfo = array();

    /**
     * Document master page.
     *
     * @var OW_MasterPage
     */
    private $masterPage;

    /**
     * Content area html code.
     *
     * @var string
     */
    private $body = '';

    /**
     * HTML code to be appended after document was rendered.
     *
     * @var string
     */
    private $appendCode = '';

    /**
     * HTML code to be prepended after document was rendered.
     *
     * @var string
     */
    private $prependCode = '';

    /**
     * Document keywords for meta tags.
     *
     * @var string
     */
    private $keywords;

    /**
     * Document heading.
     *
     * @var string
     */
    private $heading;

    /**
     * Document heading icon class.
     *
     * @var string
     */
    private $headingIconClass;

    /**
     * @var string
     */
    private $bodyClass = "";

    /**
     * @var array
     */
    private $availableMetaAttrs = array('http-equiv', 'name', 'property', 'itemprop');

    /**
     * @return string
     */
    public function getBodyClass()
    {
        return $this->bodyClass;
    }

    /**
     * @param string $bodyClass
     */
    public function setBodyClass( $bodyClass )
    {
        $this->bodyClass = trim($bodyClass);
    }

    /**
     * @param string $class
     */
    public function addBodyClass( $class )
    {
        $this->bodyClass .= " " . trim($class);
    }

    /**
     * Returns all stylesheets.
     *
     * @return array
     */
    public function getStyleSheets()
    {
        return $this->styleSheets;
    }

    /**
     * Sets whole stylesheets array.
     *
     * @param array $styleSheets
     */
    public function setStyleSheets( array $styleSheets )
    {
        $this->styleSheets = $styleSheets;
    }

    /**
     * Returns all included javascript files.
     *
     * @return array
     */
    public function getJavaScripts()
    {
        return $this->javaScripts;
    }

    /**
     * Sets whole scripts array.
     *
     * @param array $javaScripts
     */
    public function setJavaScripts( array $javaScripts )
    {
        $this->javaScripts = $javaScripts;
    }

    /**
     * Returns all meta entries.
     *
     * @return array
     */
    public function getMeta()
    {
        return $this->meta;
    }

    /**
     * Replaces all meta info.
     *
     * @param array $meta
     */
    public function setMeta( array $meta )
    {
        $this->meta = $meta;
    }

    /**
     * Constructor.
     *
     * @param array $params
     */
    public function __construct()
    {
        $this->setType(OW_Document::HTML);
    }

    /**
     * Returns document heading icon class.
     *
     * @return string
     */
    public function getHeadingIconClass()
    {
        return $this->headingIconClass;
    }

    /**
     * Sets document heading icon class.
     *
     * @param string $headingIcon
     */
    public function setHeadingIconClass( $headingIconClass )
    {
        $this->headingIconClass = $headingIconClass;
    }

    /**
     * Sets document heading.
     *
     * @param string $heading
     */
    public function setHeading( $heading )
    {
        $eventActionList = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::CORRECT_MULTIPLE_LANGUAGE_SENTENCE_ALIGNMENT, array('sentence' => $heading)));
        if(isset($eventActionList->getData()['correctedSentence'])) {
            $heading = $eventActionList->getData()['correctedSentence'];
        }
        $this->throwEvent("core.set_document_heading", array("str" => $heading));
        $this->heading = $heading;

        // change title if default
        if($this->titleIsDefault){
            $this->title = trim($heading);
        }
    }

    /**
     * @return string $heading
     */
    public function getHeading()
    {
        return $this->heading;
    }

    /**
     * Returns HTML document keywords.
     *
     * @return string
     */
    public function getKeywords()
    {
        return $this->keywords;
    }

    /**
     * Sets HTML document keywords.
     *
     * @param mixed $keywords
     * @return OW_HtmlDocument
     */
    public function setKeywords( $keywords )
    {
        if ( is_array($keywords) )
        {
            $keywords = implode(',', $keywords);
        }

        $this->keywords = $keywords;

        return $this;
    }

    /**
     * Sets html document body code.
     *
     * @param string $code
     * @return OW_HtmlDocument
     */
    public function setBody( $code )
    {
        $this->body = $code;

        return $this;
    }

    /**
     * Returns html document body.
     *
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * Sets master page in html document.
     *
     * @param OW_MasterPage $masterPage
     * @return OW_HtmlDocument
     */
    public function setMasterPage( OW_MasterPage $masterPage )
    {
        $this->masterPage = $masterPage;
    }

    /**
     * Returns document master page.
     *
     * @return OW_MobileMasterPage
     */
    public function getMasterPage()
    {
        return $this->masterPage;
    }

    /**
     * Adds stylesheet file to document.
     *
     * @param string $url
     * @return OW_HtmlDocument
     */
    public function addStyleSheet( $url, $media = 'all', $priority = null )
    {
        $url = trim($url);
        $url = $url. '?' . OW::getConfig()->getValue('base', 'cachedEntitiesPostfix');
        $media = trim($media);

        if ( in_array($url, $this->styleSheets['added']) )
        {
            return $this;
        }

        $priority = ($priority === null) ? 1000 : (int) $priority;

        $this->styleSheets['added'][] = $url;

        $this->styleSheets['items'][$priority][$media][] = $url;

        return $this;
    }

    /**
     * Adds head style declarations to document.
     *
     * @param string $style
     * @return OW_HtmlDocument
     */
    public function addStyleDeclaration( $style, $media = 'all', $priority = null )
    {
        $media = trim(mb_strtolower($media));

        $styleHash = crc32($style);

        if ( in_array($styleHash, $this->styleDeclarations['hash']) )
        {
            return $this;
        }

        $priority = ($priority === null) ? 1000 : (int) $priority;

        $this->styleDeclarations['hash'][] = $styleHash;

        $this->styleDeclarations['items'][$priority][$media][] = $style;

        return $this;
    }

    /**
     * Adds javascript file to document.
     *
     * @param string $url
     * @param string $type
     * @return OW_HtmlDocument
     */
    public function addScript( $url, $type = "text/javascript", $priority = null )
    {
        $url = trim($url);
        $url = $url. '?' . OW::getConfig()->getValue('base', 'cachedEntitiesPostfix');
        if ( in_array($url, $this->javaScripts['added']) )
        {
            return $this;
        }

        $priority = ($priority === null) ? 1000 : (int) $priority;

        $this->javaScripts['added'][] = $url;

        $this->javaScripts['items'][$priority][$type][] = $url;

        return $this;
    }

    /**
     * Adds head javascript code  to document.
     *
     * @param string $script
     * @param string $type
     * @return OW_HtmlDocument
     */
    public function addScriptDeclaration( $script, $type = "text/javascript", $priority = null )
    {
        $type = trim(mb_strtolower($type));

        $scriptHash = crc32($script);

        if ( in_array($scriptHash, $this->javaScriptDeclarations['hash']) )
        {
            return $this;
        }

        $this->javaScriptDeclarations['hash'][] = $scriptHash;

        $priority = ($priority === null) ? 1000 : (int) $priority;

        $this->javaScriptDeclarations['items'][$priority][$type][] = $script;

        return $this;
    }

    /**
     * @param $script
     * @param null $priority
     * @return $this
     */
    public function addOnloadScript( $script, $priority = null )
    {
        $scriptHash = crc32($script);

        if ( in_array($scriptHash, $this->onloadJavaScript['hash']) )
        {
            return $this;
        }

        $this->onloadJavaScript['hash'][] = $scriptHash;

        $priority = ($priority === null) ? 1000 : (int) $priority;

        $this->onloadJavaScript['items'][$priority][] = $script;

        return $this;
    }

    /**
     * Adds head javascript code  to document before script file includes.
     *
     * @param string $script
     * @param string $type
     * @return OW_HtmlDocument
     */
    public function addScriptDeclarationBeforeIncludes( $script, $type = "text/javascript", $priority = null )
    {
        $type = trim(mb_strtolower($type));

        $scriptHash = crc32($script);

        if ( in_array($scriptHash, $this->javaScriptDeclarations) )
        {
            return $this;
        }

        $priority = ($priority === null) ? 1000 : (int) $priority;

        $this->preIncludeJavaScriptDeclarations[$priority][$type][] = $script;

        return $this;
    }

    /**
     * Sets document favicon.
     *
     * @param string $url
     * @param string $type
     * @param string $relation
     * @return OW_HtmlDocument
     */
    public function setFavicon( $url )
    {
        $attributes = array('rel' => 'shortcut icon', 'type' => 'image/x-icon', 'href' => trim($url));

        $this->links[] = $attributes;

        return $this;
    }

    /**
     * Appends custom HTML to the rendered document.
     *
     * @param string $code
     * @return OW_HtmlDocument
     */
    public function appendBody( $code )
    {
        $this->appendCode .= $code;

        return $this;
    }

    /**
     * Prepends custom HTML code to the rendered document.
     *
     * @param $code
     * @return OW_HtmlDocument
     */
    public function prependBody( $code )
    {
        $this->prependCode .= $code;

        return $this;
    }

    /**
     * Adds meta info to document.
     * You should also specify meta attribute name: `name` or `http-equiv`
     *
     * @param string $name
     * @param string $value
     * @param string $attributeName
     * @return OW_HtmlDocument
     * @throws InvalidArgumentException
     */
    public function addMetaInfo( $name, $value, $attributeName = 'name' )
    {
        if ( !in_array($attributeName, $this->availableMetaAttrs) )
        {
            throw new InvalidArgumentException('Invalid meta attribute name was provided!');
        }

        $this->meta[$attributeName][$name] = $value;

        return $this;
    }

    /***
     * adds microdata as a JSON-LD
     * https://en.wikipedia.org/wiki/JSON-LD
     * https://www.google.com/webmasters/markup-helper/u/0/
     *
     * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
     * @param string $type
     * @param null $name
     * @param int $authorId
     * @param null $url
     * @param null $image
     * @param array $extraInfo
     * @return $this
     */
    public function addJSONLD( $type = "Article", $name = null, $authorId = 1, $url=null, $image=null, $extraInfo = [] )
    {
        $json = [
            "@context" => "http://schema.org",
            "@type" => $type,
            "name" => !empty($name)? $name : OW::getConfig()->getValue('base', 'site_name'),
            "image" => !empty($image)? $image : OW_URL_HOME.'favicon.ico',
            "url" => !empty($url)? $url : OW_URL_HOME
        ];
        if(!empty($authorId)){
            $json['author'] = [
                "@type" => "Person",
                "name" => BOL_UserService::getInstance()->getDisplayName($authorId)
            ];
        }
        $json = array_merge($json, $extraInfo);

        $html =
'<!-- JSON-LD markup. -->
<script type="application/ld+json">
'.json_encode($json).'
</script>';
        $this->customHeadInfo[] = $html;
        return $this;
    }

    /**
     * Adds cutom meta info.
     *
     * @param string $infoString
     */
    public function addCustomHeadInfo( $infoString )
    {
        $this->customHeadInfo[] = $infoString;
    }

    /**
     * @return string
     */
    public function render()
    {
        if ( $this->getTemplate() === null )
        {
            $this->setTemplate(OW::getThemeManager()->getMasterPageTemplate('html_document'));
        }

        $this->addMetaInfo(self::META_CONTENT_TYPE, $this->getMime() . '; charset=' . $this->getCharset(), 'http-equiv');
        $this->addMetaInfo(self::META_CONTENT_LANGUAGE, $this->getLanguage(), 'http-equiv');

        $this->getMasterPage()->assign('content', $this->body);
        $this->getMasterPage()->assign('heading', $this->getHeading());
        $this->getMasterPage()->assign('heading_icon_class', $this->getHeadingIconClass());
        if(empty($this->getHeading())){
            $this->getMasterPage()->assign('heading', OW::getConfig()->getValue('base', 'site_name'));
            $this->getMasterPage()->assign('heading_icon_class', 'ow_hidden ow_default_heading');
        }

        $this->throwEvent("core.before_master_page_render");
        $masterPageOutput = $this->getMasterPage()->render();
        $this->throwEvent("core.after_master_page_render");

        $headData = '';
        $jsData = '';

        // META INFO
        if ( $this->getDescription() )
        {
            $headData .= UTIL_HtmlTag::generateTag('meta', array("name" => "description", "content" => $this->getDescription())) . PHP_EOL;
        }

        if ( $this->getKeywords() )
        {
            $headData .= UTIL_HtmlTag::generateTag('meta', array("name" => "keywords", "content" => $this->getKeywords())) . PHP_EOL;
        }

        foreach ( $this->meta as $key => $value )
        {
            if ( in_array($key, $this->availableMetaAttrs) && !empty($value) )
            {
                foreach ( $value as $name => $content )
                {
                    $attrs = array($key => $name, 'content' => $content);
                    $headData .= UTIL_HtmlTag::generateTag('meta', $attrs) . PHP_EOL;
                }
            }
        }

        // CSS FILE INCLUDES
        ksort($this->styleSheets['items']);

        foreach ( $this->styleSheets['items'] as $priority => $scipts )
        {
            foreach ( $scipts as $media => $urls )
            {
                foreach ( $urls as $url )
                {
                    $attrs = array('rel' => 'stylesheet', 'type' => 'text/css', 'href' => $url, 'media' => $media);
                    $headData .= UTIL_HtmlTag::generateTag('link', $attrs) . PHP_EOL;
                }
            }
        }

        // JS PRE INCLUDES HEAD DECLARATIONS
        ksort($this->preIncludeJavaScriptDeclarations);

        foreach ( $this->preIncludeJavaScriptDeclarations as $priority => $types )
        {
            foreach ( $types as $type => $declarations )
            {
                foreach ( $declarations as $declaration )
                {
                    $attrs = array('type' => $type);
                    $jsData .= UTIL_HtmlTag::generateTag('script', $attrs, true, PHP_EOL . $declaration . PHP_EOL) . PHP_EOL;
                }
            }
        }

        // JS FILE INCLUDES
        ksort($this->javaScripts['items']);
        $headJsInclude = '';
        foreach ( $this->javaScripts['items'] as $priority => $types )
        {
            foreach ( $types as $type => $urls )
            {
                foreach ( $urls as $url )
                {
                    $attrs = array('type' => $type, 'src' => $url);

                    //TODO remake temp fix - get JQUERY lib to the head area
                    if ( $priority == -100 )
                    {
                        $headJsInclude .= UTIL_HtmlTag::generateTag('script', $attrs, true) . PHP_EOL;
                    }
                    else
                    {
                        $jsData .= UTIL_HtmlTag::generateTag('script', $attrs, true) . PHP_EOL;
                    }
                }
            }
        }

        // CSS HEAD DECLARATIONS
        ksort($this->styleDeclarations['items']);

        foreach ( $this->styleDeclarations['items'] as $priority => $mediaTypes )
        {
            foreach ( $mediaTypes as $media => $declarations )
            {
                $attrs = array('media' => $media);
                $headData .= UTIL_HtmlTag::generateTag('style', $attrs, true, implode(' ', $declarations));
            }
        }

        // JS HEAD DECLARATIONS
        ksort($this->javaScriptDeclarations['items']);

        foreach ( $this->javaScriptDeclarations['items'] as $priority => $types )
        {
            foreach ( $types as $type => $declarations )
            {
                foreach ( $declarations as $declaration )
                {
                    $attrs = array('type' => $type);
                    $jsData .= UTIL_HtmlTag::generateTag('script', $attrs, true,
                            PHP_EOL . '(function() {' . $declaration . '})();' . PHP_EOL) . PHP_EOL;
                }
            }
        }

        // ONLOAD JS
        $jsData .= '<script type="text/javascript">' . PHP_EOL . '$(function () {' . PHP_EOL;

        ksort($this->onloadJavaScript['items']);

        foreach ( $this->onloadJavaScript['items'] as $priority => $scripts )
        {
            foreach ( $scripts as $script )
            {
                $jsData .= '(function(_scope) {' . $script . '})({});' . PHP_EOL;
            }
        }

        $jsData .= PHP_EOL . '});' . PHP_EOL . '</script>';

        // LINKS
        foreach ( $this->links as $linkInfo )
        {
            $headData .= UTIL_HtmlTag::generateTag('link', $linkInfo) . PHP_EOL;
        }

        $customHeadData = implode('', $this->customHeadInfo);
        $siteElements = FRMSecurityProvider::getCurrentSiteGeneralElements();
        $assignArray = array(
            'title' => $this->getTitle(),
            'headData' => $headData . $headJsInclude . $customHeadData,
            'language' => $this->language,
            'direction' => $this->direction,
            'pageBody' => $this->prependCode . $masterPageOutput . $this->appendCode . $jsData . OW_Document::APPEND_PLACEHOLDER,
            'bodyClass' => $this->bodyClass,
            'currentTheme' => $siteElements['themeKey'],
            'currentThemeStaticsUrl' => $siteElements['logoUrl']
        );

        $renderer = OW_ViewRenderer::getInstance();
        $renderer->clearAssignedVars();
        $renderer->assignVars($assignArray);
        return $renderer->renderTemplate($this->getTemplate());
    }

    protected function throwEvent( $name, $params = array() )
    {
        OW::getEventManager()->trigger(new OW_Event($name, $params));
    }
}
