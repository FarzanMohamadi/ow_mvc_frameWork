<?php
/**
 * @package ow_utilities
 * @since 1.0
 */
class UTIL_HtmlTag
{

    /**
     * Generates and returns HTML tag code.
     *
     * @param string $tag
     * @param array $attrs
     * @param boolean $pair
     * @param string $content
     * @return string
     */
    public static function generateTag( $tag, $attrs = null, $pair = false, $content = null )
    {
        $attrString = '';
        if ( $attrs !== null && !empty($attrs) )
        {
            foreach ( $attrs as $key => $value )
            {
                $attrString .= ' ' . $key . '="' . self::escapeHtmlAttr($value) . '"';
            }
        }

        return $pair ? '<' . $tag . $attrString . '>' . ( $content === null ? '' : $content ) . '</' . $tag . '>' : '<' . $tag . $attrString . ' />';
    }

    /**
     * Generates randow ID for HTML tags.
     *
     * @param string $prefix
     * @return string
     */
    public static function generateAutoId( $prefix = null )
    {
        $prefix = ( $prefix === null ) ? 'auto_id' : trim($prefix);

        return $prefix . '_' . UTIL_String::getRandomString(8, UTIL_String::RND_STR_ALPHA_NUMERIC);
    }
    /**
     * @var Jevix
     */
    private static $jevix;

    /**
     * @return Jevix
     */
    private static function getJevix( $tagList = null, $attrList = null, $blackListMode = false,
        $mediaSrcValidate = true )
    {
        if ( self::$jevix === null )
        {
            require_once OW_DIR_LIB . 'jevix' . DS . 'jevix.class.php';

            self::$jevix = new Jevix();
        }

        $tagRules = array();
        $commonAttrs = array();

        if ( !empty($tagList) )
        {
            foreach ( $tagList as $tag )
            {
                $tagRules[$tag] = array(Jevix::TR_TAG_LIST => true);
            }
        }

        if ( $attrList !== null )
        {
            foreach ( $attrList as $attr )
            {
                if ( strstr($attr, '.') )
                {
                    $parts = explode('.', $attr);

                    $tag = trim($parts[0]);
                    $param = trim($parts[1]);

                    if ( !strlen($tag) || !strlen($attr) )
                    {
                        continue;
                    }

                    if ( $tag === '*' )
                    {
                        $commonAttrs[] = $param;
                        continue;
                    }

                    if ( !isset($tagRules[$tag]) )
                    {
                        $tagRules[$tag] = array(Jevix::TR_TAG_LIST => true);
                    }

                    if ( !isset($tagRules[$tag][Jevix::TR_PARAM_ALLOWED]) )
                    {
                        $tagRules[$tag][Jevix::TR_PARAM_ALLOWED] = array();
                    }

                    $tagRules[$tag][Jevix::TR_PARAM_ALLOWED][$param] = true;
                }
                else
                {
                    $commonAttrs[] = trim($attr);
                }
            }
        }

        $shortTags = array('img', 'br', 'input', 'embed', 'param', 'hr', 'link', 'meta', 'base', 'col');
        foreach ( $shortTags as $shortTag )
        {
            if ( !isset($tagRules[$shortTag]) )
            {
                $tagRules[$shortTag] = array();
            }

            $tagRules[$shortTag][Jevix::TR_TAG_SHORT] = true;
        }

        $cutWithContent = array('script', 'embed', 'object', 'style');

        foreach ( $cutWithContent as $cutTag )
        {
            if ( !isset($tagRules[$cutTag]) )
            {
                $tagRules[$cutTag] = array();
            }

            $tagRules[$cutTag][Jevix::TR_TAG_CUT] = true;
        }

        self::$jevix->blackListMode = $blackListMode;
        self::$jevix->commonTagParamRules = $commonAttrs;
        self::$jevix->tagsRules = $tagRules;
        self::$jevix->mediaSrcValidate = $mediaSrcValidate;
        self::$jevix->mediaValidSrc = BOL_TextFormatService::getInstance()->getMediaResourceList();

        return self::$jevix;
    }

