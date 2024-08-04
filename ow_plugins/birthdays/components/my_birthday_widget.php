<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_system_plugins.base.components
 * @since 1.0
 */
class BIRTHDAYS_CMP_MyBirthdayWidget extends BASE_CLASS_Widget
{

    public function __construct( BASE_CLASS_WidgetParameter $params )
    {
        parent::__construct();

        $service = BIRTHDAYS_BOL_Service::getInstance();
        $user = BOL_UserService::getInstance()->findUserById($params->additionalParamList['entityId']);
        
        if( $user === null )
        {
            $this->setVisible(false);
            return;
        }

        $eventParams =  array(
                'action' => 'birthdays_view_my_birthdays',
                'ownerId' => $user->getId(),
                'viewerId' => OW::getUser()->getId()
            );
        
        try
        {
            OW::getEventManager()->getInstance()->call('privacy_check_permission', $eventParams);
        }
        catch( RedirectException $e )
        {
            $this->setVisible(false);
            return;
        }
        
        $result = $service->findListByBirthdayPeriod(date('Y-m-d'), date('Y-m-d', strtotime('+7 day')), 0, 1, array( $user->getId()));
        $isComingSoon = !empty($result);
        $this->assign('ballonGreenSrc', OW::getPluginManager()->getPlugin('birthdays')->getStaticUrl().'img/' . 'ballon-lime-green.png');
        $data = BOL_QuestionService::getInstance()->getQuestionData(array( $user->getId() ), array('birthdate'));        

        if ( (!$isComingSoon && !$params->customizeMode) || !array_key_exists('birthdate', $data[$user->getId()]) )
        {
            $this->setVisible(false);
            return;
        }        
        
        $birtdate = $data[$user->getId()]['birthdate']; 
        $dateInfo = UTIL_DateTime::parseDate($birtdate, UTIL_DateTime::MYSQL_DATETIME_DATE_FORMAT);
        $label = '';

        if ( $dateInfo['day'] == date('d') )
        {
            $label = '<span class="ow_green" style="font-weight: bold; text-transform: uppercase;">' . OW::getLanguage()->text('base', 'date_time_today') . ": ".UTIL_DateTime::formatBirthdate($dateInfo['year'], $dateInfo['month'], $dateInfo['day']) .'</span> ';
        }
        else if ( $dateInfo['day'] == date('d') + 1 )
        {
            $label = '<span class="ow_green" style="font-weight: bold; text-transform: uppercase;">' . OW::getLanguage()->text('base', 'date_time_tomorrow') .": ".UTIL_DateTime::formatBirthdate($dateInfo['year'], $dateInfo['month'], $dateInfo['day']) . '</span> ';
        }
        else
        {
            $label = '<span class="ow_small">' . UTIL_DateTime::formatBirthdate($dateInfo['year'], $dateInfo['month'], $dateInfo['day']) . '</span>';
        }
        
        $this->assign('label', $label);
    }

    public static function getStandardSettingValueList()
    {
        return array(
            self::SETTING_TITLE => OW::getLanguage()->text('birthdays', 'my_widget_title'),
            self::SETTING_ICON => self::ICON_USER,
            self::SETTING_SHOW_TITLE => true,
            self::SETTING_WRAP_IN_BOX => true,
            self::SETTING_FREEZE => true
        );
    }

    public static function getAccess()
    {
        return self::ACCESS_ALL;
    }
}