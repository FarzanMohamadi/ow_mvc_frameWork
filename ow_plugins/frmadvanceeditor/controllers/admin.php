<?php
class FRMADVANCEEDITOR_CTRL_Admin extends ADMIN_CTRL_Abstract{
    public function index(){
        if (!OW::getUser()->isAuthenticated()){
            throw new Redirect404Exception();
        }
        if(!OW::getUser()->isAdmin()){
            throw new Redirect404Exception();
        }

        $this->setPageHeading(OW::getLanguage()->text('frmadvanceeditor', 'config_page_title'));
        $this->setPageTitle(OW::getLanguage()->text('frmadvanceeditor', 'config_page_title'));
        $this->setPageHeadingIconClass('ow_ic_gear_wheel');

        $form = new Form('editor_config_form');

        $fieldMaxCount  = new TextField('max_symbols_count');
        $fieldMaxCount->setLabel($this->text('frmadvanceeditor','max_symbols_count_title'));
        $validator = new IntValidator(1);
        $validator->setErrorMessage($this->text('frmadvanceeditor','max_symbols_count_error'));
        $fieldMaxCount->addValidator($validator);
        $form->addElement($fieldMaxCount);

        $fieldIsCustomHtmlWidgetAdvance = new CheckboxField('is_custom_html_widget_advance');
        $fieldIsCustomHtmlWidgetAdvance->setLabel($this->text('frmadvanceeditor','is_custom_html_widget_advance_title'));
        $fieldIsCustomHtmlWidgetAdvance->setValue(OW::getConfig()->getValue('frmadvanceeditor', 'isCustomHtmlWidgetEditorAdvance'));
        $form->addElement($fieldIsCustomHtmlWidgetAdvance);

        $submit = new Submit('save');
        $form->addElement($submit);
        $this->addForm($form);

        if(OW::getRequest()->isPost()){
            if($form->isValid($_POST)){
                $data = $form->getValues();
                $maxSymbolsCount = $data['max_symbols_count'];
                if(!empty($maxSymbolsCount)){
                    OW::getConfig()->saveConfig('frmadvanceeditor','MaxSymbolsCount', $maxSymbolsCount);
                }
                OW::getConfig()->saveConfig('frmadvanceeditor', 'isCustomHtmlWidgetEditorAdvance', $data['is_custom_html_widget_advance']);
                $adminPlugin = OW::getPluginManager()->getPlugin('admin');
                if(isset($adminPlugin) && $adminPlugin->isActive()){
                    OW::getFeedback()->info(OW::getLanguage()->text($adminPlugin->getKey(), 'updated_msg'));
                }
                $this->redirect();
            }
        }else{
            if ( OW::getConfig()->configExists('frmadvanceeditor','MaxSymbolsCount') ){
                $maxSymbolsCount = OW::getConfig()->getValue('frmadvanceeditor','MaxSymbolsCount');
                $fieldMaxCount->setValue($maxSymbolsCount);
            }
        }
    }
    private function text( $prefix, $key, array $vars = null )
    {
        return OW::getLanguage()->text($prefix, $key, $vars);
    }


}