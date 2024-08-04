<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmadvancesearch
 * @since 1.0
 */
class FRMADVANCESEARCH_CTRL_Admin extends ADMIN_CTRL_Abstract
{

    public function __construct()
    {
        parent::__construct();

        $this->setPageHeading(OW::getLanguage()->text('frmadvancesearch', 'admin_settings_heading'));
        $this->setPageHeadingIconClass('ow_ic_gear_wheel');
    }

    /**
     * Default action
     */
    public function index()
    {
        OW::getDocument()->setTitle(OW::getLanguage()->text('frmadvancesearch', 'admin_settings_heading'));

        $form = new Form("form");

        $resultData = array();
        $event = OW::getEventManager()->trigger(new OW_Event('frmadvancesearch.on_collect_search_items',
            array('q' => 'collecting plugin names', 'maxCount' => 10, 'do_query' => false), $resultData));
        $resultData = $event->getData();

        $fieldNames = array();
        foreach($resultData as $key => $value){
            $tmpFieldKey = 'search_allowed_'.$key;
            $fieldNames[] = $tmpFieldKey;
            $field = new CheckboxField($tmpFieldKey);
            $field->setLabel($value['label'])->setValue(true);
            if(OW::getConfig()->configExists('frmadvancesearch',$tmpFieldKey)){
                $isAllowed = OW::getConfig()->getValue('frmadvancesearch',$tmpFieldKey);
                if(!$isAllowed){
                    $field->setValue(false);
                    unset($resultData[$tmpFieldKey]);
                }
            }
            $form->addElement($field);
        }
        $fieldNames[] = 'show_entity_author';
        $field = new CheckboxField('show_entity_author');
        $field->setLabel(OW::getLanguage()->text('frmadvancesearch','show_entity_author'))->setValue(true);
        if(OW::getConfig()->configExists('frmadvancesearch','show_entity_author')){
            $isAllowed = OW::getConfig()->getValue('frmadvancesearch','show_entity_author');
            if(!$isAllowed){
                $field->setValue(false);
            }
        }
        $form->addElement($field);

        $fieldNames[] = 'show_search_to_guest';
        $field = new CheckboxField('show_search_to_guest');
        $field->setLabel(OW::getLanguage()->text('frmadvancesearch','show_search_to_guest'));
        $isGuestAllowed = (boolean)OW::getConfig()->getValue('frmadvancesearch','show_search_to_guest');
        $field->setValue($isGuestAllowed);


        $form->addElement($field);
        $this->assign('field_list', $fieldNames);

        $submit = new Submit('submit');
        $submit->setValue(OW::getLanguage()->text('frmadvancesearch', 'save_btn_label'));
        $form->addElement($submit);

        if ( OW::getRequest()->isPost() && $form->isValid($_POST) )
        {
            $fieldValues = $form->getValues();
            foreach($fieldNames as $fieldKey){
                OW::getConfig()->saveConfig('frmadvancesearch', $fieldKey, $fieldValues[$fieldKey]);
            }
            OW::getConfig()->saveConfig('frmadvancesearch', 'show_entity_author', $fieldValues['show_entity_author']);
            OW::getFeedback()->info(OW::getLanguage()->text('frmadvancesearch', 'admin_changed_success'));
        }

        $this->addForm($form);
    }

}