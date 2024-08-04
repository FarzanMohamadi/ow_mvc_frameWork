<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmnews.components
 * @since 1.0
 */
class FRMNEWS_CMP_ManagementMenu extends OW_Component
{

    public function __construct()
    {
        parent::__construct();

        $language = OW::getLanguage()->getInstance();

        $item[0] = new BASE_MenuItem();

        $item[0]->setLabel($language->text('frmnews', 'manage_page_menu_published'))
            ->setOrder(0)
            ->setKey(0)
            ->setUrl(OW::getRouter()->urlForRoute('frmnews-manage-entrys'))
            ->setActive(OW::getRequest()->getRequestUri() == OW::getRouter()->uriForRoute('frmnews-manage-entrys'))
            ->setIconClass('ow_ic_clock ow_dynamic_color_icon');

        $item[1] = new BASE_MenuItem();

        $item[1]->setLabel($language->text('frmnews', 'manage_page_menu_drafts'))
            ->setOrder(1)
            ->setKey(1)
            ->setUrl(OW::getRouter()->urlForRoute('frmnews-manage-drafts'))
            ->setActive(OW::getRequest()->getRequestUri() == OW::getRouter()->uriForRoute('frmnews-manage-drafts'))
            ->setIconClass('ow_ic_geer_wheel ow_dynamic_color_icon');

        $item[2] = new BASE_MenuItem();

        $item[2]->setLabel($language->text('frmnews', 'manage_page_menu_comments'))
            ->setOrder(2)
            ->setKey(2)
            ->setUrl(OW::getRouter()->urlForRoute('frmnews-manage-comments'))
            ->setActive(OW::getRequest()->getRequestUri() == OW::getRouter()->uriForRoute('frmnews-manage-comments'))
            ->setIconClass('ow_ic_comment ow_dynamic_color_icon');

        $menu = new BASE_CMP_ContentMenu($item);

        $this->addComponent('menu', $menu);
    }
}