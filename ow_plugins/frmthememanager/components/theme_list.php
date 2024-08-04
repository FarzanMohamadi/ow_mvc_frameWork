<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmthememanager
 * @since 1.0
 */
class FRMTHEMEMANAGER_CMP_ThemeList extends OW_Component
{
    /**
     * Constructor.
     */
    public function __construct( $params = null)
    {
        parent::__construct();
        $backUri ='';
        if( isset($params) && $params != null ){
            if ($params['key'] == 'appearance' || $params['key'] == 'plugin'  ){
                $backUri = '?backUri='. $params['key'];
            }
        }
        parent::__construct();
        $themeActionController= OW::getRouter()->urlFor('FRMTHEMEMANAGER_CTRL_ThemeActions', 'index');
        $activeTheme =  OW::getConfig()->getValue('frmthememanager', 'activeTheme') ;
        $this->assign('themeActionController',$themeActionController);
        $this->assign('frmThemeManagerThemes',FRMTHEMEMANAGER_BOL_Service::getInstance()->findAllThemes());
        $this->assign('frmThemeManagerNewTheme',OW::getRouter()->urlForRoute('create_new_theme_route').$backUri );
        $this->assign('frmThemeManagerUploadTheme',OW::getRouter()->urlForRoute('upload_theme_route').$backUri );
        $this->assign('activeTheme', $activeTheme );
        $this->assign('pluginUrl', OW::getPluginManager()->getPlugin('frmthememanager')->getUserFilesUrl() );
        $this->assign('noLogoLink', OW::getPluginManager()->getPlugin('frmthememanager')->getStaticUrl().'img/noLogo.png' );
        if(isset($activeTheme) && $activeTheme !=null){
            OW::getDocument()->addOnloadScript('
            $(window).on(\'load\', function() {
                $(\'.theme_item.frmthememanager_theme.active .theme_icon\').trigger( "click" );
            });
        ');
        }
    }
}