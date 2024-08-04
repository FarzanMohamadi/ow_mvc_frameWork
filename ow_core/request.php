<?php
/**
 * @package ow_core
 * @method static OW_Request getInstance()
 * @since 1.0
 */
class OW_Request
{
    use OW_Singleton;
    
    /**
     * Request uri.
     *
     * @var string
     */
    private $uri;
    private $uriParams;

    /**
     * Constructor.
     */
    private function __construct()
    {
        $_GET = $this->customeEscape($_GET);
        $_POST = $this->customeEscape($_POST);
    }

    /**
     * @return array
     */
    public function getUriParams()
    {
        return $this->uriParams;
    }

    /**
     * @param array $uriParams
     */
    public function setUriParams( array $uriParams )
    {
        $this->uriParams = $uriParams;
    }

    /**
     * Returns real request uri.
     *
     * @return string
     */
    public function getRequestUri()
    {
        if ( $this->uri === null )
        {
            $this->uri = UTIL_Url::getRealRequestUri(OW::getRouter()->getBaseUrl(), $_SERVER['REQUEST_URI']);
        }

        return $this->uri;
    }

    /**
     * Returns remote ip address.
     *
     * @return string
     */
    public function getRemoteAddress()
    {
        if(isset($_SERVER['HTTP_X_FORWARDED_FOR'])){
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        }
        if(isset($_SERVER['HTTP_X_REAL_IP'])){
            return $_SERVER['HTTP_X_REAL_IP'];
        }
        if(isset($_SERVER['REMOTE_ADDR'])){
            return $_SERVER['REMOTE_ADDR'];
        }
        return '';
    }

    /**
     * Returns request type.
     *
     * @return string
     */
    public function getRequestType()
    {
        return mb_strtoupper(isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET');
    }
//    public function getContentType()
//    {
//        return $_SERVER[''];
//    }

    /**
     * Indicates if request is ajax.
     *
     * @return boolean
     */
    public function isAjax()
    {
        return ( isset($_SERVER['HTTP_X_REQUESTED_WITH']) && mb_strtoupper($_SERVER['HTTP_X_REQUESTED_WITH']) === 'XMLHTTPREQUEST' );
    }

    /**
     * Indicates if request is post.
     *
     * @return boolean
     */
    public function isPost()
    {
        return ( mb_strtoupper($_SERVER['REQUEST_METHOD']) === 'POST' );
    }

    /**
     * Returns request agent name.
     *
     * @return string
     */
    public function getUserAgentName()
    {
        $userAgent = null;
        if(isset($_SERVER['HTTP_USER_AGENT'])) {
            $userAgent = $_SERVER['HTTP_USER_AGENT'];
        }
        return UTIL_Browser::getBrowser($userAgent);
    }

    /**
     * Returns user agent version;
     *
     * @return string
     */
    public function getUserAgentVersion()
    {
        return UTIL_Browser::getVersion($_SERVER['HTTP_USER_AGENT']);
    }

    /**
     * Returns request agent platform.
     *
     * @return string
     */
    public function getUserAgentPlatform()
    {
        return UTIL_Browser::getPlatform($_SERVER['HTTP_USER_AGENT']);
    }

    /**
     * Indicates if user agent is mobile.
     *
     * @return boolean
     */
    public function isMobileUserAgent()
    {
        return UTIL_Browser::isMobile($_SERVER['HTTP_USER_AGENT']);
    }

    /**
     * Builds and updates url query string.
     *
     * @param string $url
     * @param array $paramsToUpdate
     * @param string $anchor
     * @return string
     */
    public function buildUrlQueryString( $url = null, array $paramsToUpdate = array(), $anchor = null )
    {
        $url = ( $url === null ) ? OW_URL_HOME . $this->getRequestUri() : trim($url);

        $requestUrlArray = parse_url($url);

        $currentParams = array();

        if ( isset($requestUrlArray['query']) )
        {
            parse_str($requestUrlArray['query'], $currentParams);
        }

        $currentParams = array_merge($currentParams, $paramsToUpdate);

        $scheme = empty($requestUrlArray["scheme"]) ? "" : $requestUrlArray["scheme"] . ":";
        $host = empty($requestUrlArray["host"]) ? "" : "//" . $requestUrlArray["host"];
        $port = empty($requestUrlArray["port"]) ? "" : ":" . (int) $requestUrlArray["port"];
        $path = empty($requestUrlArray["path"]) ? "" : $requestUrlArray["path"];
        $queryString = empty($currentParams) ? "" : "?" . http_build_query($currentParams);
        $anchor = ($anchor === null) ? "" : "#" . trim($anchor);

        return $scheme . $host . $port . $path . $queryString . $anchor;
    }

    /**
     * @param array $value
     * @return array
     */
    private function stripSlashesRecursive( $value )
    {
        $value = is_array($value) ? array_map(array($this, 'stripSlashesRecursive'), $value) : stripslashes($value);
        return $value;
    }

    /***
     * Dirty hack to prevent SQLi on insecure third-party codes
     * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
     * @param $values
     * @return mixed
     */
    private function customeEscape($values){
        foreach($values as $key=>$value) {
            if (in_array($key, ['site_path'])){
                continue;
            }
            if(is_string($value) && preg_match("/[\']|\\\\$/", $value)) {
                $value = str_replace("'", '&#39;', $value);
                $value = rtrim($value, "\\");
                $values[$key] = $value;
            }

        }
        return $values;
    }

    public function isSsl()
    {
        $isHttps = null;

        if ( array_key_exists("HTTPS", $_SERVER) )
        {
            $isHttps = ($_SERVER["HTTPS"] == "on");
        }
        else if ( array_key_exists("REQUEST_SCHEME", $_SERVER) )
        {
            $isHttps = (strtolower($_SERVER["REQUEST_SCHEME"]) == "https");
        }
        else if ( array_key_exists("HTTP_X_FORWARDED_PROTO", $_SERVER) )
        {
            $isHttps = ($_SERVER["HTTP_X_FORWARDED_PROTO"] == "https");
        }
        else if ( array_key_exists("SERVER_PORT", $_SERVER) )
        {
            $isHttps = ($_SERVER["SERVER_PORT"] == "443");
        }

        return $isHttps;
    }
}
