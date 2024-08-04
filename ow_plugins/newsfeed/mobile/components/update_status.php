<?php
/**
 * Update Status Component
 *
 * @package ow_plugins.newsfeed.mobile.components
 * @since 1.0
 */
class NEWSFEED_MCMP_UpdateStatus extends NEWSFEED_CMP_UpdateStatus
{
    public function __construct( $feedAutoId, $feedType, $feedId, $actionVisibility = null )
    {
        parent::__construct($feedAutoId, $feedType, $feedId, $actionVisibility);
        $this->assign('statusMessage',(FRMSecurityProvider::getStatusMessage()!=null)? FRMSecurityProvider::getStatusMessage() : OW::getLanguage()->text('newsfeed', 'status_field_invintation'));
        $tpl = OW::getPluginManager()->getPlugin("newsfeed")->getMobileCmpViewDir() . "update_status.html";
        $this->setTemplate($tpl);
        if(!FRMSecurityProvider::checkPluginActive('frmnewsfeedplus', true)) {
            $this->assign('hideDefaultAttachment',false);
        }else{
            $this->assign('hideDefaultAttachment',true);
        }

        $otpEvent=OW_EventManager::getInstance()->trigger(new OW_Event('newsfeed.check.chat.form',['feedType'=>$feedType]));
        if( isset($otpEvent->getData()['showOtpForm']) && $otpEvent->getData()['showOtpForm']){
            $this->assign('otpForm',true);
        }
    }
    
    public function initAttachments($feedAutoId, Form $form) 
    {
        OW::getDocument()->addOnloadScript(UTIL_JsGenerator::composeJsString(
            'window.onStatusUpdate_' . $feedAutoId . ' = function( r ) {
                $("#newsfeed_status_input").val("");
                $("#newsfeed-att-file").val("");
                $("#newsfeed-att-file-prevew img").hide();
                $("#newsfeed-att-file-prevew span").empty();
                
                $("#newsfeed_status_save_btn_c").removeClass("owm_preloader_circle");

                if ( r.error ) {
                    OWM.error(r.error); 
                    if(r.reload)
                    {
                      document.location.reload();
                    }
                    return;
                }

                if ( r.item ) {
                    window.ow_newsfeed_feed_list[{$autoId}].loadNewItem(r.item, false);
                }
                
                if ( r.message ) {
                    OWM.info(r.message);
                }

                OWM.getActiveFloatBox().close();
                $(".owm_nav_menu.owm_nav_back").click();
                
                if (typeof refreshAttachClass === "function") { 
                    refreshAttachClass();
                }
            }',
        array(
            'autoId' => $feedAutoId
        )));
    }
    
    protected function setFocusOnInput()
    {
        $this->assign("focused", true);
    }

        /**
     * 
     * @param int $feedAutoId
     * @param string $feedType
     * @param int $feedId
     * @param int $actionVisibility
     * @return Form
     */
    public function createForm( $feedAutoId, $feedType, $feedId, $actionVisibility )
    {
        return new NEWSFEED_MStatusForm($feedAutoId, $feedType, $feedId, $actionVisibility);
    }
}

class NEWSFEED_MStatusForm extends Form
{
    public function __construct( $feedAutoId, $feedType, $feedId, $actionVisibility = null )
    {
        parent::__construct('newsfeed_update_status');
        
        $this->setEnctype(Form::ENCTYPE_MULTYPART_FORMDATA);
        
        $field = new Textarea('status');
        $field->setId('newsfeed_update_status_info_id');
        $field->setHasInvitation(true);
        $field->setInvitation( OW::getLanguage()->text('newsfeed', 'status_field_invintation') );
        $this->addElement($field);

        $field = new HiddenField('attachment');
        $this->addElement($field);

        $field = new HiddenField('feedType');
        $field->setValue($feedType);
        $this->addElement($field);

        $field = new HiddenField('feedId');
        $field->setValue($feedId);
        $this->addElement($field);
        
        $field = new HiddenField('feedAutoId');
        $field->setValue($feedAutoId);
        $this->addElement($field);

        $field = new HiddenField('visibility');
        $field->setValue($actionVisibility);
        $this->addElement($field);

        $moreFields = OW::getEventManager()->trigger(new OW_Event('newsfeed.update_status.form', ['feedType'=>$feedType, 'version'=>'desktop'], ['elements' => []]));
        if(isset($moreFields->getData()['elements'])){
            foreach($moreFields->getData()['elements'] as $field){
                $this->addElement($field);
            }
        }

        $submit = new Submit('save');
        $submit->setId('updatestatus_submit_button');
        $submit->setValue(OW::getLanguage()->text('newsfeed', 'status_btn_label'));
        $this->addElement($submit);
        if ( !OW::getRequest()->isAjax() )
        {
            $js = UTIL_JsGenerator::composeJsString('
            
            owForms["newsfeed_update_status"].bind( "submit", function( r )
            {
                $("#newsfeed_status_save_btn_c").addClass("owm_preloader_circle");
            });
            
            owForms["newsfeed_update_status"].bind( "success", function( r )
            {
                OW.trigger("clear.attachment.inProgress",{"pluginKey": "frmnewsfeedplus"});
            });   
            ');

            OW::getDocument()->addOnloadScript( $js );
        }

        $this->setAction( OW::getRequest()->buildUrlQueryString(OW::getRouter()->urlFor('NEWSFEED_MCTRL_Feed', 'statusUpdate')) );
        OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_BEFORE_UPDATE_STATUS_FORM_RENDERER, array('form' => $this)));
    }
}