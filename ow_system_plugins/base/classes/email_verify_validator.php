<?php
/**
 * Email verification validator
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_system_plugins.base.classes
 * @since 1.8.2
 */


class BASE_CLASS_EmailVerifyValidator extends OW_Validator
{

    /**
     * Constructor.
     *
     * @param array $params
     */
    public function __construct()
    {

    }

    /**
     * @see Validator::isValid()
     *
     * @param mixed $value
     */
    public function isValid( $value )
    {
        $language = OW::getLanguage();

        if ( !UTIL_Validator::isEmailValid($value) )
        {
            $this->setErrorMessage($language->text('base', 'join_error_email_not_valid'));
            return false;
        }
        else if ( BOL_UserService::getInstance()->isExistEmail($value) )
        {
            $userId = OW::getUser()->getId();
            $user = BOL_UserService::getInstance()->findUserById($userId);

            if ( $user->email !== $value )
            {
                $this->setErrorMessage($language->text('base', 'join_error_email_already_exist'));
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