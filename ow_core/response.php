<?php
/**
 * @package ow_core
 * @method static OW_Response getInstance()
 * @since 1.0
 */
class OW_Response
{
    /**
     * HTTP Header constants
     */
    const HD_CACHE_CONTROL = 'Cache-Control';
    const HD_CNT_DISPOSITION = 'Content-Disposition';
    const HD_CNT_LENGTH = 'Content-Length';
    const HD_CONNECTION = 'Connection';
    const HD_PRAGMA = 'Pragma';
    const HD_CNT_TYPE = 'Content-Type';
    const HD_EXPIRES = 'Expires';
    const HD_LAST_MODIFIED = 'Last-Modified';
    const HD_LOCATION = 'Location';

    use OW_Singleton;
    
    /**
     * Headers to send with response
     *
     * @var array
     */
    private $headers = array();

    /**
     * Document to send
     *
     * @var OW_Document
     */
    private $document;

    /**
     * Rendered markup
     *
     * @var string
     */
    private $markup = '';

    /**
     * @return OW_Document
     */
    public function getDocument()
    {
        return $this->document;
    }

    /**
     * @param OW_Document $document
     */
    public function setDocument( OW_Document $document )
    {
        $this->document = $document;
    }

    /**
     * Adds headers to response.
     *
     * @param string $name
     * @param string $value
     */
    public function setHeader( $name, $value )
    {
        $this->headers[trim($name)] = trim($value);
    }

    /**
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * @param array $headers
     */
    public function setHeaders( array $headers )
    {
        $this->headers = $headers;
    }

    /**
     * Clears all headers.
     */
    public function clearHeaders()
    {
        $this->headers = array();
    }

    /**
     * Sends all added headers.
     */
    public function sendHeaders()
    {
        if ( !headers_sent() )
        {
            foreach ( $this->headers as $headerName => $headerValue )
            {
                if ( substr(mb_strtolower($headerName), 0, 4) === 'http' )
                {
                    header($headerName . ' ' . $headerValue);
                }
                else if ( mb_strtolower($headerName) === 'status' )
                {
                    header(ucfirst(mb_strtolower($headerName)) . ': ' . $headerValue, null, (int) $headerValue);
                }
                else
                {
                    header($headerName . ':' . $headerValue);
                }
            }
        }
    }

    /**
     * @return string
     */
    public function getMarkup()
    {
        return $this->markup;
    }

    /**
     * @param string $markup
     */
    public function setMarkup( $markup )
    {
        $this->markup = $markup;
    }

    /**
     * Sends generated response
     *
     */
    public function respond()
    {
        $event = new OW_Event(OW_EventManager::ON_BEFORE_DOCUMENT_RENDER);
        OW::getEventManager()->trigger($event);
        if ( $this->document !== null )
        {
            $renderedMarkup = $this->document->render();

            $event = new BASE_CLASS_EventCollector('base.append_markup');
            OW::getEventManager()->trigger($event);
            $data = $event->getData();
            $this->markup = str_replace(OW_Document::APPEND_PLACEHOLDER, PHP_EOL . implode(PHP_EOL, $data), $renderedMarkup);
        }

        $event = new OW_Event(OW_EventManager::ON_AFTER_DOCUMENT_RENDER);
        OW::getEventManager()->trigger($event);

        $this->sendHeaders();

        if ( OW::getRequest()->isAjax() )
        {
            exit();
        }

        if ( OW_PROFILER_ENABLE )
        {
            UTIL_Profiler::getInstance()->mark('final');
        }

        if ( OW_DEBUG_MODE )
        {
            // This code does not clear the buffer
            // It may lead to double printing of some outputs
            // Discussed in #9070
            echo ob_get_contents();
        }

        echo $this->markup;

        $event = new OW_Event('core.exit');
        OW::getEventManager()->trigger($event);
    }
}