    /**
     * Removes all restricted HTML tags and attributes. Works with white and black lists.
     *
     * @param string $text
     * @param array $tagList
     * @param array $attributeList
     * @param boolean $nlToBr
     * @param boolean $blackListMode
     * @param boolean $autoLink
     * @param boolean $ignoreAdmin
     *
     * @return string
     */
    public static function stripTags( $text, array $tagList = null, array $attributeList = null, $blackListMode = false,
        $mediaSrcValidate = true, $ignoreAdmin = true ){
        $event = new OW_Event('frmsecurityessentials.before.html.strip', array('text' => $text, 'ignoreAdmin' => $ignoreAdmin));
        OW::getEventManager()->trigger($event);
        if(isset($event->getData()['text'])){
            $text = $event->getData()['text'];
        }
        return self::stripTagsByJevix($text, $tagList, $attributeList, $blackListMode, $mediaSrcValidate);
    }

    public static function stripTagsAndJs( $text, array $tagList = null, array $attributeList = null, $blackListMode = false,
                                      $mediaSrcValidate = true, $ignoreAdmin = true ){
        $event = new OW_Event('frmsecurityessentials.before.html.strip', array('text' => $text, 'ignoreAdmin' => $ignoreAdmin));
        OW::getEventManager()->trigger($event);
        if(isset($event->getData()['text'])){
            $text = $event->getData()['text'];
        }
        $text = self::stripJsByJevix($text);
        return self::stripTagsByJevix($text, $tagList, $attributeList, $blackListMode, $mediaSrcValidate);
    }

    public static function stripTagsByJevix($text, array $tagList = null, array $attributeList = null, $blackListMode = false, $mediaSrcValidate = true){
        // style remove fix
        if ( $blackListMode )
        {
            if ( $tagList === null )
            {
                $tagList = array();
            }

            $tagList[] = 'style';

//            if( $attributeList === null )
//            {
//                $attributeList = array();
//            }
//            
//            $attributeList[] = '*.style';
        }
        else
        {
            if ( is_array($tagList) )
            {
                if ( in_array('style', $tagList) )
                {
                    $tagList = array_diff($tagList, array('style'));
                }
            }

//            if( is_array( $attributeList ) )
//            {
//                foreach ( $attributeList as $key => $item )
//                {
//                    if( strstr($item, 'style') )
//                    {
//                        unset($attributeList[$key]);
//                    }
//                }
//            }
        }
        // fix end

        $jevix = self::getJevix($tagList, $attributeList, $blackListMode, $mediaSrcValidate);
        return $jevix->parse($text);
    }

    /**
     * Removes <script> tags and JS event handlers.
     *
     * @param string $text
     * @param boolean $ignoreAdmin
     * @return string
     */
    public static function stripJs( $text, $ignoreAdmin = true )
    {
        $event = new OW_Event('frmsecurityessentials.before.html.strip', array('text' => $text, 'ignoreAdmin' => $ignoreAdmin));
        OW::getEventManager()->trigger($event);
        if(isset($event->getData()['text'])){
            $text = $event->getData()['text'];
        }
        return self::stripJsByJevix($text);
    }

    public static function stripJsByJevix($text){
        $tags = array('script');

        $attrs = array(
            'onchange',
            'onclick',
            'ondblclick',
            'onerror',
            'onfocus',
            'onkeydown',
            'onkeypress',
            'onkeyup',
            'onload',
            'onmousedown',
            'onmousemove',
            'onmouseout',
            'onmouseover',
            'onmouseup',
            'onreset',
            'onselect',
            'onsubmit',
            'onunload');

        $jevix = self::getJevix($tags, $attrs, true, false);
        return $jevix->parse($text);
    }

    /**
     * Sanitizes provided html code to escape unclosed tags and params.
     * @param string $text
     * @return string
     */
    public static function sanitize( $text )
    {
        $event = new OW_Event('frmsecurityessentials.before.html.strip', array('text' => $text));
        OW::getEventManager()->trigger($event);
        if(isset($event->getData()['text'])){
            $text = $event->getData()['text'];
        }

        $jevix = self::getJevix(null, null, true, false);
        return $jevix->parse($text);
    }

