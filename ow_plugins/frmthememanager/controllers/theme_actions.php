<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmthememanager
 * @since 1.0
 */

class FRMTHEMEMANAGER_CTRL_ThemeActions extends OW_ActionController
{

    public function index()
    {
        $respondArray = array();

        $themeKey = trim($_POST['themeKey']);
        $action = trim($_POST['action']);
        $token = trim($_POST['token']);
        $service = FRMTHEMEMANAGER_BOL_Service::getInstance();
        $theme = $service -> getThemeArrayByKey( $themeKey );
        $adminPage = OW::getEventManager()->call('admin.check_if_admin_page');

        if($action == 'themeColorAjax'){
            $colors = $service -> getParentColors( $themeKey );
            if($colors == false){
                $respondArray['messageType'] = 'error';
                $respondArray['message'] = ow::getLanguage()->text('frmthememanager','operation_failure');
                echo json_encode($respondArray);
                exit;
            }
            $respondArray['messageType'] = 'success';
            $respondArray['colors'] = $colors;
            $respondArray['message'] = ow::getLanguage()->text('frmthememanager','operation_success');
            echo json_encode($respondArray);
            exit;
        }

        if($action == 'colorPicker'){
            $status = trim($_POST['status']);
            if( $status == "true" ){
                OW::getConfig()->saveConfig('frmthememanager', 'colorPicker', true);
            }elseif ( $status == "false" ){
                OW::getConfig()->saveConfig('frmthememanager', 'colorPicker', false);
            }else{
                $respondArray['messageType'] = 'error';
                $respondArray['message'] = ow::getLanguage()->text('frmthememanager','operation_failure');
                $respondArray['success'] = false;
                echo json_encode($respondArray);
                exit;
            }
            $respondArray['messageType'] = 'success';
            $respondArray['success'] = true;
            $respondArray['message'] = ow::getLanguage()->text('frmthememanager','operation_success');
            echo json_encode($respondArray);
            exit;
        }

        if($action == 'export'){
            $downloadUrl = $service->exportTheme($themeKey);
            $respondArray['export'] = true;
            $respondArray['messageType'] = 'success';
            $respondArray['downloadUrl'] = $downloadUrl;
            $respondArray['message'] = ow::getLanguage()->text('frmthememanager','operation_success');
            echo json_encode($respondArray);
            exit;
        }

        if(  !(OW::getUser()->isAuthenticated()
            && ow::getUser()->isAdmin()
            && !$adminPage
            && $theme['csrf_token'] == $token
        ) ){
            $respondArray['messageType'] = 'error';
            $respondArray['message'] = ow::getLanguage()->text('frmthememanager','theme_must_be_defined');
            echo json_encode($respondArray);
            exit;
        }

        switch ( $action )
        {
            case 'edit':
                $respondArray['edit'] = true;
                $respondArray['editUrl'] = OW::getRouter()->urlForRoute('create_new_theme_route',[$key = $themeKey]).'/'.$themeKey;
                break;

            case 'remove':
                $service -> removeTheme( $themeKey );
                break;

            case 'activate':
                $service -> activateTheme( $themeKey );
                break;

            case 'click':
                $themeArray = $service -> getThemeArrayByKey( $themeKey );
                $respondArray['click'] = true;
                $respondArray['clickData'] = $themeArray;
                $respondArray['activeTheme'] = OW::getConfig()->getValue('frmthememanager', 'activeTheme');
                $respondArray['debugMode'] = OW_DEBUG_MODE;
                $respondArray['pluginUrl'] = OW::getPluginManager()->getPlugin('frmthememanager')->getUserFilesUrl();
                $respondArray['themeActionController'] = OW::getRouter()->urlFor('FRMTHEMEMANAGER_CTRL_ThemeActions', 'index');
                break;

            case 'deactivateAll':
                $service -> deactivateThemes( $themeKey );
                break;
            case 'updateAllThemesList':
                $service -> updateAllThemesList();
                break;
        }
        $respondArray['messageType'] = 'success';
        $respondArray['message'] = ow::getLanguage()->text('frmthememanager','operation_success');
        echo json_encode($respondArray);
        exit;
    }

}