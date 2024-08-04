<?php
/**
 * FRM Employee
 */
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmemployee
 * @since 1.0
 */

class FRMEMPLOYEE_CMP_ProfileWidget extends BASE_CLASS_Widget
{

    public function __construct( BASE_CLASS_WidgetParameter $param )
    {
        parent::__construct();

        $userId = $param->additionalParamList['entityId'];
        $displayName = BOL_UserService::getInstance()->getDisplayName($userId);

        $em = FRMEMPLOYEE_BOL_Service::getInstance()->getEmployer($userId);
        if (empty($em)){
            self::setVisible(false);
            return;
        }

        $text = OW::getLanguage()->text('frmemployee', 'widget_text',
            ['employee'=>$displayName, 'employer'=> $em]);
        $this->assign('contentText', $text);
    }

    public function render()
    {
        return parent::render();
    }

    public static function getSettingList()
    {
        $settingList = array();
        $settingList['content'] = array(
            'presentation' => self::PRESENTATION_HIDDEN,
            'label' => '',
            'value' => null
        );

        return $settingList;
    }

    public static function getStandardSettingValueList()
    {
        return array(
            self::SETTING_TITLE => OW::getLanguage()->text('frmemployee', 'widget_title'),
            self::SETTING_SHOW_TITLE => true,
            self::SETTING_WRAP_IN_BOX => true,
            self::SETTING_ICON => self::ICON_INFO,
            self::SETTING_FREEZE => true
        );
    }

}