    /**
     * Replaces all urls with link tags in the provided text.
     * @deprecated  Use linkify instead
     * @param string $text
     * @return string
     */
    public static function autoLink( $text )
    {
        $event = new OW_Event('frmsecurityessentials.before.html.strip', array('text' => $text));
        OW::getEventManager()->trigger($event);
        if(isset($event->getData()['text'])){
            $text = $event->getData()['text'];
        }

//        return self::linkify($text);
//
        $jevix = self::getJevix(array(), array(), true, false);
        $jevix->isAutoLinkMode = true;

        return $jevix->parse($text);
    }

    /***
     * @param $input
     * @return string|string[]|null
     */
    public static function linkify($input){
        $re = <<<'REGEX'
!
    (
      <\w++
      (?:
        \s++
      | [^"'<>]++
      | "[^"]*+"
      | '[^']*+'
      )*+
      >
    )
    |
    (\b https?://[^\s"'<>]++ )
    |
    (\b www\d*+\.\w++\.\w{2,}(/\w*)*(\?(\w|\=|&)*)* )
!xi
REGEX;

        return preg_replace_callback($re, function($m){
            if($m[1]) return $m[1];
            $url = htmlspecialchars($m[2] ? $m[2] : "http://$m[3]");
            $text = htmlspecialchars(isset($m[3])? "$m[2]$m[3]" : "$m[2]");
            return "<a href='$url' class=\"ow_autolink\" target=\"_blank\" rel=\"nofollow\">$text</a>";
        },
            $input);
    }

    /**
     * Escape a string for the URI or Parameter contexts. This should not be used to escape
     * an entire URI - only a subcomponent being inserted. The function is a simple proxy
     * to rawurlencode() which now implements RFC 3986 since PHP 5.3 completely.
     *
     * @param string $string
     * @return string
     */
    public static function escapeUrl( $string = null )
    {
        if ( !$string )
        {
            return;
        }

        return rawurlencode($string);
    }

    /**
     * Escape a string for the HTML Body context where there are very few characters
     * of special meaning. Internally this will use htmlspecialchars().
     *
     * @param string $string
     * @return string
     */
    public static function escapeHtml( $string = null )
    {
        if ( !$string )
        {
            return;
        }

        return htmlspecialchars($string, ENT_QUOTES);
    }

    /**
     * Escape a string for the HTML Attribute context. We use an extended set of characters
     * to escape that are not covered by htmlspecialchars() to cover cases where an attribute
     * might be unquoted or quoted illegally (e.g. backticks are valid quotes for IE).
     *
     * @param string $string
     * @return string
     */
    public static function escapeHtmlAttr( $string = null )
    {
        if($string === "0"){
            return $string;
        }
        if ( !$string )
        {
            return;
        }

        return htmlspecialchars($string, ENT_COMPAT);
    }

    /**
     * Escapes chars to make sure that string doesn't contain valid JS code
     * 
     * @param string $string
     * @return string
     */
    public static function escapeJs( $string = null )
    {
        if ( !$string )
        {
            return;
        }

        return strtr($string,
            array('\\' => '\\\\', "'" => "\\'", '"' => '\\"', "\r" => '\\r', "\n" => '\\n', '</' => '<\/'));
    }

    /**
     * @param $string
     * @return string
     */
    public static function convertPersianNumbers($string)
    {
        if ( !$string )
        {
            return '';
        }
        $string = str_replace("۱","1",$string);
        $string = str_replace("۲","2",$string);
        $string = str_replace("۳","3",$string);
        $string = str_replace("۴","4",$string);
        $string = str_replace("۵","5",$string);
        $string = str_replace("۶","6",$string);
        $string = str_replace("۷","7",$string);
        $string = str_replace("۸","8",$string);
        $string = str_replace("۹","9",$string);
        $string = str_replace("۰","0",$string);

        return $string;
    }
}
