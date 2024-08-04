<?php
/**
 * Base class for renderable elements. Allows to assign vars and compile HTML using template engine.
 *
 * @package ow_core
 * @since 1.0
 */
abstract class OW_Renderable extends OW_View
{
    /**
     * List of added components.
     *
     * @var array
     */
    protected $components = array();

    /**
     * List of registered forms.
     *
     * @var array
     */
    protected $forms = array();

    /**
     * Constructor.
     */
    protected function __construct()
    {
        
    }

    /**
     * Adds component to renderable object.
     *
     * @param string $key
     * @param OW_Renderable $component
     */
    public function addComponent( $key, OW_Renderable $component )
    {
        $this->components[$key] = $component;
    }

    /**
     * Returns added component by key.
     *
     * @param string $key
     * @return OW_Component
     */
    public function getComponent( $key )
    {
        return ( isset($this->components[$key]) ? $this->components[$key] : null );
    }

    /**
     * Deletes added component.
     *
     * @param string $key
     */
    public function removeComponent( $key )
    {
        if ( isset($this->components[$key]) )
        {
            unset($this->components[$key]);
        }
    }

    /**
     * Adds form to renderable object.
     *
     * @param Form $form
     */
    public function addForm( Form $form )
    {
        $this->forms[$form->getName()] = $form;
    }

    /**
     * Returns added form by key.
     *
     * @param string $key
     * @return Form
     */
    public function getForm( $name )
    {
        return ( isset($this->forms[$name]) ? $this->forms[$name] : null );
    }

    protected function onRender()
    {
        parent::onRender();

        $viewRenderer = OW_ViewRenderer::getInstance();

        if ( !empty($this->components) )
        {
            $renderedCmps = array();

            foreach ( $this->components as $key => $value )
            {
                $renderedCmps[$key] = $value->isVisible() ? $value->render() : '';
            }

            $viewRenderer->assignVars($renderedCmps);
        }

        if ( !empty($this->forms) )
        {
            $viewRenderer->assignVar("_owForms_", $this->forms);
        }
    }
}
