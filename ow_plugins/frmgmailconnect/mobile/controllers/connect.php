<?php
class FRMGMAILCONNECT_MCTRL_Connect extends OW_MobileActionController
{
    /**
     *
     * @var FRMGMAILCONNECT_BOL_Service
     */
    private $service;

    public function init()
    {
        $this->service = FRMGMAILCONNECT_BOL_Service::getInstance();
    }

    public function oauth()
    {
     $language = OW::getLanguage();
     if (!empty ($_GET['code']))
     {
       $data = array (
         'code'=>$_GET['code'],
         'client_id'=>$this->service->props->client_id,
         'client_secret'=>$this->service->props->client_secret,
         'redirect_uri'=>$this->service->props->redirect_uri,
         'grant_type'=>'authorization_code'
        );
       $userinfo = $this->service->getUserInfo ($data);
     }
     else
     {
        OW::getFeedback()->error($language->text('frmgmailconnect', 'login_failure_msg'));
        $this->redirect(OW::getRouter()->urlForRoute('static_sign_in'));
     }
     $result = $this->login($userinfo);
     if ($result){
         echo "<script>window.opener.OWM.message('" . $language->text('frmgmailconnect', 'login_success_msg') . "','info');" .
             "window.close();window.opener.location.href = window.opener.location.href;</script>";
     }
     else $this->redirect(OW::getRouter()->urlForRoute('static_sign_in'));
    }


  public function login( $params )
    {
      $language = OW::getLanguage();
      // Register or login
      $user = BOL_UserService::getInstance()->findByEmail($params['email']);
      if (!empty($user))
      {
        // LOGIN
          OW::getUser()->login($user->id);
          if (OW::getConfig()->getValue('base', 'mandatory_user_approve') && OW::getUser()->isAuthenticated() && !OW::getUser()->isAdmin() && !BOL_UserService::getInstance()->isApproved() ) {
              OW::getFeedback()->error(OW::getLanguage()->text('base', 'wait_for_approval'));
              OW_User::getInstance()->logout();
              OW::getApplication()->redirect(OW::getRouter()->urlForRoute(('base_default_index')));
          }
          else {

              OW::getFeedback()->info($language->text('frmgmailconnect', 'login_success_msg'));
              return true;
          }
      }
      else
      {
        //REGISTER
        $authAdapter = new FRMGMAILCONNECT_CLASS_AuthAdapter($params['email']);
        $username = 'g_'.$params ['id'];
        $password = FRMSecurityProvider::generateUniqueId();
        try
        {
          $user = BOL_UserService::getInstance()->createUser($username, $password, $params['email'], null, $params['verified_email']);
        }
        catch ( Exception $e )
        {
          switch ( $e->getCode() )
          {
           case BOL_UserService::CREATE_USER_DUPLICATE_EMAIL:
             OW::getFeedback()->error($language->text('frmgmailconnect', 'join_dublicate_email_msg'));
             return false;
             break;
          case BOL_UserService::CREATE_USER_INVALID_USERNAME:
             OW::getFeedback()->error($language->text('frmgmailconnect', 'join_incorrect_username'));
             return false;
             break;
          default:
             OW::getFeedback()->error($language->text('frmgmailconnect', 'join_incomplete'));
             return false;
             break;
         }
      } //END TRY-CATCH
      $user->username = "google_user_" . $user->id;
      BOL_UserService::getInstance()->saveOrUpdate($user);
      BOL_QuestionService::getInstance()->saveQuestionsData(array('realname' => $params['name']), $user->id);
      //BOL_AvatarService::getInstance()->setUserAvatar ($user->id, $params['picture']);

      switch (isset($params['gender']))
      {
        case 'male'   :  BOL_QuestionService::getInstance()->saveQuestionsData(array('sex' => 1), $user->id);break;
        case 'female' :  BOL_QuestionService::getInstance()->saveQuestionsData(array('sex' => 2), $user->id);break;
      }
      $authAdapter->register($user->id);
      $authResult = OW_Auth::getInstance()->authenticate($authAdapter);
      if ( $authResult->isValid() )
      {
        $event = new OW_Event(OW_EventManager::ON_USER_REGISTER, array('method' => 'google', 'userId' => $user->id));
        OW::getEventManager()->trigger($event);
        OW::getUser()->login($user->getId());
          $resetPasswordCode = BOL_UserService::getInstance()->getNewResetPasswordCode($user->getId());
          $vars = array('code' => $resetPasswordCode, 'username' => $user->getUsername(), 'requestUrl' => OW::getRouter()->urlForRoute('base.reset_user_password_request'),
              'resetUrl' => OW::getRouter()->urlForRoute('base.reset_user_password', array('code' => $resetPasswordCode)));
          $email = trim($params['email']);
          $mail = OW::getMailer()->createMail();
          $mail->addRecipientEmail($email);
          $mail->setSubject($language->text('frmgmailconnect', 'set_password_mail_template_subject'));
          $mail->setTextContent($language->text('frmgmailconnect', 'set_password_mail_template_content_txt', $vars));
          $mail->setHtmlContent($language->text('frmgmailconnect', 'set_password_mail_template_content_html', $vars));
          OW::getMailer()->send($mail);
          if (OW::getConfig()->getValue('base', 'mandatory_user_approve') && OW::getUser()->isAuthenticated() && !OW::getUser()->isAdmin() && !BOL_UserService::getInstance()->isApproved() ) {
              OW::getFeedback()->error(OW::getLanguage()->text('base', 'wait_for_approval'));
              OW_User::getInstance()->logout();
              OW::getApplication()->redirect(OW::getRouter()->urlForRoute(('base_default_index')));
          }
          else
              OW::getFeedback()->info($language->text('frmgmailconnect', 'join_success_msg'));
      }
      else
      {
        OW::getFeedback()->error($language->text('frmgmailconnect', 'join_failure_msg'));
      }
      return $authResult->isValid();
    }
   }
}