<?php
/**
 * @package ow_utilities
 * @since 1.0
 */
class UTIL_HttpClientResponse
{
    private $resultBody;

    /**
     * @var Psr\Http\Message\ResponseInterface
     */
    private $response;

    /**
     * 
     * @param Psr\Http\Message\ResponseInterface $response
     */
    public function __construct( $response )
    {
        $this->response = $response;
        $this->resultBody = $this->response->getBody()->getContents();
    }

    /**
     * @param string $name
     * @return string
     */
    public function getHeader( $name )
    {
        return $this->response->getHeader($name);
    }

    /**
     * @param string $name
     * @return bool
     */
    public function hasHeader( $name )
    {
        return $this->response->hasHeader($name);
    }

    /**
     * @return array
     */
    public function getHeaders()
    {
        return $this->response->getHeaders();
    }

    /**
     * @return int
     */
    public function getStatusCode()
    {
        return $this->response->getStatusCode();
    }

    /**
     * @return string
     */
    public function getBody()
    {
        return $this->resultBody;
    }
}