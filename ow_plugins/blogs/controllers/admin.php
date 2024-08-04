<?php
/**
 * @package ow_plugins.blogs.controllers
 * @since 1.0
 */
class BLOGS_CTRL_Admin extends ADMIN_CTRL_Abstract
{

    public function __construct()
    {
        parent::__construct();

        $this->setPageHeading(OW::getLanguage()->text('blogs', 'admin_blogs_settings_heading'));
        $this->setPageHeadingIconClass('ow_ic_gear_wheel');
    }

    /**
     * Default action
     */
    public function index()
    {
        OW::getDocument()->setTitle(OW::getLanguage()->text('blogs', 'admin_blogs_settings_heading'));
        $form = new BLOGS_SettingsForm($this);
        if ( !empty($_POST) && $form->isValid($_POST) )
        {
            $data = $form->getValues();

            OW::getConfig()->saveConfig('blogs', 'results_per_page', $data['results_per_page']);
        }

        $this->addForm($form);
    }
    
    public function uninstall()
    {
        if ( isset($_POST['action']) && $_POST['action'] == 'delete_content' )
        {
            OW::getConfig()->saveConfig('blogs', 'uninstall_inprogress', 1);
            OW::getEventManager()->trigger(new OW_Event(PostService::EVENT_UNINSTALL_IN_PROGRESS));

            //maint-ce mode

            OW::getFeedback()->info(OW::getLanguage()->text('blogs', 'plugin_set_for_uninstall'));
            $this->redirect();
        }

        $this->setPageHeading(OW::getLanguage()->text('blogs', 'page_title_uninstall'));
        $this->setPageHeadingIconClass('ow_ic_delete');

        $this->assign('inprogress', (bool) OW::getConfig()->getValue('blogs', 'uninstall_inprogress'));

        $js = new UTIL_JsGenerator();

        $js->jQueryEvent('#btn-delete-content', 'click', 'if ( !confirm("'.OW::getLanguage()->text('blogs', 'confirm_delete_photos').'") ) return false;');

        OW::getDocument()->addOnloadScript($js);    	
    }    

}

class BLOGS_SettingsForm extends Form
{

    public function __construct( $ctrl )
    {
        parent::__construct('form');

        $configs = OW::getConfig()->getValues('blogs');

        $ctrl->assign('configs', $configs);

        $l = OW::getLanguage();

        $textField['results_per_page'] = new TextField('results_per_page');

        $textField['results_per_page']->setLabel($l->text('blogs', 'admin_settings_results_per_page'))
            ->setValue($configs['results_per_page'])
            ->addValidator(new IntValidator())
            ->setRequired(true);

        $this->addElement($textField['results_per_page']);

        $submit = new Submit('submit');

        $submit->setValue($l->text('blogs', 'save_btn_label'));

        $this->addElement($submit);
    }
}