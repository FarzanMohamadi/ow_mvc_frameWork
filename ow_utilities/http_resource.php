<?php
require_once  OW_DIR_LIB . 'oembed' . DS. 'oembed.php';

class UTIL_HttpResource
{

    /**
     * @param $url
     * @param int $timeout
     * @return OW_HttpResource
     * @throws Redirect404Exception
     */
    public static function getContents( $url, $timeout = 20 )
    {
        $event=OW_EventManager::getInstance()->trigger(new OW_Event('check.user.access.getContents',
            array(['url'=>$url,'timeout'=>$timeout])));
        if(isset($event->getData()['denied_access']) && $event->getData()['denied_access']==true)
        {
            OW::getLogger()->writeLog(OW_Log::ALERT, 'unauthorized_get_content', [ 'enType'=>'core', 'url'=>(int) $url]);
            throw new Redirect404Exception();
        }
        $context = stream_context_create( array(
            'http'=>array(
                'timeout' => $timeout,
                'header' => "User-Agent:  Content Fetcher\r\n"
            )
        ));

        return OW::getStorage()->fileGetContent($url, false, false, $context);
    }

    /**
     * @param $url
     * @return array|null
     * @throws Redirect404Exception
     */
    public static function getOEmbed( $url )
    {
        $urlInfo = parse_url($url);
        if (isset($urlInfo['scheme']) && !in_array($urlInfo['scheme'], array('http', 'https'))) {
            throw new Redirect404Exception();
        }
        return OEmbed::parse($url);
    }
}