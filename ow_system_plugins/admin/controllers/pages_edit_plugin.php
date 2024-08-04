<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_system_plugins.base.controller
 * @since 1.0
 */

class ADMIN_CTRL_PagesEditPlugin extends ADMIN_CTRL_Abstract
{

    public function __construct()
    {
        parent::__construct();

        $this->setPageHeading(OW::getLanguage()->text('admin', 'pages_page_heading'));
        $this->setPageHeadingIconClass('ow_ic_gear_wheel');
        OW::getDocument()->getMasterPage()->getMenu(OW_Navigation::ADMIN_PAGES)->setItemActive('sidebar_menu_item_pages_manage');
    }

    public function index( $params )
    {
        $id = (int) $params['id'];

        $menu = BOL_NavigationService::getInstance()->findMenuItemById($id);

        $form = new EditPluginPageForm('edit-form', $menu);

        $service = BOL_NavigationService::getInstance();

        if ( OW::getRequest()->isPost() && $form->isValid($_POST) )
        {
            $data = $form->getValues();

            $visibleFor = 0;
            $arr = !empty($data['visible-for']) ? $data['visible-for'] : array();
            foreach ( $arr as $val )
            {
                $visibleFor += $val;
            }

            $service->saveMenuItem(
                $menu->setVisibleFor($visibleFor)
            );

            $languageService = BOL_LanguageService::getInstance();


            $langKey = $languageService->findKey($menu->getPrefix(), $menu->getKey());

            $langValue = $languageService->findValue($languageService->getCurrent()->getId(), $langKey->getId());

            $languageService->saveValue(
                $langValue->setValue($data['name'])
            );

            $adminPlugin = OW::getPluginManager()->getPlugin('admin');

            OW::getFeedback()->info(OW::getLanguage()->text($adminPlugin->getKey(), 'updated_msg'));

            $this->redirect();
        }

//--    	
        $this->addForm($form);
    }
}

class EditPluginPageForm extends Form
{

    public function __construct( $name, BOL_MenuItem $menu )
    {
        parent::__construct($name);

        $navigationService = BOL_NavigationService::getInstance();

        $document = $navigationService->findDocumentByKey($menu->getDocumentKey());

        $language = OW_Language::getInstance();

        $adminPlugin = OW::getPluginManager()->getPlugin('admin');

        $nameTextField = new TextField('name');

        $this->addElement(
                $nameTextField->setValue($language->text($menu->getPrefix(), $menu->getKey()))
                ->setLabel(OW::getLanguage()->text('admin', 'pages_edit_local_menu_name'))
                ->setRequired()
        );

        $visibleForCheckboxGroup = new CheckboxGroup('visible-for');

        $visibleFor = $menu->getVisibleFor();

        $options = array(
            '1' => OW::getLanguage()->text('admin', 'pages_edit_visible_for_guests'),
            '2' => OW::getLanguage()->text('admin', 'pages_edit_visible_for_members')
        );

        $values = array();

        foreach ( $options as $value => $option )
        {
            if ( !($value & $visibleFor) )
                continue;

            $values[] = $value;
        }

        $this->addElement(
                $visibleForCheckboxGroup->setOptions($options)
                ->setValue($values)
                ->setLabel(OW::getLanguage()->text('admin', 'pages_edit_local_visible_for'))
        );

        $submit = new Submit('save');

        $this->addElement(
            $submit->setValue(OW::getLanguage()->text('admin', 'save_btn_label'))
        );
    }
}