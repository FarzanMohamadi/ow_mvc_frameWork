<?php
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Handler\CurlHandler;
use GuzzleHttp\Handler\StreamHandler;
use GuzzleHttp\RequestOptions;

/**
 * @package ow_utilities
 * @since 1.8.1
 */
class UTIL_HttpClient
{
    const HTTP_STATUS_OK = 200;
    const CONNECTION_TIMEOUT = 20;

    /**
     * @var GuzzleHttp\Client
     */
    private static $client;

    /**
     * @param string $url
     * @param UTIL_HttpClientParams $params
     * @return UTIL_HttpClientResponse
     */
    public static function get( $url, UTIL_HttpClientParams $params = null )
    {
        $options = $params ? $params->getOptions() : array();

        if ( !empty($options["params"]) )
        {
            $options[RequestOptions::QUERY] = $options["params"];
        }

        return self::request("GET", $url, $options);
    }

    /**
     * @param string $url
     * @param UTIL_HttpClientParams $params
     * @return UTIL_HttpClientResponse
     */
    public static function post( $url, UTIL_HttpClientParams $params = null )
    {
        $options = $params ? $params->getOptions() : array();

        if ( !empty($options["params"]) )
        {
            $options[RequestOptions::FORM_PARAMS] = $options["params"];
        }

        return self::request("POST", $url, $options);
    }
    /* --------------------------------------------------------------------- */

    private static function getClient()
    {
        if ( self::$client == null )
        {
            $handler = function_exists("curl_version") ? new CurlHandler() : new StreamHandler();

            self::$client = new Client(array(
                "request.options" => array(
                    "exceptions" => false,
                ),
                "handler" => HandlerStack::create($handler)
            ));
        }

        return self::$client;
    }

    private static function request( $method, $url, array $options )
    {
        $options[RequestOptions::VERIFY] = false;
        if(!isset($options[RequestOptions::CONNECT_TIMEOUT])) {
            $options[RequestOptions::CONNECT_TIMEOUT] = self::CONNECTION_TIMEOUT;
        }
        try
        {
            $response = self::getClient()->request($method, $url, $options);
        }
        catch ( Exception $e )
        {
            return null;
        }

        return new UTIL_HttpClientResponse($response);
    }
}
