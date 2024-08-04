<?php
/**
 * Update Status Component
 *
 * @package ow_plugins.newsfeed.components
 * @since 1.0
 */
class NEWSFEED_CMP_UpdateStatus extends OW_Component
{
    protected $focused = false;
    CONST KEY_PHOTO_UPLOAD = 'photo_upload';
    public function __construct( $feedAutoId, $feedType, $feedId, $actionVisibility = null )
    {
        parent::__construct();

        $form = $this->createForm($feedAutoId, $feedType, $feedId, $actionVisibility);
        $csrfToken =$form->getElement('csrf_token');
        if ( OW::getPluginManager()->isPluginActive('frmnewsfeedplus') && OW::getPluginManager()->isPluginActive('frmnewsfeedplus'))
        {
          $this->assign('hidePhotoUpload', true);
        }
        $this->addForm($form);

        OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_AFTER_UPDATE_STATUS_FORM_RENDERER, array('form' => $form, 'component' => $this)));
        $this->initAttachments($feedAutoId, $form);
    }
    
    protected function initAttachments( $feedAutoId, Form $form )
    {
        $attachmentId = FRMSecurityProvider::generateUniqueId('nfa-' . $feedAutoId);
        $this->assign('uniqId', $attachmentId);
        $event=OW_EventManager::getInstance()->trigger(new OW_Event('check.user.access.getContents',array()));
        if(isset($event->getData()['denied_access']) && $event->getData()['denied_access']==true)
        {
            return;
        }
        $attachmentInputId = $form->getElement('attachment')->getId();

        $attachmentBtnId = $attachmentId . "-btn";

        $inputId = $form->getElement('status')->getId();
        $js = 'OWLinkObserver.observeInput("' . $inputId . '", function(link){
            var ac = $("#attachment_preview_' . $attachmentId . '-oembed");
            if ( ac.data("sleep") ) return;

            ac.show().html("<div class=\"ow_preloader\" style=\"height: 30px;\"></div>");

            this.requestResult(function( r )
            {
                ac.show().html(r);
            });

            this.onResult = function( r )
            {
                if(r.type == "remove_link"){
                  r = [];
                  $("#' . $attachmentInputId . '").val(JSON.stringify(r));
                }
                else{
                    if(r.description != null || r.title != null){
                        $("#' . $attachmentInputId . '").val(JSON.stringify(r));
                    }
                    else{
                        error_message = "'. OW::getLanguage()->text("newsfeed", "error_retrieving_url") .'";
                        OW.error(error_message);
                    }
                }

            };

        });';

        OW::getDocument()->addOnloadScript($js);

        $attachment = new BASE_CLASS_Attachment("newsfeed", $attachmentId, $attachmentBtnId);

        $this->addComponent('attachment', $attachment);

        $js = 'var attUid = {$uniqId}, uidUniq = 0; owForms[{$form}].bind("success", function(data){
                    OW.trigger("base.photo_attachment_reset", {pluginKey:"newsfeed", uid:attUid});
                    owForms[{$form}].getElement("attachment").setValue("");
                    OWLinkObserver.getObserver("' .$inputId. '").resetObserver();
                    $("#attachment_preview_" + {$uniqId} + "-oembed").data("sleep", false).empty();
                    
                    var attOldUid = attUid;
                    attUid = {$uniqId} + (uidUniq++);
                    OW.trigger("base.photo_attachment_uid_update", {
                        uid: attOldUid,
                        newUid: attUid
                    });
                });
                owForms[{$form}].reset = false;
                
                OW.bind("base.add_photo_attachment_submit",
                    function(data){
                        if( data.uid == attUid ) {
                            $("#attachment_preview_" + {$uniqId} + "-oembed").hide().empty();
                            $("#attachment_preview_" + {$uniqId} + "-oembed").data("sleep", true);
                        }
                    }
                );

                
                OW.bind("base.attachment_hide_button_cont",
                    function(data){
                        if( data.uid == attUid ) {
                            $("#" + {$uniqId} + "-btn-cont").hide();
                        }
                    }
                );
                
                OW.bind("base.attachment_show_button_cont",
                    function(data){
                        if( data.uid == attUid ) {
                            $("#" + {$uniqId} + "-btn-cont").show();
                        }
                    }
                );

                OW.bind("base.attachment_added",
                    function(data){
                        if( data.uid == attUid ) {
                            data.type = "photo";
                            owForms[{$form}].getElement("attachment").setValue(JSON.stringify(data));
                        }
                    }
                );

                OW.bind("base.attachment_deleted",
                    function(data){
                        if( data.uid == attUid ){
                            $("#attachment_preview_" + {$uniqId} + "-oembed").data("sleep", false).empty();
                            owForms[{$form}].getElement("attachment").setValue("");
                            OWLinkObserver.getObserver("' .$inputId. '").resetObserver();
                        }
                    }
                );';

        $js = UTIL_JsGenerator::composeJsString($js , array(
            'form' => $form->getName(),
            'uniqId' => $attachmentId
        ));

        OW::getDocument()->addOnloadScript($js);
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
        return new NEWSFEED_StatusForm($feedAutoId, $feedType, $feedId, $actionVisibility);
    }
    
    public function focusOnInput( $focus = true )
    {
        $this->focused = $focus;
    }
    
    protected function setFocusOnInput()
    {
        $statusId = $this->getForm("newsfeed_update_status")->getElement("status")->getId();
        OW::getDocument()->addOnloadScript('$("#' . $statusId . '").focus();');
    }
    
    public function onBeforeRender() 
    {
        parent::onBeforeRender();
        
        if ( $this->focused )
        {
            $this->setFocusOnInput();
        }
    }
    
}

