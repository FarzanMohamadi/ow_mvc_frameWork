<?php
/**
 * Context action component class.
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_system_plugins.base.components
 * @since 1
 */
class FRMMENU_CMP_ContextAction extends OW_Component
{

    private $actions = array();
    private $position;

    public function __construct( )
    {
        parent::__construct();

    }

    public function render()
    {
        $this->assign('actions', $this->actions);
        $this->assign('position', $this->position);

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

        return parent::render();
    }

    /**
     * @param mixed $position
     */
    public function setPosition($position)
    {
        $this->position = $position;
    }

    /**
     * @return mixed
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @param array $actions
     */
    public function setActions($actions)
    {
        $this->actions = $actions;
    }

    /**
     * @return array
     */
    public function getActions()
    {
        return $this->actions;
    }
}