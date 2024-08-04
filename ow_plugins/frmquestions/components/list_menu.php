<?php
class FRMQUESTIONS_CMP_ListMenu extends OW_Component
{

    public function __construct($selected)
    {
        parent::__construct();

        $this->addComponent('menu', $this->getMenu($selected));
    }

    public function onBeforeRender()
    {
        parent::onBeforeRender();
    }

    public function getMenu($selected)
    {
        $language = OW::getLanguage();

        $menu = new BASE_CMP_ContentMenu();

        $menuItem = new BASE_MenuItem();
        $menuItem->setKey('all');
        $menuItem->setPrefix('frmquestions');
        $menuItem->setLabel( $language->text('frmquestions', 'list_all_tab') );
        $menuItem->setOrder(1);
        if($selected == 'all')
            $menuItem->setActive(true);
        $menuItem->setUrl(OW::getRouter()->urlForRoute('frmquestions-home',array('type'=>'all')));
        $menuItem->setIconClass('ow_ic_all_questions ow_dynamic_color_icon');

        $menu->addElement($menuItem);

        $menuItem = new BASE_MenuItem();
        $menuItem->setKey('hottest');
        $menuItem->setPrefix('frmquestions');
        $menuItem->setLabel( $language->text('frmquestions', 'feed_order_popular') );
        $menuItem->setOrder(1);
        if($selected == 'hottest')
            $menuItem->setActive(true);
        $menuItem->setUrl(OW::getRouter()->urlForRoute('frmquestions-home',array('type'=>'hottest')));
        $menuItem->setIconClass('ow_ic_most_popular_questions ow_dynamic_color_icon');

        $menu->addElement($menuItem);

        if ( OW::getUser()->isAuthenticated() )
        {

            $menuItem = new BASE_MenuItem();
            $menuItem->setKey('my');
            $menuItem->setPrefix('frmquestions');
            $menuItem->setLabel( $language->text('frmquestions', 'list_my_tab') );
            $menuItem->setOrder(3);
            if($selected == 'my')
                $menuItem->setActive(true);
            $menuItem->setUrl(OW::getRouter()->urlForRoute('frmquestions-home',array('type'=>'my')));
            $menuItem->setIconClass('ow_ic_my_questions ow_dynamic_color_icon');

            $menu->addElement($menuItem);

            if ( OW::getPluginManager()->isPluginActive('friends') )
            {
                $menuItem = new BASE_MenuItem();
                $menuItem->setKey('friends');
                if($selected == 'friends')
                    $menuItem->setActive(true);
                $menuItem->setPrefix('frmquestions');
                $menuItem->setLabel( $language->text('frmquestions', 'list_friends_tab') );
                $menuItem->setOrder(2);
                $menuItem->setUrl(OW::getRouter()->urlForRoute('frmquestions-home',array('type'=>'friend')));
                $menuItem->setIconClass('ow_ic_friends_questions ow_dynamic_color_icon');

                $menu->addElement($menuItem);
            }
        }

        return $menu;
    }
}