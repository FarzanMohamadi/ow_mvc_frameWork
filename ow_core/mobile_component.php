<?php
/**
 * OW_Component is the base class for all components (represents blocks of rendered markup).
 *
 * @package ow_core
 * @since 1.0
 */
abstract class OW_MobileComponent extends OW_Component
{

    /**
     * Constructor.
     *
     * @param string $template
     */
    public function __construct()
    {
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
                $this->setTemplate($plugin->getMobileCmpViewDir() . $template . '.html');
            }
        }

        return parent::render();
    }
}