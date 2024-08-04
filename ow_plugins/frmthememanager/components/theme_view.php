<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmthememanager
 * @since 1.0
 */
class FRMTHEMEMANAGER_CMP_ThemeView extends OW_Component
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $themeActionController = OW::getRouter()->urlFor('FRMTHEMEMANAGER_CTRL_ThemeActions', 'index');
        $this->assign('themeActionController', $themeActionController);
        $this->assign('frmThemeManagerNewTheme', OW::getRouter()->urlForRoute('create_new_theme_route'));
        $this->assign('themeColors', FRMTHEMEMANAGER_BOL_Service::getInstance()->colorsList);
    }

}