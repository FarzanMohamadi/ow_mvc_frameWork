<?php
/**
 * 
 * All rights reserved.
 */

/**
 *
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmguidedtour
 * @since 1.0
 */

class FRMGUIDEDTOUR_MCTRL_GuidedTour extends OW_MobileActionController
{
    public function setGuideSeen()
    {
        if (!OW::getUser()->isAuthenticated()) {
            throw new AuthenticateException();
        }
        if (!OW::getRequest()->isAjax()) {
            throw new Redirect404Exception('');
        }
        FRMGUIDEDTOUR_BOL_Service::getInstance()->setSeen();
        exit(json_encode(array('result' => 'true')));
    }

    public function setGuideUnseen($addr)
    {
        if (!OW::getUser()->isAuthenticated()) {
            throw new AuthenticateException();
        }
        if (!OW::getRequest()->isAjax()) {
            throw new Redirect404Exception('');
        }
        FRMGUIDEDTOUR_BOL_Service::getInstance()->setUnseen();
        exit(json_encode(array('result' => 'true')));
    }

    public function showGuide()
    {
        if (!OW::getUser()->isAuthenticated()) {
            throw new AuthenticateException();
        }
        FRMGUIDEDTOUR_BOL_Service::getInstance()->updateSeenStatus(1);
        header('Location: ' . OW_URL_HOME);
        exit();
    }
}