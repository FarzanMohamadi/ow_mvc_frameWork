<?php
/**
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_system_plugins.base.components
 * @since 1.7.2
 */

class BASE_CMP_CommentSetSuspendMessage extends BASE_CMP_SetSuspendMessage
{

    private $userSuspendId;
    private $commentListId;
    /**
     * @return Constructor.
     */
    public function __construct($userSuspendId,$commentListId)
    {
        $this->userSuspendId = $userSuspendId;
        $this->commentListId = $commentListId;
        parent::__construct();
        $plugin = OW::getPluginManager()->getPlugin(OW::getAutoloader()->getPluginKey(get_class($this)));
        $this->setTemplate($plugin->getCmpViewDir() . 'set_suspend_message.html');
    }
    
    protected function bindJs( $form )
    {
        OW::getLanguage()->addKeyForJs('base', 'set_suspend_message_label ');
        
        $form->bindJsFunction(Form::BIND_SUBMIT, ' function(e) { 
                debugger;
                OW.trigger("base.on_suspend_command_'.$this->commentListId.'", [\'suspend\',\''.$this->userSuspendId.'\',e.message]);
                var floatbox = OW.getActiveFloatBox();

                if ( floatbox )
                {
                    floatbox.close();
                }

                return false;
        } ');
    }
}
