<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_system_plugins.base.controllers
 * @since 1.0
 */

class BASE_CTRL_DeleteUser extends OW_ActionController
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index( $params )
    {
        if ( !OW::getUser()->isAuthenticated() )
        {
            throw new AuthenticateException();
        }

        if ( OW::getUser()->isAdmin() )
        {
            throw new Redirect404Exception();
        }

        $newDeletePath = OW::getEventManager()->trigger(new OW_Event('base.before.action_user_delete', array('href' => '', 'userId' => 'me')));
        if(isset($newDeletePath->getData()['href'])){
            $href = $newDeletePath->getData()['href'];
            OW::getApplication()->redirect($href);
            exit();
        }

        $form = new Form('deleteUser');
        $form->setMethod(Form::METHOD_POST);

        $userNameField = new TextField('userName');
        $userNameField->setLabel(OW::getLanguage()->text('admin', 'restrictedusernames_username_label'));
        $userNameField->setRequired(true);
        $form->addElement($userNameField);

        $fieldCaptcha = new CaptchaField('captcha');
        $fieldCaptcha->setLabel(OW::getLanguage()->text('base', 'form_label_captcha'));
        $form->addElement($fieldCaptcha);
        $this->assign('captcha_present', 'true');

        $this->assign('passwordRequiredProfile', false);
        if(OW::getConfig()->configExists('frmsecurityessentials','passwordRequiredProfile')){
            $passwordRequiredProfile=OW::getConfig()->getValue('frmsecurityessentials','passwordRequiredProfile');
            if($passwordRequiredProfile){
                $password = new PasswordField('password');
                $password->setLabel(OW::getLanguage()->text("frmsecurityessentials", "password"));
                $password->setRequired(true);
                $form->addElement($password);
                $this->assign('passwordRequiredProfile', true );
            }
        }
        $submit = new Submit('submit');
        $submit->setValue(OW::getLanguage()->text('base','delete_user_delete_button'));
        $submit->addAttribute('class', 'ow_button ow_ic_delete ow_red');
        $form->addElement($submit);

        $cancel = new Button('cancel');
        $cancel->setValue(OW::getLanguage()->text('base','delete_user_cancel_button'));
        $form->addElement($cancel);

        $user = OW::getUser()->getUserObject();
        $username = null;
        if(isset($user))
            $username = $user->username;
        $profileUrl = OW::getRouter()->urlForRoute('base_user_profile',array('username'=>$username));

        OW::getDocument()->addOnloadScript('
            $("form[name=deleteUser] input[name=cancel]").click(
                function(){
                    window.location = "'.$profileUrl.'";
                }
            );
        ');

        $this->addForm($form);
        $language = OW::getLanguage();

        $this->setPageHeading($language->text('base', 'delete_user_index'));

        $userId = OW::getUser()->getId();

        if ( OW::getRequest()->isPost() && !(OW::getRequest()->isAjax()) && $form->isValid($_POST))
        {
            if ( isset( $_POST['submit'] ) )
            {
                $data = $form->getValues();
                $userToDelete =BOL_UserService::getInstance()->findByUsername($data['userName']);
                if(!isset($userToDelete) || $userId!=$userToDelete->id) {
                    OW::getFeedback()->error(OW::getLanguage()->text("base", "no_match_usernames"));
                    return false;
                }
                if(OW::getConfig()->configExists('frmsecurityessentials','passwordRequiredProfile')) {
                    $passwordRequiredProfile = OW::getConfig()->getValue('frmsecurityessentials', 'passwordRequiredProfile');
                    if ($passwordRequiredProfile) {
                        $auth = false;
                        if ( !empty($data['password']) )
                        {
                            $auth = BOL_UserService::getInstance()->isValidPassword( OW::getUser()->getId(), $data['password'] );
                        }
                        if(!$auth){
                            OW::getFeedback()->error($language->text('base', 'password_protection_error_message'));
                            return;
                        }
                    }
                }

                OW::getUser()->logout();

                BOL_UserService::getInstance()->deleteUser($userId, true);

                $this->redirect( OW::getRouter()->urlForRoute('base_index') );
            }

            if ( isset( $_POST['cancel'] ) )
            {
                $this->redirect( OW::getRouter()->urlForRoute('base_edit') );
            }
        }
    }
}
