<?php
class FRMEMAILCONTROLLER_CLASS_EmailProviderValidator extends OW_Validator
{

    /***
     * Constructor.
     *
     */
    public function __construct()
    {

    }

    /***
     * @param mixed $value
     * @return bool
     */
    public function isValid( $value )
    {
        $config=OW::getConfig();
        if(isset($value) && $value!=null && $config->getValue('frmemailcontroller','disable_frmemailcontroller')!=1 && !OW::getUser()->isAdmin()){
            $pieces = explode("@", $value);
            $emailProvider =$pieces[1];
            $validEmailServiceProviders = json_decode(OW::getConfig()->getValue('frmemailcontroller', 'valid_email_services'), true);
            if ( !empty($validEmailServiceProviders) && !in_array($emailProvider, $validEmailServiceProviders) )
            {
                if(isset($_POST['form_name']) && ($_POST['form_name']=='emailVerifyForm' || $_POST['form_name']=='forgot-password')){
                    $errorText = OW::getLanguage()->text('frmemailcontroller', 'email_verify_provider_validate_error');
                    foreach ($validEmailServiceProviders as $validEmailProvider){
                        $errorText = $errorText .$validEmailProvider." - ";
                    }
                    $errorText=rtrim($errorText," - ");
                    $this->setErrorMessage($errorText);
                }else{
                    $this->setErrorMessage('<a class="error" href="javascript:showValidEmails()">'.OW::getLanguage()->text('frmemailcontroller', 'provider_validate_error').'</a>');
                }
                return false;
            }
        }
        return true;
    }

}
