<?php
/**
 * Feed Widget
 *
 * @package ow_plugins.newsfeed.components
 * @since 1.0
 */
abstract class NEWSFEED_CMP_FeedWidget extends BASE_CLASS_Widget
{
    private $feedParams = array();
    /**
     *
     * @var NEWSFEED_CMP_Feed
     */
    private $feed;

    /**
     * @return Constructor.
     */
    public function __construct( BASE_CLASS_WidgetParameter $paramObj, $template = null )
    {
        parent::__construct();

        $template = empty($template) ? 'feed_widget' : $template;
        $this->setTemplate(OW::getPluginManager()->getPlugin('newsfeed')->getCmpViewDir() . $template . '.html');

        $this->feedParams['customizeMode'] = $paramObj->customizeMode;

        $this->feedParams['viewMore'] = $paramObj->customParamList['view_more'];
        $this->feedParams['displayCount'] = (int) $paramObj->customParamList['count'];
        if (isset($paramObj->additionalParamList)) {
            $this->feedParams['additionalParamList'] = $paramObj->additionalParamList;
        }

        $this->feedParams['displayCount'] = $this->feedParams['displayCount'] > NEWSFEED_CLASS_Driver::$MAX_ITEMS
                ? NEWSFEED_CLASS_Driver::$MAX_ITEMS
                : $this->feedParams['displayCount'];

        $event = OW::getEventManager()->trigger(new OW_Event('newsfeed.widget.feed.params', ['params'=>$paramObj], $this->feedParams));
        $this->feedParams = $event->getData();
    }

    public function setFeed( NEWSFEED_CMP_Feed $feed )
    {
        $this->feed = $feed;
    }

    public function onBeforeRender()
    {
        $this->feed->setup($this->feedParams);

        $this->addComponent('feed', $this->feed);
    }

    public static function getStandardSettingValueList()
    {
        return array(
            self::SETTING_SHOW_TITLE => true,
            self::SETTING_TITLE => OW::getLanguage()->text('newsfeed', 'widget_feed_title'),
            self::SETTING_WRAP_IN_BOX => false,
            self::SETTING_ICON => self::ICON_CLOCK
        );
    }

    public static function getAccess()
    {
        return self::ACCESS_ALL;
    }

    public static function getSettingList()
    {
        $settingList['count'] = array(
            'presentation' => self::PRESENTATION_SELECT,
            'label' => OW::getLanguage()->text('newsfeed', 'widget_settings_count'),
            'optionList' => array(5 => '5', '10' => 10, '20' => 20, '40' => 40),
            'value' => 10
        );

        $settingList['view_more'] = array(
            'presentation' => self::PRESENTATION_CHECKBOX,
            'label' => OW::getLanguage()->text('newsfeed', 'widget_settings_view_more'),
            'value' => true
        );

        return $settingList;
    }
}