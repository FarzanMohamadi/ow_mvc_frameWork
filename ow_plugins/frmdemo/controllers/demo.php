<?php
class FRMDEMO_CTRL_Demo extends OW_ActionController
{

    public function changeTheme($params)
    {
        if(isset($_POST['themeValue'])){
            $themeValue = $_POST['themeValue'];
            $ignoresThemeList = array();
            if(FRMSecurityProvider::checkPluginActive('frmupdateserver', true)) {
                $ignoresThemeList = FRMUPDATESERVER_BOL_Service::getInstance()->getIgnoreThemesKeyList();
            }else{
                $response = UTIL_HttpClient::get(BOL_StorageService::UPDATE_SERVER. "get-ignore-themes");
                if ( $response && $response->getStatusCode() == UTIL_HttpClient::HTTP_STATUS_OK && $response->getBody() )
                {
                    $ignoresThemeList = json_decode($response->getBody());
                }
            }
            if (OW::getThemeManager()->getThemeService()->themeExists($themeValue) && !in_array($themeValue, $ignoresThemeList)) {
                OW::getThemeManager()->getThemeService()->updateThemeList();
                OW::getConfig()->saveConfig('base', 'selectedTheme', $themeValue);
            }
        }
        exit(true);
    }

    public function updateStaticFiles(){
        if(OW::getUser()->isAuthenticated() && OW::getUser()->isAdmin()){
            FRMSecurityProvider::updateStaticFiles();
            OW::getFeedback()->info('Static files updated successfully');
        }
        $this->redirect(OW_URL_HOME);
    }
}