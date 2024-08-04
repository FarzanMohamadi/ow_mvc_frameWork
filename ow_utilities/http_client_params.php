<?php
use GuzzleHttp\RequestOptions;

/**
 * @package ow_utilities
 * @since 1.8.1
 */
class UTIL_HttpClientParams
{
    /**
     * @var array
     */
    private $options = array("params" => array());

    public function __construct()
    {
        
    }

    /**
     * @param bool $allowRedirects
     */
    public function setAllowRedirects( $allowRedirects )
    {
        $this->options[RequestOptions::ALLOW_REDIRECTS] = (bool) $allowRedirects;
    }

    /**
     * @param int $timeout
     */
    public function setTimeout( $timeout )
    {
        $this->options[RequestOptions::CONNECT_TIMEOUT] = $timeout;
    }

    /**
     * @param string $headerName
     * @param string $headerVal
     */
    public function setHeader( $headerName, $headerVal )
    {
        if (!array_key_exists(RequestOptions::HEADERS, $this->options) )
        {
            $this->options[RequestOptions::HEADERS] = array();
        }

        $this->options[RequestOptions::HEADERS][trim($headerName)] = trim($headerVal);
    }

    /**
     * @param array $headers
     */
    public function setHeaders( array $headers )
    {
        foreach ( $headers as $name => $val )
        {
            $this->setHeader($name, $val);
        }
    }

    /**
     * @param string $body
     */
    public function setBody( $body )
    {
        $this->options[RequestOptions::BODY] = trim($body);
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @param string $name
     * @param string $val
     */
    public function addParam( $name, $val )
    {
        $this->options["params"][trim($name)] = trim($val);
    }

    /***
     * @param $value
     */
    public function setJson($value)
    {
        $this->options["json"] = $value;
    }

    /**
     * @param array $params
     */
    public function addParams( array $params )
    {
        foreach ( $params as $name => $val )
        {
            $this->addParam($name, $val);
        }
    }
}
