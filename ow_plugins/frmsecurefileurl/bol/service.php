<?php
/**
 * 
 * All rights reserved.
 */

/**
 * 
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmsecurefileurl.bol
 * @since 1.0
 */
class FRMSECUREFILEURL_BOL_Service
{
    private static $classInstance;
    
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }
    
    private $urlsDao;
    
    private function __construct()
    {
        $this->urlsDao = FRMSECUREFILEURL_BOL_UrlsDao::getInstance();
    }

    /***
     * @param $key
     * @param $hash
     * @param $type
     * @param $path
     * @return FRMSECUREFILEURL_BOL_Urls
     */
    public function addUrl($key, $hash, $type, $path)
    {
        return $this->urlsDao->addUrl($key, $hash, $type, $path);
    }

    public function bufferVideo($file){
        $fp = @fopen($file, 'rb');
        $size   = filesize($file); // File size
        $length = $size;           // Content length
        $start  = 0;               // Start byte
        $end    = $size - 1;       // End byte

        // Close open sessions
        session_write_close();

        // Turn off apache-level compression
        @apache_setenv('no-gzip', 1);

        // Turn off compression
        @ini_set('zlib.output_compression', 0);

        // Turn error reporting off
        @ini_set('error_reporting', E_ALL & ~ E_NOTICE);

        // Tell browser not to cache this
        header("Cache-Control: no-cache, must-revalidate");

        // close any existing buffers
        while (ob_get_level()) ob_end_clean();

        header('Content-type: video/mp4');
        header("Accept-Ranges: bytes");
        if (isset($_SERVER['HTTP_RANGE'])) {
            $c_start = $start;
            $c_end   = $end;
            list(, $range) = explode('=', $_SERVER['HTTP_RANGE'], 2);
            if (strpos($range, ',') !== false) {
                header('HTTP/1.1 416 Requested Range Not Satisfiable');
                header("Content-Range: bytes $start-$end/$size");
                exit;
            }
            if ($range == '-') {
                $c_start = $size - substr($range, 1);
            }else{
                $range  = explode('-', $range);
                $c_start = $range[0];
                $c_end   = (isset($range[1]) && is_numeric($range[1])) ? $range[1] : $size;
            }
            $c_end = ($c_end > $end) ? $end : $c_end;
            if ($c_start > $c_end || $c_start > $size - 1 || $c_end >= $size) {
                header('HTTP/1.1 416 Requested Range Not Satisfiable');
                header("Content-Range: bytes $start-$end/$size");
                exit;
            }
            $start  = $c_start;
            $end    = $c_end;
            $length = $end - $start + 1;
            fseek($fp, $start);
            header('HTTP/1.1 206 Partial Content');
        }
        header("Content-Range: bytes $start-$end/$size");
        header("Content-Length: ".$length);
        $buffer = 1024 * 8;
        while(!feof($fp) && ($p = ftell($fp)) <= $end) {
            if ($p + $buffer > $end) {
                $buffer = $end - $p + 1;
            }
            set_time_limit(0);
            echo fread($fp, $buffer);
            flush();
        }
        fclose($fp);
        exit();
    }

    /***
     * @param $id
     * @param $new_hash
     * @return mixed
     */
    public function updateUrl($id, $new_hash)
    {
        return $this->urlsDao->updateUrl($id, $new_hash);
    }

    /***
     * @param $key
     * @return mixed|null
     */
    public function existUrlByKey($key)
    {
        return $this->urlsDao->existUrlByKey($key);
    }

    /***
     * @param array $keyList
     * @return array
     */
    public function existUrlByKeyList($keyList)
    {
        return $this->urlsDao->existUrlByKeyList($keyList);
    }

    public function onAddMembersOnlyException( BASE_CLASS_EventCollector $event )
    {
        $event->add(array('controller' => 'FRMSECUREFILEURL_CTRL_Url', 'action' => 'index'));
    }

    /***
     * @param $hash
     * @return mixed|null
     */
    public function existUrlByHash($hash)
    {
        return $this->urlsDao->existUrlByHash($hash);
    }

    /***
     * @param $time
     */
    public function deleteExpired( $time )
    {
        $this->urlsDao->deleteExpired($time);
    }

    public function addStaticFiles(OW_Event $event){
        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('frmsecurefileurl')->getStaticJsUrl().'frmsecurefileurl.js');
    }

    public function getKeyOfUrl($urlArray){
        $firstDetectArray = array('photo', 'groups', 'forum', 'event', 'video');
        $secondDetectArray = array('base', 'mailbox');
        $attachmentArray = array('attachments');
        if(sizeof($urlArray) == 4 && in_array($urlArray['2'], $firstDetectArray)){
            return $urlArray['3'];
        }else if(sizeof($urlArray) == 5 && in_array($urlArray['3'], $firstDetectArray)){
            return $urlArray['4'];
        }else if(sizeof($urlArray) == 5 && in_array($urlArray['2'], $secondDetectArray) && in_array($urlArray['3'], $attachmentArray)){
            return $urlArray['4'];
        }else if(sizeof($urlArray) == 6 && in_array($urlArray['3'], $secondDetectArray) && in_array($urlArray['4'], $attachmentArray)){
            return $urlArray['5'];
        }

        return null;
    }

    public function getKeyFileUrl($url) {
        $url = str_replace(OW_URL_HOME, '', $url);
        $urlArray = explode('/', $url);
        $key = $this->getKeyOfUrl($urlArray);
        return array('urlArray' => $urlArray, 'key' => $key);
    }

    public function processFileUrl(OW_Event $event){
        $params = $event->getParams();
        if(isset($params['returnPath']) && $params['returnPath'] == true){
            return;
        }
        $cache = array();
        if (isset($params['params']['data']['cache'])) {
            $cache = $params['params']['data']['cache'];
        } else if (isset($params['params']['cache'])) {
            $cache = $params['params']['cache'];
        } else if (isset($params['params']['params']['cache'])) {
            $cache = $params['params']['params']['cache'];
        }
        if(isset($params['url']) && isset($params['path'])){
            $url = $params['url'];
            $keyInfo = $this->getKeyFileUrl($url);
            $urlArray = $keyInfo['urlArray'];
            $key = $keyInfo['key'];

            if($key != null){
                $url = null;
                if (isset($cache['secure_files']) && array_key_exists($key, $cache['secure_files'])) {
                    $url = $cache['secure_files'][$key];
                } else {
                    $url = $this->existUrlByKey($key);
                }
                if($url == null) {
                    $ext = UTIL_File::getExtension($key);
                    $type = $urlArray['2'];
                    if(defined('OW_DIR_USERFILES_SAAS')){
                        $type = $urlArray['3'];
                    }
                    $path = $params['path'];
                    $path = str_replace(OW_DIR_ROOT, '', $path);
                    $hash = FRMSecurityProvider::generateUniqueId($type . '_' . UTIL_String::getRandomString(20, 2)) . '.' . $ext;
                    $url = $this->addUrl($key, $hash, $type, $path);
                }
                $hash = $url->hash;
                $newUrl = OW::getRouter()->urlForRoute('frmsecurefileurl.process_file', array('hash' => $hash));
                $event->setData(array('url' => $newUrl));
            }
        }
    }

}
