<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_system_plugins.frmmenu.mobile.components
 * @since 1
 */
class FRMMENU_MCMP_ContextAction extends BASE_MCMP_ContextAction
{
    /**
     * Constructor.
     */
    public function __construct( $items, $label = null )
    {
        parent::__construct($items, $label = null);
    }

    public function render()
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

        return parent::render();
    }
}