<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 */
class BASE_CTRL_WaitForApproval extends OW_ActionController
{

    public function index()
    {
    }

    public function requestChangeFormSubmit($params){
        $userId = $params['id'];
        $message = $_POST['message'];

        BOL_UserService::getInstance()->requestChangeFromUser($userId, $message);

        // feadback to moderator
        OW::getFeedback()->info(OW::getLanguage()->text('base', 'saved_successfully'));

        // redirect
        $UserName = BOL_UserService::getInstance()->findUserById($userId)->getUsername();
        $url = OW::getRouter()->urlForRoute('base_user_profile', array('username' => $UserName));
        $this->redirect($url);
    }
}