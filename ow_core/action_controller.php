<?php
/**
 * The base class for all action controllers.
 *
 * @package ow_core
 * @since 1.0
 */
abstract class OW_ActionController extends OW_Renderable
{
    /**
     * Default controller action (used if action isn't provided).
     *
     * @var string
     */
    protected $defaultAction = 'index';

    /**
     * Constructor.
     */
    public function __construct()
    {
        
    }

    /**
     * @return string
     */
    public function getDefaultAction()
    {
        return $this->defaultAction;
    }

    /**
     * @param string $action
     */
    public function setDefaultAction( $action )
    {
        $this->defaultAction = trim($action);
    }

    /**
     * Makes permanent redirect to the same controller and provided action.
     *
     * @param string $action
     */
    public function redirectToAction( $action )
    {
        $handlerAttrs = OW::getRequestHandler()->getHandlerAttributes();

        OW::getApplication()->redirect(OW::getRouter()->uriFor($handlerAttrs['controller'], trim($action)));
    }

    /**
     * Makes permanent redirect to provided URL or URI.
     *
     * @param string $redirectTo
     */
    public function redirect( $redirectTo = null )
    {
        OW::getApplication()->redirect($redirectTo);
    }

    /**
     * Optional method. Called before action.
     */
    public function init()
    {
        
    }

    /**
     * Sets custom document key for current page.
     *
     * @param string $key
     */
    public function setDocumentKey( $key )
    {
        OW::getApplication()->setDocumentKey($key);
    }

    /**
     * Returns document key for current page.
     * 
     * @return string
     */
    public function getDocumentKey()
    {
        return OW::getApplication()->getDocumentKey();
    }

    /**
     * Sets page heading.
     * @param string $heading
     */
    public function setPageHeading( $heading )
    {
        OW::getDocument()->setHeading(trim($heading));
    }

    /**
     * Sets page heading icon class.
     *
     * @param string $class
     */
    public function setPageHeadingIconClass( $class )
    {
        OW::getDocument()->setHeadingIconClass($class);
    }

    /**
     * @param string $title
     */
    public function setPageTitle( $title )
    {
        OW::getDocument()->setTitle(trim($title));
    }

    /**
     * @param string $desc
     */
    public function setPageDescription( $desc )
    {
        OW::getDocument()->setDescription($desc);
    }

    /**
     * @param array $keywords
     */
    public function setKeywords( $keywords )
    {
        OW::getDocument()->setKeywords($keywords);
    }
}
