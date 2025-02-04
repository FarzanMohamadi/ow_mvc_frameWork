<?php
/**
 * Dispatcher handles request after routing process,
 * i.e. creates instance of controller and calls action using provided params.
 *
 * @package ow_core
 * @method static OW_RequestHandler getInstance()
 * @since 1.0
 */
class OW_RequestHandler
{
    const ATTRS_KEY_CTRL = 'controller';
    const ATTRS_KEY_ACTION = 'action';
    const ATTRS_KEY_VARLIST = 'params';
    const CATCH_ALL_REQUEST_KEY_CTRL = 'controller';
    const CATCH_ALL_REQUEST_KEY_ACTION = 'action';
    const CATCH_ALL_REQUEST_KEY_REDIRECT = 'redirect';
    const CATCH_ALL_REQUEST_KEY_JS = 'js';
    const CATCH_ALL_REQUEST_KEY_ROUTE = 'route';
    const CATCH_ALL_REQUEST_KEY_PARAMS = 'params';

    use OW_Singleton;
    
    /**
     * @var array
     */
    protected $handlerAttributes;

    /**
     * @var array
     */
    protected $indexPageAttributes;

    /**
     * @var array
     */
    protected $staticPageAttributes;

    /**
     * @var array
     */
    protected $catchAllRequestsAttributes = array();

    /**
     * @var array
     */
    protected $catchAllRequestsExcludes = array();

    /**
     * @return array
     */
    public function getCatchAllRequestsAttributes( $key )
    {
        return !empty($this->catchAllRequestsAttributes[$key]) ? $this->catchAllRequestsAttributes[$key] : null;
    }

    /**
     * <controller> <action> <params> <route> <redirect> <js>
     *
     * @param array $attributes 
     */
    public function setCatchAllRequestsAttributes( $key, array $attributes )
    {
        $this->catchAllRequestsAttributes[$key] = $attributes;

        $this->addCatchAllRequestsExclude($key, $attributes[self::ATTRS_KEY_CTRL], $attributes[self::ATTRS_KEY_ACTION]);
    }

    /**
     *
     * @param string $controller
     * @param string $action
     */
    public function addCatchAllRequestsExclude( $key, $controller, $action = null, $params = null )
    {
        if ( empty($this->catchAllRequestsExcludes[$key]) )
        {
            $this->catchAllRequestsExcludes[$key] = array();
        }

        $this->catchAllRequestsExcludes[$key][] = array(self::CATCH_ALL_REQUEST_KEY_CTRL => $controller, self::CATCH_ALL_REQUEST_KEY_ACTION => $action,
            self::CATCH_ALL_REQUEST_KEY_PARAMS => $params);
    }

    /**
     * @return array
     */
    public function getIndexPageAttributes()
    {
        return $this->indexPageAttributes;
    }

    /**
     * @param string $controller
     * @param string $action
     */
    public function setIndexPageAttributes( $controller, $action = 'index' )
    {
        $this->indexPageAttributes = array(self::ATTRS_KEY_CTRL => $controller, self::ATTRS_KEY_ACTION => $action);
    }

    /**
     * @return array
     */
    public function getStaticPageAttributes()
    {
        return $this->staticPageAttributes;
    }

    /**
     * @param string $controller
     * @param string $action
     */
    public function setStaticPageAttributes( $controller, $action = 'index' )
    {
        $this->staticPageAttributes = array(self::ATTRS_KEY_CTRL => $controller, self::ATTRS_KEY_ACTION => $action);
    }

    /**
     * @return array
     */
    public function getHandlerAttributes()
    {
        return $this->handlerAttributes;
    }

    /**
     * @param array $attributes
     * @throws Redirect404Exception
     */
    public function setHandlerAttributes( array $attributes )
    {
        if ( empty($attributes[OW_Route::DISPATCH_ATTRS_CTRL]) )
        {
            throw new Redirect404Exception();
        }

        $this->handlerAttributes = array(
            self::ATTRS_KEY_CTRL => trim($attributes[OW_Route::DISPATCH_ATTRS_CTRL]),
            self::ATTRS_KEY_ACTION => ( empty($attributes[OW_Route::DISPATCH_ATTRS_ACTION]) ? null : trim($attributes[OW_Route::DISPATCH_ATTRS_ACTION]) ),
            self::ATTRS_KEY_VARLIST => ( empty($attributes[OW_Route::DISPATCH_ATTRS_VARLIST]) ? array() : $attributes[OW_Route::DISPATCH_ATTRS_VARLIST])
        );
    }

