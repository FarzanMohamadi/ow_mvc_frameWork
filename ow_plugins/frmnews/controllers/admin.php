<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmnews.controllers
 * @since 1.0
 */
class FRMNEWS_CTRL_Admin extends ADMIN_CTRL_Abstract
{

    public function __construct()
    {
        parent::__construct();

        $this->setPageHeading(OW::getLanguage()->text('frmnews', 'admin_news_settings_heading'));
        $this->setPageHeadingIconClass('ow_ic_gear_wheel');
    }

    /**
     * Default action
     */
    public function index()
    {
        $form = new FRMNEWS_SettingsForm($this);
        if ( !empty($_POST) && $form->isValid($_POST) )
        {
            $data = $form->getValues();

            OW::getConfig()->saveConfig('frmnews', 'results_per_page', $data['results_per_page']);
        }

        $this->addForm($form);
    }
    
    public function uninstall()
    {
        if ( isset($_POST['action']) && $_POST['action'] == 'delete_content' )
        {
            OW::getConfig()->saveConfig('frmnews', 'uninstall_inprogress', 1);
            OW::getEventManager()->trigger(new OW_Event(EntryService::EVENT_UNINSTALL_IN_PROGRESS));

            //maint-ce mode

            OW::getFeedback()->info(OW::getLanguage()->text('frmnews', 'plugin_set_for_uninstall'));
            $this->redirect();
        }

        $this->setPageHeading(OW::getLanguage()->text('frmnews', 'page_title_uninstall'));
        $this->setPageHeadingIconClass('ow_ic_delete');

        $this->assign('inprogress', (bool) OW::getConfig()->getValue('frmnews', 'uninstall_inprogress'));

        $js = new UTIL_JsGenerator();

        $js->jQueryEvent('#btn-delete-content', 'click', 'if ( !confirm("'.OW::getLanguage()->text('frmnews', 'confirm_delete_photos').'") ) return false;');

        OW::getDocument()->addOnloadScript($js);    	
    }    

}

class FRMNEWS_SettingsForm extends Form
{

    /***
     * FRMNEWS_SettingsForm constructor.
     * @param FRMNEWS_CTRL_Admin $ctrl
     */
    public function __construct( $ctrl )
    {
        OW::getDocument()->setTitle(OW::getLanguage()->text('frmnews', 'admin_news_settings_heading'));
        parent::__construct('form');

        $configs = OW::getConfig()->getValues('frmnews');

        $ctrl->assign('configs', $configs);

        $l = OW::getLanguage();

        $textField['results_per_page'] = new TextField('results_per_page');

        $textField['results_per_page']->setLabel($l->text('frmnews', 'admin_settings_results_per_page'))
            ->setValue($configs['results_per_page'])
            ->addValidator(new IntValidator())
            ->setRequired(true);

        $this->addElement($textField['results_per_page']);

        $submit = new Submit('submit');

        $submit->setValue($l->text('frmnews', 'save_btn_label'));

        $this->addElement($submit);
    }
}