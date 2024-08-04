<?php
class FRMTECHNOLOGY_CMP_ServicesUniMembers extends BASE_CLASS_Widget
{

    public function __construct()
    {
        parent::__construct();
    }

    public static function getStandardSettingValueList()
    {
        return array(
            self::SETTING_SHOW_TITLE => true,
            self::SETTING_TITLE => OW::getLanguage()->text('frmtechnology', 'services_uni_members_widget_title'),
            self::SETTING_WRAP_IN_BOX => true,
        );
    }


}