    /**
     * @throws Redirect404Exception
     */
    public function dispatch()
    {
        if ( empty($this->handlerAttributes[self::ATTRS_KEY_CTRL]) )
        {
            throw new InvalidArgumentException("Cant dispatch request! Empty or invalid controller class provided!");
        }
        // set uri params in request object
        if ( !empty($this->handlerAttributes[self::ATTRS_KEY_VARLIST]) )
        {
            OW::getRequest()->setUriParams($this->handlerAttributes[self::ATTRS_KEY_VARLIST]);
        }

        $catchAllRequests = $this->processCatchAllRequestsAttrs();

        if ( $catchAllRequests !== null )
        {
            $this->handlerAttributes = $catchAllRequests;
        }

        /* @var $controller OW_ActionController */
        try
        {
            $controller = OW::getClassInstance($this->handlerAttributes[self::ATTRS_KEY_CTRL]);

            if ( empty($this->handlerAttributes[self::ATTRS_KEY_ACTION]) )
            {
                $this->handlerAttributes[self::ATTRS_KEY_ACTION] = $controller->getDefaultAction();
            }

            $action = new ReflectionMethod(get_class($controller), $this->handlerAttributes[self::ATTRS_KEY_ACTION]);
        }
        catch ( ReflectionException $e )
        {
            throw new Redirect404Exception();
        }

        // check if controller exists and is instance of base action controller class
        if ( !$this->checkControllerInstance($controller) )
        {
            throw new LogicException("Cant dispatch request!Please provide valid controller class!");
        }

        // call optional init method
        $controller->init();

        $this->processControllerAction($action, $controller);
    }

    /**
     * @param $controller
     * @return bool
     */
    protected function checkControllerInstance( $controller )
    {
        return $controller != null & $controller instanceof OW_ActionController;
    }

