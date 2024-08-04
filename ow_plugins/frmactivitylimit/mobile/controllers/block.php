<?php
/**
 * frmactivitylimit
 */
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmactivitylimit
 * @since 1.0
 */

class FRMACTIVITYLIMIT_MCTRL_Block extends OW_MobileActionController
{
    public function __construct()
    {
        parent::__construct();
    }
    
    public function index( $params = NULL )
    {
        if(!OW::getUser()->isAuthenticated()){
            OW::getApplication()->redirect(OW::getRouter()->urlForRoute('base_index'));
        }

        $item = FRMACTIVITYLIMIT_BOL_UserRequestsDao::getInstance()->findById(OW::getUser()->getId());
        if(!$item->isLocked()){
            OW::getApplication()->redirect(OW::getRouter()->urlForRoute('base_index'));
        }

        $blocking_minutes = OW::getConfig()->getValue('frmactivitylimit', 'blocking_minutes');
        $end= $item->getLastResetTimestamp() + ($blocking_minutes * 60);

        $this->assign('min', $blocking_minutes);
        $this->assign('end', UTIL_DateTime::formatSimpleDate($end));
    }
}
