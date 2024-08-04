<?php
/**
 * FRM Graph
 */
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmgraph
 * @since 1.0
 */
class FRMGRAPH_CMP_TooltipIcon extends OW_Component
{
    public function __construct( $params )
    {
        if(is_array($params)) {
            $text = $params['text'];
        }else{
            $text = $params;
        }

        parent::__construct();

        OW::getDocument()->addStyleDeclaration('
.tooltip-icon {
    float: right;
    width: 20px;
    height: 20px;
}
.tooltip-icon:lang(fa-IR) {
    float: left;
}
        ');

        $this->assign('text',$text);
        $this->assign('img_src', OW::getPluginManager()->getPlugin('frmgraph')->getStaticCssUrl() . 'tooltip-icon.svg');
    }
}