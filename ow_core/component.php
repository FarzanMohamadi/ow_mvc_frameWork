<?php
/**
 * OW_Component is the base class for all components (represents blocks of rendered markup).
 *
 * @package ow_core
 * @since 1.0
 */
abstract class OW_Component extends OW_Renderable
{

    /**
     * Constructor.
     *
     * @param string $template
     */
    public function __construct( $template = null )
    {
        parent::__construct();

        // TODO remove everthing from constructor
        try
        {
            $plugin = OW::getPluginManager()->getPlugin(OW::getAutoloader()->getPluginKey(get_class($this)));
        }
        catch ( InvalidArgumentException $e )
        {
            $plugin = null;
        }

        if ( $template !== null && $plugin !== null )
        {
            $this->setTemplate($plugin->getCmpViewDir() . $template . '.html');
        }
    }

    public function render()
    {
        if ( $this->getTemplate() === null )
        {
            try
            {
                $plugin = OW::getPluginManager()->getPlugin(OW::getAutoloader()->getPluginKey(get_class($this)));
            }
            catch ( InvalidArgumentException $e )
            {
                $plugin = null;
            }

            if ( $plugin !== null )
            {
                $template = OW::getAutoloader()->classToFilename(get_class($this), false);
                $this->setTemplate($plugin->getCmpViewDir() . $template . '.html');
            }
        }

        return parent::render();
    }
}