    /**
     * @param ReflectionMethod $action
     * @param OW_ActionController $controller
     */
    protected function processControllerAction( $action, $controller )
    {
        $args = array(
            self::ATTRS_KEY_VARLIST =>
            empty($this->handlerAttributes[self::ATTRS_KEY_VARLIST]) ? array() : $this->handlerAttributes[self::ATTRS_KEY_VARLIST]
        );
        OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_BEFORE_CONTROLLERS_INVOKE));
        OW::getEventManager()->trigger(new OW_Event("core.performance_test",
            array("key" => "controller_call.start", "handlerAttrs" => $this->handlerAttributes)));
        $action->invokeArgs($controller, $args);
        OW::getEventManager()->trigger(new OW_Event("core.performance_test",
            array("key" => "controller_call.end", "handlerAttrs" => $this->handlerAttributes)));
        // set default template for controller action if template wasn"t set
        if ( $controller->getTemplate() === null )
        {
            $controller->setTemplate($this->getControllerActionDefaultTemplate($controller));
        }
        OW::getDocument()->setBody($controller->render());
    }

    /**
     * Returns template path for provided controller and action.
     *
     * @param OW_ActionController $ctrl
     * @return string
     */
    protected function getControllerActionDefaultTemplate( OW_ActionController $ctrl )
    {
        $plugin = OW::getPluginManager()->getPlugin(OW::getAutoloader()->getPluginKey($this->handlerAttributes[self::ATTRS_KEY_CTRL]));

        $templateFilename = OW::getAutoloader()->classToFilename($this->handlerAttributes[self::ATTRS_KEY_CTRL], false) . '_'
            . OW::getAutoloader()->classToFilename(ucfirst($this->handlerAttributes[self::ATTRS_KEY_ACTION]), false) . '.html';

        return ( $ctrl instanceof OW_MobileActionController ? $plugin->getMobileCtrlViewDir() : $plugin->getCtrlViewDir() ) . $templateFilename;
    }

    /**
     * Returns processed catch all requests attributes.
     *
     * @return string
     */
    protected function processCatchAllRequestsAttrs()
    {
        if ( empty($this->catchAllRequestsAttributes) )
        {
            return null;
        }

        $catchRequest = true;

        $lastKey = array_search(end($this->catchAllRequestsAttributes), $this->catchAllRequestsAttributes);

        foreach ( $this->catchAllRequestsExcludes[$lastKey] as $exclude )
        {
            if ( $exclude[self::CATCH_ALL_REQUEST_KEY_CTRL] == $this->handlerAttributes[self::ATTRS_KEY_CTRL] )
            {
                if ( $exclude[self::CATCH_ALL_REQUEST_KEY_ACTION] === null || $exclude[self::CATCH_ALL_REQUEST_KEY_ACTION] == $this->handlerAttributes[self::ATTRS_KEY_ACTION] )
                {
                    if ( $exclude[self::CATCH_ALL_REQUEST_KEY_PARAMS] === null || $exclude[self::CATCH_ALL_REQUEST_KEY_PARAMS] == $this->handlerAttributes[self::ATTRS_KEY_VARLIST] )
                    {
                        $catchRequest = false;
                        break;
                    }
                }
            }
        }
        if ( $catchRequest )
        {
            if ( isset($this->catchAllRequestsAttributes[$lastKey][self::CATCH_ALL_REQUEST_KEY_REDIRECT]) && (bool) $this->catchAllRequestsAttributes[$lastKey][self::CATCH_ALL_REQUEST_KEY_REDIRECT] )
            {
                $route = isset($this->catchAllRequestsAttributes[$lastKey][self::CATCH_ALL_REQUEST_KEY_ROUTE]) ? trim($this->catchAllRequestsAttributes[$lastKey][self::CATCH_ALL_REQUEST_KEY_ROUTE]) : null;

                $params = isset($this->catchAllRequestsAttributes[$lastKey][self::CATCH_ALL_REQUEST_KEY_PARAMS]) ? $this->catchAllRequestsAttributes[$lastKey][self::CATCH_ALL_REQUEST_KEY_PARAMS] : array();

                $redirectUrl = ($route !== null) ?
                    OW::getRouter()->urlForRoute($route, $params) :
                    OW::getRouter()->urlFor($this->catchAllRequestsAttributes[$lastKey][self::CATCH_ALL_REQUEST_KEY_CTRL],
                        $this->catchAllRequestsAttributes[$lastKey][self::CATCH_ALL_REQUEST_KEY_ACTION], $params);

                $redirectUrl = OW::getRequest()->buildUrlQueryString($redirectUrl,
                    array('back_uri' => OW::getRequest()->getRequestUri()));

                if ( isset($this->catchAllRequestsAttributes[$lastKey][self::CATCH_ALL_REQUEST_KEY_JS]) && (bool) $this->catchAllRequestsAttributes[$lastKey][self::CATCH_ALL_REQUEST_KEY_JS] )
                {
                    // TODO resolve hotfix
                    // hotfix for splash screen + members only case
                    if ( array_key_exists('base.members_only', $this->catchAllRequestsAttributes) )
                    {
                        if ( in_array($this->handlerAttributes[self::CATCH_ALL_REQUEST_KEY_CTRL],
                                array('BASE_CTRL_User', 'BASE_MCTRL_User')) && $this->handlerAttributes[self::CATCH_ALL_REQUEST_KEY_ACTION] === 'standardSignIn' )
                        {
                            $backUri = isset($_GET['back_uri']) ? $_GET['back_uri'] : OW::getRequest()->getRequestUri();
                            OW::getDocument()->addOnloadScript("window.location = '" . OW::getRequest()->buildUrlQueryString($redirectUrl,
                                    array('back_uri' => $backUri)) . "'");
                            return null;
                        }
                        else
                        {
                            $ru = OW::getRequest()->buildUrlQueryString(OW::getRouter()->urlForRoute('static_sign_in'),
                                array('back_uri' => OW::getRequest()->getRequestUri()));
                            OW::getApplication()->redirect($ru);
                        }
                    }

                    OW::getDocument()->addOnloadScript("window.location = '" . $redirectUrl . "'");
                    return null;
                }

                UTIL_Url::redirect($redirectUrl);
            }

            return $this->getCatchAllRequestsAttributes($lastKey);
        }

        return null;
    }
}
