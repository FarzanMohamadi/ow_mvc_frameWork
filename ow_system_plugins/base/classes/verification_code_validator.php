<?php
/**
 * Email verification code validator
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_system_plugins.base.classes
 * @since 1.8.2
 */

class BASE_CLASS_VerificationCodeValidator extends OW_Validator
{

    /**
     * Constructor.
     *
     * @param array $params
     */
    public function __construct()
    {
        $language = OW::getLanguage();

        $this->setErrorMessage($language->text('base', 'email_verify_invalid_verification_code'));
    }

    /**
     * @see Validator::isValid()
     *
     * @param mixed $value
     */
    public function isValid( $value )
    {
        $emailVerifyData = BOL_EmailVerifyService::getInstance()->findByHash($value);

        if ( $emailVerifyData == null )
        {
            return false;
        }

        if( $emailVerifyData->type === BOL_EmailVerifyService::TYPE_USER_EMAIL )
        {
            $user = BOL_UserService::getInstance()->findUserById($emailVerifyData->userId);

            if ( $user == null )
            {
                return false;
            }
        }

        return true;
    }

    /**
     * @see Validator::getJsValidator()
     *
     * @return string
     */
    public function getJsValidator()
    {
        return "{
                validate : function( value )
                {
                },
                getErrorMessage : function(){
                    return " . json_encode($this->getError()) . ";
                 }
        }";
    }
}