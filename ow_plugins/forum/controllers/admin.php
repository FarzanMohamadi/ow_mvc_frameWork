<?php
/**
 * Forum admin action controller
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow.ow_plugins.forum.controllers
 * @since 1.0
 */
class FORUM_CTRL_Admin extends ADMIN_CTRL_Abstract
{
    /**
     * Default action
     */
	public function index()
	{
        OW::getDocument()->setTitle(OW::getLanguage()->text('forum', 'admin_forum_settings_heading'));
        $language = OW::getLanguage();
        
        $configs =  OW::getConfig()->getValues('forum');
        
        $configSaveForm = new FORUM_ConfigSaveForm();
        $this->addForm($configSaveForm);
            
        if ( OW::getRequest()->isPost() && $configSaveForm->isValid($_POST) )
        {
            $configSaveForm->process();
            OW::getFeedback()->info($language->text('forum', 'settings_updated'));
            $this->redirect();
        }
        
	    if ( !OW::getRequest()->isAjax() )
        {
            $this->setPageHeading(OW::getLanguage()->text('forum', 'admin_forum_settings_heading'));
            $this->setPageHeadingIconClass('ow_ic_forum');
        }

        $config = OW::getConfig();

        $configValue = $config->getValue('forum', 'enable_attachments', false);
        $configSaveForm->getElement('enableAttachments')->setValue($configValue);

        $configValue = $config->getValue('forum', 'showClosedTopicLastPostInTopSection', false);
        $configSaveForm->getElement('showClosedTopicLastPostInTopSection')->setValue($configValue);

        $configValue = $config->getValue('forum', 'enableCommentsForReplies', false);
        $configSaveForm->getElement('enableCommentsForReplies')->setValue($configValue);
    }
	
	/**
	 * Plugin uninstall action
	 */
    public function uninstall()
    {
        if ( isset($_POST['action']) && $_POST['action'] == 'delete_content' )
        {
            OW::getConfig()->saveConfig('forum', 'uninstall_inprogress', 1);
            OW::getEventManager()->trigger(new OW_Event(FORUM_BOL_ForumService::EVENT_UNINSTALL_IN_PROGRESS));

            FORUM_BOL_ForumService::getInstance()->setMaintenanceMode(true);

            $event = new OW_Event('forum.uninstall_plugin');
            OW::getEventManager()->trigger($event);

            OW::getFeedback()->info(OW::getLanguage()->text('forum', 'plugin_set_for_uninstall'));
            $this->redirect();
        }
                      
        $this->setPageHeading(OW::getLanguage()->text('forum', 'page_title_uninstall'));
        $this->setPageHeadingIconClass('ow_ic_delete');
        
        $this->assign('inprogress', (bool) OW::getConfig()->getValue('forum', 'uninstall_inprogress'));
        
        $js = new UTIL_JsGenerator();
        $js->jQueryEvent('#btn-delete-content', 'click', 'if ( !confirm("'.OW::getLanguage()->text('forum', 'confirm_delete_forum').'") ) return false;');
        
        OW::getDocument()->addOnloadScript($js);
    }
}


/**
 * Save Configurations form class
 */
class FORUM_ConfigSaveForm extends Form
{
    /**
     * Class constructor
     *
     */
    public function __construct()
    {
        parent::__construct('configSaveForm');

        $language = OW::getLanguage();
        
        $attachEnableField = new CheckboxField('enableAttachments');
        $this->addElement($attachEnableField);

        $showClosedTopicLastPostInTopSection = new CheckboxField('showClosedTopicLastPostInTopSection');
        $this->addElement($showClosedTopicLastPostInTopSection);

        $enableCommentsForReplies = new CheckboxField('enableCommentsForReplies');
        $this->addElement($enableCommentsForReplies);

        // submit
        $submit = new Submit('save');
        $submit->setValue($language->text('base', 'edit_button'));
        $this->addElement($submit);
    }
    
    /**
     * Updates forum plugin configuration
     *
     * @return boolean
     */
    public function process( )
    {
        $values = $this->getValues();

        $config = OW::getConfig();

        $config->saveConfig('forum', 'enable_attachments', $values['enableAttachments']);
        $config->saveConfig('forum', 'showClosedTopicLastPostInTopSection', $values['showClosedTopicLastPostInTopSection']);
        $config->saveConfig('forum', 'enableCommentsForReplies', $values['enableCommentsForReplies']);

        return array('result' => true);
    }
}