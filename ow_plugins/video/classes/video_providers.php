<?php
/**
 * Video service providers class
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow.plugin.video.classes
 * @since 1.0
 */
class VideoProviders
{
    private $code;

    const PROVIDER_YOUTUBE = 'youtube';
    const PROVIDER_GOOGLEVIDEO = 'googlevideo';
    const PROVIDER_APARAT = 'aparat';
    const PROVIDER_APARAT_URL = 'aparat_url';
    const PROVIDER_UNDEFINED = 'undefined';

    private static $provArr;

    public function __construct( $code )
    {
        $this->code = $code;

        $this->init();
    }

    private function init()
    {
        if ( !isset(self::$provArr) )
        {
            self::$provArr = array(
                self::PROVIDER_YOUTUBE => '//www.youtube(-nocookie)?.com/',
                self::PROVIDER_GOOGLEVIDEO => 'http://video.google.com/',
                self::PROVIDER_APARAT =>'src="https://www.aparat.com/',
                self::PROVIDER_APARAT_URL=>'https://www.aparat.com/',
            );
            $event = new OW_Event(FRMEventManager::ON_AFTER_VIDEO_PROVIDERS_DEFINED);
            OW::getEventManager()->trigger($event);
            if (is_array($event->getData())){
                self::$provArr =  array_merge(self::$provArr,$event->getData());
            }
        }
    }

    public function detectProvider()
    {
        foreach ( self::$provArr as $name => $url )
        {
            if ( preg_match("~$url~", $this->code) )
            {
                return $name;
            }
        }
        return self::PROVIDER_UNDEFINED;
    }

    public function getProviderThumbUrl( $provider = null )
    {
        if ( !$provider )
        {
            $provider = $this->detectProvider();
        }

        $className = 'VideoProvider' . ucfirst($provider);

        /** @var $class VideoProviderUndefined */
        if ( class_exists($className) )
        {
            $class = new $className;
        }
        else
        {
            return VideoProviders::PROVIDER_UNDEFINED;
        }
        $thumb = $class->getThumbUrl($this->code);

        return $thumb;
    }
}

class VideoProviderYoutube
{
    const clipUidPattern = '\/\/www\.youtube(-nocookie)?\.com\/(v|embed)\/([^?&"]+)[?&"]';
    const thumbUrlPattern = 'http://img.youtube.com/vi/()/default.jpg';

    private static function getUid( $code )
    {
        $pattern = self::clipUidPattern;

        return preg_match("~{$pattern}~", $code, $match) ? $match[3] : null;
    }

    public static function getThumbUrl( $code )
    {
        if ( ($uid = self::getUid($code)) !== null )
        {
            $url = str_replace('()', $uid, self::thumbUrlPattern);

            return strlen($url) ? $url : VideoProviders::PROVIDER_UNDEFINED;
        }

        return VideoProviders::PROVIDER_UNDEFINED;
    }
}

class VideoProviderGooglevideo
{
    const clipUidPattern = 'http:\/\/video\.google\.com\/googleplayer\.swf\?docid=([^\"][a-zA-Z0-9-_]+)[&\"]';
    const thumbXmlPattern = 'http://video.google.com/videofeed?docid=()';

    private static function getUid( $code )
    {
        $pattern = self::clipUidPattern;

        return preg_match("~{$pattern}~", $code, $match) ? $match[1] : null;
    }

    public static function getThumbUrl( $code )
    {
        if ( ($uid = self::getUid($code)) !== null )
        {
            $xmlUrl = str_replace('()', $uid, self::thumbXmlPattern);

            $fileCont = OW::getStorage()->fileGetContent($xmlUrl, true);

            if ( strlen($fileCont) )
            {
                preg_match("/media:thumbnail url=\"([^\"]\S*)\"/siU", $fileCont, $match);

                $url = isset($match[1]) ? $match[1] : VideoProviders::PROVIDER_UNDEFINED;
            }

            return !empty($url) ? $url : VideoProviders::PROVIDER_UNDEFINED;
        }

        return VideoProviders::PROVIDER_UNDEFINED;
    }
}

class VideoProviderAparat
{
    private static $AparatNewEmbedCodeRegex = '/<div\s+id="\d+">\s*<script\s+type="text\/JavaScript"\s+src="((?:https|http):\/\/www\.aparat\.com\/[\/\w\?\[\]=\&]+)">\s*<\/script>\s*<\/div>/i';
    private static $AparatOldEmbedCodeRegex = '/<iframe\s+src="((?:https|http):\/\/www\.aparat\.com\/[\/\w\?\[\]=\&]+)"[\s\w="]+>\s*<\/iframe>/i';

    public static function getThumbUrl( $code )
    {
        $url = null;
        if (preg_match_all(self::$AparatNewEmbedCodeRegex, $code, $matches)){
            $uid = $matches[1][0];
            $content = OW::getStorage()->fileGetContent($uid, true);
            if(!preg_match_all('/(?:http|https):\/\/www\.aparat\.com\/video\/video\/embed\/videohash\/\w+\/vt\/frame/i', $content, $matches)){
                return VideoProviders::PROVIDER_UNDEFINED;
            }
            $url = $matches[0][0];
        }
        if (preg_match_all(self::$AparatOldEmbedCodeRegex, $code, $matches)){
            $url = $matches[1][0];
        }
        if (!$url)
            return VideoProviders::PROVIDER_UNDEFINED;

        $content = OW::getStorage()->fileGetContent($url, true);
        if(!preg_match_all('/[\'"]poster[\'"]\:[\'"](?:https|http)\:....static\.cdn\.asset\.aparat\.com..avt..?([^\'"\)]*)[\'"]/i',$content,$matches)){
            return VideoProviders::PROVIDER_UNDEFINED;
        }
        $matches[1][0] = 'https://static.cdn.asset.aparat.com/avt/'.$matches[1][0];
        $matches[0][0]='style="background-image: url('.$matches[1][0].")";
        $url = $matches[1][0];
//        $url = str_replace('https','http',$url);
        return !empty($url) ? $url : VideoProviders::PROVIDER_UNDEFINED;
    }
}
class VideoProviderUndefined
{
    public static function getThumbUrl( $code )
    {
        return VideoProviders::PROVIDER_UNDEFINED;
    }
}
