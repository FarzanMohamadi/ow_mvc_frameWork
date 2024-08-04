<?php
/**
 * 
 * All rights reserved.
 */

/**
 *
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmpluginmanager.bol
 * @since 1.0
 */
class FRMPLUGINMANAGER_BOL_Service
{
    private static $classInstance;

    public static function getInstance()
    {
        if (self::$classInstance === null) {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    private function __construct()
    {
    }

    public function onBeforeThemeInstall(OW_Event $event){
        $params = $event->getParams();
        if(isset($params['paramsData'])){
            $paramsData = $params['paramsData'];
            if(isset($paramsData['key'])){
                if(!$this->canInstallTheme($paramsData['key'])){
                    throw new Redirect404Exception();
                }
            }else{
                throw new Redirect404Exception();
            }
        }else{
            throw new Redirect404Exception();
        }
    }

    public function onBeforePluginInstall(OW_Event $event){
        $params = $event->getParams();
        if(isset($params['pluginKey'])){
            if(!$this->canInstallPlugin($params['pluginKey'])){
                throw new Redirect404Exception();
            }
        }
    }

    public function onBeforePluginUnistall(OW_Event $event){
        $params = $event->getParams();
        if(isset($params['pluginKey'])){
            if(!$this->canUninstallPlugin($params['pluginKey'])){
                throw new Redirect404Exception();
            }
        }
    }

    public function canUninstallPlugin($key, $pluginsXml = null){
        if($pluginsXml == null){
            $pluginsXml = BOL_PluginService::getInstance()->getPluginsXmlInfo();
        }

        if(isset($pluginsXml[$key]['removable']) &&
            $pluginsXml[$key]['removable'] == 0){
            return false;
        }

        return true;
    }

    public function canSeePlugin($key, $pluginsXml = null){
        $ignorePlugins = array();
        $event = OW::getEventManager()->trigger(new OW_Event('on.before_plugin_view_check', array('pluginsXml' => $pluginsXml)));
        if(isset($event->getData()['plugins'])){
            $ignorePlugins = $event->getData()['plugins'];
        }

        if(in_array($key, $ignorePlugins)){
            return false;
        }

        if($pluginsXml == null){
            $pluginsXml = BOL_PluginService::getInstance()->getPluginsXmlInfo();
        }
        if(!isset($pluginsXml[$key]['visible']) ||
            $pluginsXml[$key]['visible'] == 1){
            return true;
        }

        return false;
    }

    public function canInstallTheme($key){
        $ignoreThemes = array();
        $event = OW::getEventManager()->trigger(new OW_Event('on.before_theme_view_check'));
        if(isset($event->getData()['themes'])){
            $ignoreThemes = $event->getData()['themes'];
        }

        if(in_array($key, $ignoreThemes)){
            return false;
        }

        $themeXml = BOL_ThemeService::getInstance()->getThemeXmlInfoForKey($key);
        if(!isset($themeXml['installable']) ||
            $themeXml['installable'] == 1){
            return true;
        }

        return false;
    }

    public function canInstallPlugin($key, $pluginsXml = null){
        $ignorePlugins = array();
        $event = OW::getEventManager()->trigger(new OW_Event('on.before_plugin_view_check', array('pluginsXml' => $pluginsXml)));
        if(isset($event->getData()['plugins'])){
            $ignorePlugins = $event->getData()['plugins'];
        }

        if(in_array($key, $ignorePlugins)){
            return false;
        }

        if($pluginsXml == null){
            $pluginsXml = BOL_PluginService::getInstance()->getPluginsXmlInfo();
        }

        if(isset($pluginsXml[$key]['installable']) &&
            $pluginsXml[$key]['installable'] == 0){
            return false;
        }

        return true;
    }

    public function onPluginListView(OW_Event $event){
        $params = $event->getParams();
        $pluginsPopulated = array();
        $pluginsXml = BOL_PluginService::getInstance()->getPluginsXmlInfo();
        if(isset($params['type'])){
            $plugins = $event->getData();
            if($params['type'] == 'index'){
                $activePlugins = $plugins['active'];
                $inactivePlugins = $plugins['inactive'];
                $activePluginsPopulated = array();
                $inactivePluginsPopulated = array();
                foreach ($activePlugins as $activePlugin){
                    if($this->canSeePlugin($activePlugin['key'], $pluginsXml)){
                        $activePluginsPopulated[] = $activePlugin;
                    }
                }
                foreach ($inactivePlugins as $inactivePlugin){
                    if($this->canInstallPlugin($activePlugin['key'], $pluginsXml)){
                        $inactivePluginsPopulated[] = $inactivePlugin;
                    }
                }
                $pluginsPopulated = array(
                    "active" => $activePluginsPopulated,
                    "inactive" => $inactivePluginsPopulated
                );
            }else if($params['type'] == 'available'){
                foreach ($plugins as $plugin){
                    if($this->canInstallPlugin($plugin['key'], $pluginsXml)){
                        $pluginsPopulated[] = $plugin;
                    }
                }
            }

            $event->setData($pluginsPopulated);
        }
    }

    public function onThemesListView(OW_Event $event){
        $params = $event->getParams();
        $themesPopulated = array();
        if(isset($params['type'])){
            $themes = $event->getData();
            if($params['type'] == 'index'){
                foreach ($themes as $theme){
                    if($this->canInstallTheme($theme['key'])){
                        $themesPopulated[$theme['key']] = $theme;
                    }
                }
            }

            $event->setData($themesPopulated);
        }
    }
}