<?php
/**
 * Exceptions.
 *
 * @package core
 * @since 1.0
 */

/**
 * Redirect exception forces 301 http redirect.
 */
class RedirectException extends Exception
{
    /**
     * @var string
     */
    private $url;
    /**
     * @var integer
     */
    private $redirectCode;
    /**
     * @var mixed
     */
    private $data;

    /**
     * Constructor.
     *
     * @param string $url
     */
    public function __construct( $url, $code = null )
    {
        parent::__construct('', 0);
        $this->url = $url;
        $this->redirectCode = ( empty($code) ? 301 : (int) $code );
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @return integer
     */
    public function getRedirectCode()
    {
        return $this->redirectCode;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param mixed $data
     */
    public function setData( $data )
    {
        $this->data = $data;
    }
}

class InterceptException extends Exception
{
    private $handlerAttrs;

    public function __construct( $attrs )
    {
        $this->handlerAttrs = $attrs;
    }

    public function getHandlerAttrs()
    {
        return $this->handlerAttrs;
    }
}

class AuthorizationException extends InterceptException
{

    /**
     * Constructor.
     */
    public function __construct( $message = null )
    {
        $route = OW::getRouter()->getRoute('base_page_auth_failed');
        $params = $route === null ? array('controller' => 'BASE_CTRL_BaseDocument', 'action' => 'authorizationFailed') : $route->getDispatchAttrs();
        $params[OW_Route::DISPATCH_ATTRS_VARLIST]['message'] = $message;
        parent::__construct($params);
    }
}



/**
 * Page not found 404 redirect exception.
 */
class Redirect404Exception extends InterceptException
{

    /**
     * Constructor.
     */
    public function __construct()
    {
        if(!OW::getUser()->isAuthenticated()){
            $route = OW::getRouter()->getRoute('static_sign_in');
            if ( (int) OW::getConfig()->getValue('base', 'who_can_join') === BOL_UserService::PERMISSIONS_JOIN_BY_INVITATIONS )
            {
                $route = OW::getRouter()->getRoute('base_page_404');
                $params = $route === null ? array('controller' => 'BASE_CTRL_BaseDocument', 'action' => 'page404') : $route->getDispatchAttrs();
            }else{
                $params = $route === null ? array('controller' => 'BASE_CTRL_User', 'action' => 'standardSignIn') : $route->getDispatchAttrs();
            }
            OW::getResponse()->setHeader('HTTP/1.0', '404 Not Found');
            OW::getResponse()->setHeader('Status', '404 Not Found');
        }else{
            $route = OW::getRouter()->getRoute('base_page_404');
            $params = $route === null ? array('controller' => 'BASE_CTRL_BaseDocument', 'action' => 'page404') : $route->getDispatchAttrs();
        }
        parent::__construct($params);
    }
}

/**
 * Internal server error redirect exception.
 */
class Redirect500Exception extends RedirectException
{

    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct(OW_URL_HOME . '500.phtml', 500);
    }
}

/**
 * @deprecated Use Redirect403Exception for security reasons
 * Forbidden 403 redirect exception.
 */
class Redirect403Exception extends InterceptException
{

    /**
     * Constructor.
     */
    public function __construct( $message = null )
    {
        $route = OW::getRouter()->getRoute('base_page_403');
        $params = $route === null ? array('controller' => 'BASE_CTRL_BaseDocument', 'action' => 'page403') : $route->getDispatchAttrs();
        $params[OW_Route::DISPATCH_ATTRS_VARLIST]['message'] = $message;
        parent::__construct($params);
    }
}

/**
 * Blank confirm page redirect exception.
 */
class RedirectConfirmPageException extends RedirectException
{

    /**
     * Constructor.
     */
    public function __construct( $message )
    {
        parent::__construct(OW::getRequest()->buildUrlQueryString(OW::getRouter()->urlForRoute('base_page_confirm'), array('back_uri' => urlencode(OW::getRequest()->getRequestUri()))));
        OW::getSession()->set('baseConfirmPageMessage', $message);
    }
}

/**
 * Blank message page redirect exception.
 */
class RedirectAlertPageException extends RedirectException
{

    /**
     * Constructor.
     */
    public function __construct( $message )
    {
        parent::__construct(OW::getRouter()->urlForRoute('base_page_alert'));
        OW::getSession()->set('baseAlertPageMessage', $message);
    }
}

/**
 * Sign in page redirect exception.
 */
class AuthenticateException extends RedirectException
{

    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct(OW::getRequest()->buildUrlQueryString(OW::getRouter()->urlForRoute('static_sign_in'), array('back-uri' => urlencode(OW::getRequest()->getRequestUri()))));
    }
}
