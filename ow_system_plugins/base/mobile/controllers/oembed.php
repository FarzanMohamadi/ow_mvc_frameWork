<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow.ow_system_plugins.base.mobile.controllers
 * @since 1.6.0
 */
class BASE_MCTRL_Oembed extends OW_MobileActionController
{
    /**
     * Get embed code
     * 
     * @return sting
     */
    public function getAjaxEmbedCode()
    {
        $result = array();
        $url = !empty($_GET['url']) ? urldecode($_GET['url']) : null;

        if ( $url )
        {
            $embedInfo = UTIL_HttpResource::getOEmbed($url);

            if ( !empty($embedInfo['html']) ) {
                $result = $embedInfo;
            }
        }

        die(json_encode($result));
    }
}