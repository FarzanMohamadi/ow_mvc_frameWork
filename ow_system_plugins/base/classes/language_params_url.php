<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_utilities
 * @since 1.8.1
 */

class BASE_CLASS_LanguageParamsUrl extends BASE_CLASS_LanguageParams {

    protected $route;
    protected $controller;
    protected $action;
    protected $params = array();

    /**
     * @param string $route
     * @param array $params
     */

    public function setRoute( $route, $params = array() ) {
        $this->route = $route;
        $this->params = $params;
    }

    /**
     * @param string $controller
     * @param string $action
     * @param array $params
     */

    public function setActionController( $controller, $action, $params = array() ) {
        $this->controller = $controller;
        $this->action = $action;
        $this->params = $params;
    }

    /**
     * String representation of object
     * @link http://php.net/manual/en/serializable.serialize.php
     * @return string the string representation of the object or null
     * @since 5.1.0
     */
    public function serialize()
    {
        return serialize(array('route' => $this->route, 'controller' =>$this->controller, 'action' => $this->action, 'params' => $this->params ));
    }

    /**
     * Constructs the object
     * @link http://php.net/manual/en/serializable.unserialize.php
     * @param string $serialized <p>
     * The string representation of the object.
     * </p>
     * @return void
     * @since 5.1.0
     */
    public function unserialize($serialized)
    {
        $data = unserialize($serialized);

        if ( !empty($data) ) {
            $this->route = !empty($data['route']) ? $data['route'] : null;
            $this->controller = !empty($data['controller']) ? $data['controller'] : null;
            $this->action = !empty($data['action']) ? $data['action'] : null;
            $this->params = !empty($data['params']) ? $data['params'] : array();
        }
    }

    public function fetch()
    {
        if ( !empty($this->route) ) {
            return OW::getRouter()->urlForRoute($this->route, $this->params);
        }

        if ( !empty($this->controller) && !empty($this->action)  ) {
            return OW::getRouter()->urlForRoute($this->controller, $this->action, $this->params);
        }

        return null;
    }
}