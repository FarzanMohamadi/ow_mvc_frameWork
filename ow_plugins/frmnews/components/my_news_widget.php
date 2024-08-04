<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmnews.components
 * @since 1.0
 */
class FRMNEWS_CMP_MyNewsWidget extends BASE_CLASS_Widget
{

    public function __construct( BASE_CLASS_WidgetParameter $paramObj )
    {
        parent::__construct();

        /* @var $service EntryService */
        $service = EntryService::getInstance();

        $userId = $paramObj->additionalParamList['entityId'];

        if ( $userId != OW::getUser()->getId() )
        {
            $this->setVisible(false);
            return;
        }

        $this->assign('count', (int) $service->countUserEntry($userId));

        $this->assign('isAdmin', OW::getUser()->isAdmin());

        $this->assign('commentCount', $service->countUserEntryComment($userId));

        $this->assign('draftCount', (int) $service->countUserDraft($userId));
    }

    public static function getSettingList()
    {
        $settingList = array();

        return $settingList;
    }

    public static function getStandardSettingValueList()
    {
        return array(
            self::SETTING_TITLE => OW::getLanguage()->text('frmnews', 'my_news'),
            self::SETTING_ICON => 'ow_ic_write',
            self::SETTING_WRAP_IN_BOX => true,
            self::SETTING_SHOW_TITLE => true,
        );
    }

    public static function getAccess()
    {
        return self::ACCESS_MEMBER;
    }
}