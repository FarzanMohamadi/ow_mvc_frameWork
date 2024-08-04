<?php
/**
 * frmajaxloader
 */
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmajaxloader
 * @since 1.0
 */

class FRMAJAXLOADER_CTRL_Load extends OW_ActionController
{

    public function __construct()
    {
    }
    public function init()
    {
        if ( !OW::getRequest()->isAjax() )
        {
            throw new Redirect404Exception();
        }
    }

    /***
     * @param $params
     * @throws AuthenticateException
     */
    public function load_myfeed_newly($params){
        echo FRMAJAXLOADER_BOL_Service::getInstance()->get_myfeed_newly($params);
        exit;
    }

    /***
     * @param $params
     * @throws AuthenticateException
     */
    public function load_sitefeed_newly($params)
    {
        echo FRMAJAXLOADER_BOL_Service::getInstance()->get_sitefeed_newly($params);
        exit;
    }

    /***
     * @param $params
     * @throws RedirectException
     */
    public function load_userfeed_newly($params)
    {
        echo FRMAJAXLOADER_BOL_Service::getInstance()->get_userfeed_newly($params);
        exit;

    }

    /***
     * @param $params
     * @throws RedirectException
     */
    public function load_groupsfeed_newly($params)
    {
        echo FRMAJAXLOADER_BOL_Service::getInstance()->get_groupsfeed_newly($params);
        exit;
    }

}

