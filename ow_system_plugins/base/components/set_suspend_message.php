<?php
/**
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_system_plugins.base.components
 * @since 1.7.2
 */

class BASE_CMP_SetSuspendMessage extends OW_Component
{
    /**
     * @return Constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $form = new Form('set_suspend_message');
        $form->setAjax(true);
        $form->setAjaxResetOnSuccess(true);
        
        $textarea = new Textarea('message');
        $textarea->setRequired();
        
        $form->addElement($textarea);
        
        $submit = new Submit('submit');
        $submit->setLabel(OW::getLanguage()->text('base', 'submit'));
        
        $form->addElement($submit);
        

        
//        $form->bindJsFunction(Form::BIND_SUBMIT, ' function(e) { 
//                return false;  } 
//                
//                ');
        
        
        $this->addForm($form);
        
        $this->bindJs($form);
    }
    
    protected function bindJs( $form )
    {
        OW::getLanguage()->addKeyForJs('base', 'set_suspend_message_label ');
        
        $form->bindJsFunction(Form::BIND_SUBMIT, ' function(e) { 
                
                OW.trigger("base.on_suspend_command", [\'suspend\', e.message]);
                var floatbox = OW.getActiveFloatBox();

                if ( floatbox )
                {
                    floatbox.close();
                }

                return false;
        } ');
    }
}
