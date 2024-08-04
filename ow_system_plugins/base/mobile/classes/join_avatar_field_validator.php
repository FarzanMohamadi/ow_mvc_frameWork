<?php
/**
 * Avatar field form element validator.
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow.ow_system_plugins.base.bol
 * @since 1.7.2
 */
class BASE_MCLASS_JoinAvatarFieldValidator extends BASE_CLASS_AvatarFieldValidator
{
    /**
     * @param mixed $value
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

        if ( !OW::getStorage()->isWritable(BOL_AvatarService::getInstance()->getAvatarsDir()) )
        {
            $this->setErrorMessage($language->text('base', 'not_writable_avatar_dir'));

            return false;
        }
        
        if ( empty($_FILES['userPhoto']['name']) )
        {
            return false;
        }

        if ( !empty($_FILES['userPhoto']['error']) )
        {
            $this->setErrorMessage(BOL_FileService::getInstance()->getUploadErrorMessage($_FILES['userPhoto']['error']));

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