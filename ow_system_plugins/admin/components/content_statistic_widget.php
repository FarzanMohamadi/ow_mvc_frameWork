<?php
/**
 * Admin content statistics widget component
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_system_plugins.base.components
 * @since 1.7.6
 */
class ADMIN_CMP_ContentStatisticWidget extends ADMIN_CMP_AbstractStatisticWidget
{
    /**
     * Default content group
     * @var string
     */
    protected $defaultContentGroup;

    /**
     * Default period
     * @var string
     */
    protected $defaultPeriod;

    /**
     * Class constructor
     */
    public function __construct( BASE_CLASS_WidgetParameter $paramObj )
    {
        parent::__construct();

        if (isset($paramObj->customParamList['defaultContentGroup']) && isset($paramObj->customParamList['defaultPeriod'])) {
            $this->defaultContentGroup = $paramObj->customParamList['defaultContentGroup'];
            $this->defaultPeriod = $paramObj->customParamList['defaultPeriod'];
        }
        else{
            $this->defaultContentGroup =  $paramObj->additionalParamList['defaultContentGroup'];
            $this->defaultPeriod = $paramObj->additionalParamList['defaultPeriod'];
        }
    }

    /**
     * On before render
     *
     * @return void
     */
    public function onBeforeRender()
    {
        // register forms
        $this->addForm(new ContentStatisticForm('content_statistics_form', $this->defaultContentGroup));

        // register components
        $this->addComponent('statistics', new ADMIN_CMP_ContentStatistic(array(
            'defaultContentGroup' => $this->defaultContentGroup,
            'defaultPeriod' => $this->defaultPeriod
        )));

        $this->addMenu('content');

        // assign view variables
        $this->assign('defaultContentGroup', $this->defaultContentGroup);
        $this->assign('defaultPeriod', $this->defaultPeriod);
    }

    /**
     * Get custom settings list
     *
     * @return array
     */
    public static function getSettingList()
    {
        $settingList = array();

        $contentGroups = self::getContentTypes();;
        $settingList['defaultContentGroup'] = array(
            'presentation' => self::PRESENTATION_SELECT,
            'label' => OW::getLanguage()->text('admin', 'widget_content_statistics_default_content_group'),
            'value' => !empty($contentGroups) ? key($contentGroups) : null,
            'optionList' => $contentGroups
        );

        $settingList['defaultPeriod'] = array(
            'presentation' => self::PRESENTATION_SELECT,
            'label' => OW::getLanguage()->text('admin', 'site_statistics_default_period'),
            'value' => BOL_SiteStatisticService::PERIOD_TYPE_LAST_7_DAYS,
            'optionList' => array(
                BOL_SiteStatisticService::PERIOD_TYPE_TODAY => OW::getLanguage()->text('admin', 'site_statistics_today_period'),
                BOL_SiteStatisticService::PERIOD_TYPE_YESTERDAY => OW::getLanguage()->text('admin', 'site_statistics_yesterday_period'),
                BOL_SiteStatisticService::PERIOD_TYPE_LAST_7_DAYS => OW::getLanguage()->text('admin', 'site_statistics_last_7_days_period'),
                BOL_SiteStatisticService::PERIOD_TYPE_LAST_30_DAYS => OW::getLanguage()->text('admin', 'site_statistics_last_30_days_period'),
                BOL_SiteStatisticService::PERIOD_TYPE_LAST_YEAR => OW::getLanguage()->text('admin', 'site_statistics_last_year_period')
            )
        );

        return $settingList;
    }

    /**
     * Get standart setting values list
     *
     * @return array
     */
    public static function getStandardSettingValueList()
    {
        return array(
            self::SETTING_TITLE => OW::getLanguage()->text('admin', 'widget_content_statistics'),
            self::SETTING_ICON => self::ICON_FILES,
            self::SETTING_SHOW_TITLE => true,
            self::SETTING_WRAP_IN_BOX => true
        );
    }

    /**
     * Get content types
     *
     * @return array
     */
    public static function getContentTypes()
    {
        $contentGroups = BOL_ContentService::getInstance()->getContentGroups();
        $processedGroups = array();

        $disallowedEntityTypes = explode(',',
                OW::getConfig()->getValue('base', 'site_statistics_disallowed_entity_types'));

        foreach ($contentGroups as $group => $data)
        {
            $skip = false;

            foreach($data['entityTypes'] as $entityType)
            {
                if ( in_array($entityType, $disallowedEntityTypes) )
                {
                    $skip = true;
                    break;
                }
            }

            if ( $skip )
            {
                continue;
            }

            $processedGroups[$group] = $data['label'];
        }

        return $processedGroups;
    }
}

/**
 * Class ContentStatisticForm
 */
class ContentStatisticForm extends Form
{
    /**
     * Class constructor
     *
     * @param string $name
     * @apram string $defaultGroup
     */
    public function __construct($name, $defaultGroup)
    {
        parent::__construct($name);

        $processedGroups = ADMIN_CMP_ContentStatisticWidget::getContentTypes();

        $groupField = new Selectbox('group');
        $groupField->setOptions($processedGroups);
        $groupField->setValue($defaultGroup);
        $groupField->setHasInvitation(false);
        $this->addElement($groupField);
    }
}