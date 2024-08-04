<?php
/**
 * Unsubscribe mass mailing users
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_system_plugins.base.controllers
 * @since 1.0
 */

class BASE_CTRL_Unsubscribe extends OW_ActionController
{
    private $unsubscribeServise;
    private $userServise;

    public function __construct()
    {
        $this->unsubscribeServise = BOL_MassMailingIgnoreUserService::getInstance();
        $this->userServise = BOL_UserService::getInstance();
    }

    public function index( $params )
    {
        if( OW::getRequest()->isAjax() )
        {
            exit;
        }
        
        $language = OW::getLanguage();

        $this->setPageHeading( $language->text( 'base', 'massmailing_unsubscribe' ) );

        $code = null;
        $userId = null;

        $result = false;

        if( isset($params['code']) && isset($params['id']) )
        {
            $result = 'confirm';
            
            if ( !empty($_POST['cancel']) )
            {
                $this->redirect(OW_URL_HOME);
            }


            $code = trim($params['code']);
            $userId = $params['id'];
            $user = $this->userServise->findUserById($userId);
            if ( $user !== null )
            {
                if( md5( $user->username . $user->password ) ===  $code )
                {
                    $result = 'confirm';
                    if (!empty( $_POST['confirm'] ) )
                    {   
                        BOL_PreferenceService::getInstance()->savePreferenceValue('mass_mailing_subscribe', false, $user->id);
                        $result = true;
                        OW::getFeedback()->info($language->text('base', 'massmailing_unsubscribe_successful'));
                        $this->redirect(OW_URL_HOME);
                    }
                }
            }
        }

        $this->assign('result', $result);
    }
    
    public function apiUnsubscribe($params)
    {
        if ( empty($params['emails']) || !is_array($params['emails']) )
        {
            throw new InvalidArgumentException('Invalid email list');
        }
        
        foreach ( $params['emails'] as $email )
        {
            $user = BOL_UserService::getInstance()->findByEmail($email);
            
            if ( $user === null )
            {
                throw new LogicException('User with email ' . $email . ' not found');
            }
            
            BOL_PreferenceService::getInstance()->savePreferenceValue('mass_mailing_subscribe', false, $user->id);
        }
    }
}

?>
