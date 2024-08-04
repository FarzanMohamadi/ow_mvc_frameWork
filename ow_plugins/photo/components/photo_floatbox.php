<?php
/**
 * Photo floatbox component
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow.plugin.photo.components
 * @since 1.3.2
 */
class PHOTO_CMP_PhotoFloatbox extends OW_Component
{
    public function __construct( $layout, $params )
    {
        parent::__construct();

        if ( empty($params['available']) )
        {
            if ( !empty($params['msg']) )
            {
                $msg = $params['msg'];
            }
            else
            {
                $msg = OW::getLanguage()->text('base', 'authorization_failed_feedback');
            }

            $this->assign('authError', $msg);

            return;
        }
        if(OW::getPluginManager()->isPluginActive('frmwidgetplus') && OW::getConfig()->getValue('frmwidgetplus', 'displayRateWidget')==2 && !OW::getUser()->isAuthenticated())
            $this->assign('displayRate', false);
        else
            $this->assign('displayRate', true);
        switch ( $layout )
        {
            case 'page':
                $class = ' ow_photoview_info_onpage';
                break;
            default:
                if ( (bool)OW::getConfig()->getValue('photo', 'photo_view_classic') )
                {
                    $class = ' ow_photoview_pint_mode';
                }
                else
                {
                    $class = '';
                }
                break;
        }
        
        $this->assign('class', $class);
        $this->assign('layout', $layout);
    }
}
