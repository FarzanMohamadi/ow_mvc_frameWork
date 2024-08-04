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
class FRMGUIDEDTOUR_CTRL_GuidedTour extends OW_ActionController
{
    public function setGuideSeen()
    {
        if (!OW::getUser()->isAuthenticated() || BOL_QuestionService::getInstance()->getEmptyRequiredQuestionsList(OW::getUser()->getId())) {
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
        if (!OW::getUser()->isAuthenticated() || BOL_QuestionService::getInstance()->getEmptyRequiredQuestionsList(OW::getUser()->getId())) {
            throw new AuthenticateException();
        }
        if (!OW::getRequest()->isAjax()) {
            throw new Redirect404Exception('');
        }
        $url = isset($_POST['url'])?$_POST['url']:'';
        exit(json_encode(array('result' => 'true', 'markup' => FRMGUIDEDTOUR_BOL_Service::getInstance()->echoMarkup($url))));
    }

    public function openGuide()
    {
        if (!OW::getUser()->isAuthenticated() || BOL_QuestionService::getInstance()->getEmptyRequiredQuestionsList(OW::getUser()->getId())) {
            throw new AuthenticateException();
        }
        if (!OW::getRequest()->isAjax()) {
            throw new Redirect404Exception('');
        }
        exit(json_encode(array('result' => 'true')));
    }

    public function updateGuideSeenStatus()
    {
        if (!OW::getUser()->isAuthenticated() || BOL_QuestionService::getInstance()->getEmptyRequiredQuestionsList(OW::getUser()->getId())) {
            throw new AuthenticateException();
        }
        if (!OW::getRequest()->isAjax()) {
            throw new Redirect404Exception('');
        }
        FRMGUIDEDTOUR_BOL_Service::getInstance()->updateSeenStatus($_POST['status']);
        exit(json_encode(array('result' => 'true')));
    }
}