<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_system_plugins.base.components
 * @since 1.0
 */
class BASE_CMP_WidgetMenu extends OW_Component
{

    public function __construct( $items )
    {
        parent::__construct();

        $this->assign('items', $items);
        OW::getDocument()->addOnloadScript('OW.initWidgetMenu(' . json_encode($items) . ');');
    }
}