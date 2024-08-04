<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_system_plugins.base.classes
 * @since 1.0
 */
class BASE_CLASS_StandardAuth extends OW_AuthAdapter
{
    /**
     * @var string
     */
    private $identity;
    /**
     * @var string
     */
    private $password;
    /**
     * @var BOL_UserService
     */
    private $userService;

    /**
     * Constructor.
     *
     * @param string $identity
     * @param string $password
     */
    public function __construct( $identity, $password )
    {
        $this->identity = trim($identity);
        $this->password = trim($password);

        $this->userService = BOL_UserService::getInstance();
    }

    /**
     * @see OW_AuthAdapter::authenticate()
     *
     * @return OW_AuthResult
     */
    function authenticate()
    {
        $user = $this->userService->findUserForStandardAuth($this->identity);

        $language = OW::getLanguage();

        if ( $user === null )
        {
            return new OW_AuthResult(OW_AuthResult::FAILURE_IDENTITY_NOT_FOUND, null, array($language->text('base', 'auth_identity_not_found_error_message')));
        }
        
        if ( $user->getPassword() !== BOL_UserService::getInstance()->hashPassword($this->password,$user->id) )
        {
            return new OW_AuthResult(OW_AuthResult::FAILURE_PASSWORD_INVALID, null, array($language->text('base', 'auth_invlid_password_error_message')));
        }
        if(OW::getRequest()->isAjax()){
            return new OW_AuthResult(OW_AuthResult::SUCCESS, $user->getId(), array($language->text('base', 'auth_success_message')));
        }
        else
        {
            return new OW_AuthResult(OW_AuthResult::SUCCESS, $user->getId(), array($language->text('base', 'auth_success_message_not_ajax')));
        }
    }
}