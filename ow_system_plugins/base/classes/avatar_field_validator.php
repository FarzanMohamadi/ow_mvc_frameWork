<?php
/**
 * Avatar field form element validator.
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow.ow_system_plugins.base.bol
 * @since 1.7.2
 */
class BASE_CLASS_AvatarFieldValidator extends OW_Validator
{
    protected $required = false;
    protected $userId = null;

    /**
     * @param bool $required
     */
    public function __construct( $required = false, $userId = null )
    {
        $this->required = $required;
        $this->userId = $userId;

        $language = OW::getLanguage();
        $this->setErrorMessage($language->text('base', 'form_validator_required_error_message'));
    }

    /**
     * Is avatar valid
     *
     * @param string $value
     * @return bool
     */
    public function isValid( $value )
    {
        if ( !$this->required )
        {
            return true;
        }

        $language = OW::getLanguage();
        $avatarService = BOL_AvatarService::getInstance();

        $key = $avatarService->getAvatarChangeSessionKey();
        $path = $avatarService->getTempAvatarPath($key, 3);

        $userId = $this->userId ? $this->userId : OW::getUser()->getId();

        if ( !$value  || (!OW::getStorage()->fileExists($path) && !BOL_AvatarService::getInstance()->getAvatarUrl($userId, 1, null,false)) )
        {
            return false;
        }

        if ( !OW::getStorage()->isWritable(BOL_AvatarService::getInstance()->getAvatarsDir()) )
        {
            $this->setErrorMessage($language->text('base', 'not_writable_avatar_dir'));

            return false;
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
        $condition = '';

        if ( $this->required )
        {
            $condition = "
            if ( value == undefined || $.trim(value).length == 0 ) {
                throw " . json_encode($this->getError()) . ";
            }";
        }

        return "{
                validate : function( value ){ " . $condition . " },
                getErrorMessage : function(){ return " . json_encode($this->getError()) . " }
        }";
    }
}