<?php
/**
 * Widget Settings
 *
 * @package ow_system_plugins.base.components
 * @since 1.0
 */
class BASE_CMP_ComponentSettings extends OW_Component
{
    /**
     * Component default settings
     *
     * @var array
     */
    private $defaultSettingList = array();
    /**
     * Component default settings
     *
     * @var array
     */
    private $componentSettingList = array();
    private $standardSettingValueList = array();
    private $hiddenFieldList = array();
    private $access;

    private $uniqName;

    /**
     * Class constructor
     *
     * @param array $menuItems
     */
    public function __construct( $uniqName, array $componentSettings = array(), array $defaultSettings = array(), $access = null )
    {
        parent::__construct();

        $this->componentSettingList = $componentSettings;
        $this->defaultSettingList = $defaultSettings;
        $this->uniqName = $uniqName;
        $this->access = $access;
        
        $tpl = OW::getPluginManager()->getPlugin("base")->getCmpViewDir() . "component_settings.html";
        $this->setTemplate($tpl);
    }

    public function setStandardSettingValueList( $valueList )
    {
        $this->standardSettingValueList = $valueList;
    }

    protected function makeSettingList( $defaultSettingList )
    {
        $settingValues = $this->standardSettingValueList;
        foreach ( $defaultSettingList as $name => $value )
        {
            $settingValues[$name] = $value;
        }

        return $settingValues;
    }

    public function markAsHidden( $settingName )
    {
        $this->hiddenFieldList[] = $settingName;
    }

    /**
     * @see OW_Renderable::onBeforeRender()
     *
     */
    public function onBeforeRender()
    {
        $settingValues = $this->makeSettingList($this->defaultSettingList);

        $this->assign('values', $settingValues);

        $this->assign('avaliableIcons', IconCollection::allWithLabel());

        foreach ( $this->componentSettingList as $name => & $setting )
        {
            if ( $setting['presentation'] == BASE_CLASS_Widget::PRESENTATION_HIDDEN )
            {
                unset($this->componentSettingList[$name]);
                continue;
            }

            if ( isset($settingValues[$name]) )
            {
                $setting['value'] = $settingValues[$name];
            }

            if ( $setting['presentation'] == BASE_CLASS_Widget::PRESENTATION_CUSTOM )
            {
                if( FRMSecurityProvider::isRenderFunction($setting['render']) ){
                    $setting['markup'] = call_user_func($setting['render'], $this->uniqName, $name, empty($setting['value']) ? null : $setting['value']);
                }
            }

            $setting['display'] = !empty($setting['display']) ? $setting['display'] : 'table';
        }

        $this->assign('settings', $this->componentSettingList);


        $authorizationService = BOL_AuthorizationService::getInstance();

        $roleList = array();
        $isModerator = OW::getUser()->isAuthorized('base');
        
        if ( $this->access == BASE_CLASS_Widget::ACCESS_GUEST || !$isModerator )
        {
            $this->markAsHidden(BASE_CLASS_Widget::SETTING_RESTRICT_VIEW);
        }
        else
        {
            $roleList = $authorizationService->findNonGuestRoleList();

            if ( $this->access == BASE_CLASS_Widget::ACCESS_ALL )
            {
                $guestRoleId = $authorizationService->getGuestRoleId();
                $guestRole = $authorizationService->getRoleById($guestRoleId);
                array_unshift($roleList, $guestRole);
            }
        }

        $this->assign('roleList', $roleList);

        $this->assign('hidden', $this->hiddenFieldList);
    }

}

class IconCollection
{
    private static $all = array(
        "ow_ic_add",
        "ow_ic_aloud",
        "ow_ic_app",
        "ow_ic_attach",
        "ow_ic_birthday",
        "ow_ic_bookmark",
        "ow_ic_calendar",
        "ow_ic_cart",
        "ow_ic_chat",
        "ow_ic_clock",
        "ow_ic_comment",
        "ow_ic_cut",
        "ow_ic_dashboard",
        "ow_ic_delete",
        "ow_ic_down_arrow",
        "ow_ic_edit",
        "ow_ic_female",
        "ow_ic_file",
        "ow_ic_files",
        "ow_ic_flag",
        "ow_ic_folder",
        "ow_ic_forum",
        "ow_ic_friends",
        "ow_ic_gear_wheel",
        "ow_ic_help",
        "ow_ic_heart",
        "ow_ic_house",
        "ow_ic_info",
        "ow_ic_key",
        "ow_ic_left_arrow",
        "ow_ic_lens",
        "ow_ic_link",
        "ow_ic_lock",
        "ow_ic_mail",
        "ow_ic_male",
        "ow_ic_mobile",
        "ow_ic_moderator",
        "ow_ic_monitor",
        "ow_ic_move",
        "ow_ic_music",
        "ow_ic_new",
        "ow_ic_ok",
        "ow_ic_online",
        "ow_ic_picture",
        "ow_ic_plugin",
        "ow_ic_push_pin",
        "ow_ic_reply",
        "ow_ic_right_arrow",
        "ow_ic_rss",
        "ow_ic_save",
        "ow_ic_script",
        "ow_ic_server",
        "ow_ic_star",
        "ow_ic_tag",
        "ow_ic_trash",
        "ow_ic_unlock",
        "ow_ic_up_arrow",
        "ow_ic_update",
        "ow_ic_user",
        "ow_ic_video",
        "ow_ic_warning",
        "ow_ic_write"
    );

    public static function all()
    {
        return self::$all;
    }

    public static function allWithLabel()
    {
        $out = array();

        foreach ( self::$all as $icon )
        {
            $item = array();
            $item['class'] = $icon;
            $translatedLabel = OW::getLanguage()->text('base',$icon);
            if($translatedLabel == "base+" . $icon){
                $item['label'] = ucfirst(str_replace('_', ' ', substr($icon, 6)));
            }else{
                $item['label'] = $translatedLabel;
            }
            $out[] = $item;
        }

        return $out;
    }
}
