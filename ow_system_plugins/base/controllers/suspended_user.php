<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_system_plugins.base.controllers
 * @since 1.0
 */
class BASE_CTRL_SuspendedUser extends OW_ActionController
{

    public function index()
    {
        $this->assign('reason', BOL_UserService::getInstance()->getSuspendReason(OW::getUser()->getId()));
    }

    public function suspend( $params )
    {
        if ( !OW::getUser()->isAuthorized('base') || empty($params['id']) || empty($params['message']) )
        {
            exit;
        }

        $id = (int) $params['id'];
        $message = $params['message'];

        $userService = BOL_UserService::getInstance();
        $userService->suspend($id, $message);

        OW::getFeedback()->info(OW::getLanguage()->text('base', 'user_feedback_profile_suspended'));

        if ( !empty($_GET['backUrl']) )
        {
            if(strpos( $_GET['backUrl'], ":") === false ) {
                $this->redirect($_GET['backUrl']);
            }
        }
        $this->redirect(OW::getRouter()->urlForRoute('base_index'));
    }

    public function unsuspend( $params )
    {
        if ( !OW::getUser()->isAuthorized('base') || empty($params['id']) )
        {
            exit;
        }

        $id = (int) $params['id'];

        $userService = BOL_UserService::getInstance();
        $userService->unsuspend($id);

        OW::getFeedback()->info(OW::getLanguage()->text('base', 'user_feedback_profile_unsuspended'));

        if ( !empty($_GET['backUrl']) )
        {
            if(strpos( $_GET['backUrl'], ":") === false ) {
                $this->redirect($_GET['backUrl']);
            }
        }
        $this->redirect(OW::getRouter()->urlForRoute('base_index'));
    }

    public function ajaxRsp()
    {
        if ( !OW::getRequest()->isAjax() )
        {
            throw new Redirect404Exception();
        }

        $response = array();

        if ( empty($_GET['userId']) || empty($_GET['command']) )
        {
            echo json_encode($response);
            exit;
        }

        $userId = (int) $_GET['userId'];
        $command = $_GET['command'];

        switch ( $command )
        {
            case "suspend":
                BOL_UserService::getInstance()->suspend($userId);
                $response["info"] = OW::getLanguage()->text('base', 'user_feedback_profile_suspended');
                break;

            case "unsuspend":
                BOL_UserService::getInstance()->unsuspend($userId);
                $response["info"] = OW::getLanguage()->text('base', 'user_feedback_profile_unsuspended');
                break;
        }

        echo json_encode($response);
        exit;
    }
}