class NEWSFEED_StatusForm extends Form
{
    public function __construct( $feedAutoId, $feedType, $feedId, $actionVisibility = null )
    {
        parent::__construct('newsfeed_update_status');

        $this->setAjax();
        $this->setAjaxResetOnSuccess(false);

        $field = new Textarea('status');
        $field->setId('newsfeed_update_status_info_id');
        $field->setHasInvitation(true);
        $field->setInvitation(FRMSecurityProvider::getStatusMessage());
        $field->addAttribute('maxlength','65500');
        $field->addValidator(new StringValidator(null,65500));
        $this->addElement($field);

        $field = new HiddenField('attachment');
        $this->addElement($field);

        $field = new HiddenField('feedType');
        $field->setValue($feedType);
        $this->addElement($field);

        $field = new HiddenField('feedId');
        $field->setValue($feedId);
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
        $submit->setValue(OW::getLanguage()->text('newsfeed', 'status_btn_label'));
        $submit->setId('updatestatus_submit_button');
        $this->addElement($submit);

        if ( !OW::getRequest()->isAjax() )
        {
            $js = UTIL_JsGenerator::composeJsString('
            owForms["newsfeed_update_status"].bind( "submit", function( r )
            {
                $(".newsfeed-status-preloader", "#" + {$autoId}).show();
            });

            owForms["newsfeed_update_status"].bind( "success", function( r )
            {
                $(this.status).val("");
                OW.trigger("clear.attachment.inProgress",{"pluginKey": "frmnewsfeedplus"});
                $(".newsfeed-status-preloader", "#" + {$autoId}).hide();

                if ( r.error ) {
                    OW.error(r.error); 
                    if(r.redirect){
                     document.location.reload();
                    }
                    return;
                }
                
                if ( r.message ) {
                    OW.info(r.message);
                }

                if ( r.item )
                {
                    window.ow_newsfeed_feed_list[{$autoId}].loadNewItem(r.item, false);
                }
            });', array('autoId' => $feedAutoId ));

            OW::getDocument()->addOnloadScript( $js );
        }

        $this->setAction( OW::getRequest()->buildUrlQueryString(OW::getRouter()->urlFor('NEWSFEED_CTRL_Ajax', 'statusUpdate')) );
        OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_BEFORE_UPDATE_STATUS_FORM_RENDERER, array('form' => $this)));

    }
}