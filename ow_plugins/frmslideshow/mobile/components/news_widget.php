<?php
/**
 * frmslideshow
 */
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmslideshow
 * @since 1.0
 */

class FRMSLIDESHOW_MCMP_NewsWidget extends BASE_CLASS_Widget
{
    /**
     * @param BASE_CLASS_WidgetParameter $paramObj
     */
    public function __construct( BASE_CLASS_WidgetParameter $paramObj )
    {
        parent::__construct();

        $count = OW::getConfig()->getValue('frmslideshow', 'news_count');
        $max_text_char = OW::getConfig()->getValue('frmslideshow', 'max_text_char');

        $entry_service = EntryService::getInstance();
        $arr = $entry_service->findList(0, $count);
        $list = array();
        foreach ( $arr as $item )
        {
            $title = UTIL_String::truncate( $item->title, 32, '...' );

            $text = explode("<!--more-->", $item->entry);
            $isPreview = count($text) > 1;
            if ( !$isPreview )
                $text = explode('<!--page-->', $text[0]);
            $showMore = count($text) > 1;
            $text = $text[0];
            $text = strip_tags(UTIL_HtmlTag::stripTags($text));
            $text = UTIL_String::truncate( $text, $max_text_char, '...' );


            $list[] = array('title' => $title, 'text' => $text, 'showMore'=>$showMore,
                'date' => UTIL_DateTime::formatSimpleDate($item->timestamp,true),
                'url' => $entry_service->getEntryUrl($item));

        }

        $this->assign('count', count($list));
        $this->assign('items', $list);
        $this->setTemplate(OW::getPluginManager()->getPlugin('frmslideshow')->getMobileCmpViewDir() . 'news_widget.html');

        // import static files
        $jsDir = OW::getPluginManager()->getPlugin("frmslideshow")->getStaticJsUrl();
        OW::getDocument()->addScript($jsDir . "slick.js");
        OW::getDocument()->addScript($jsDir . "frmslideshow.js");

        $cssFile = OW::getPluginManager()->getPlugin('frmslideshow')->getStaticCssUrl() . 'frmslideshow.css';
        OW::getDocument()->addStyleSheet($cssFile);
    }

    public static function getAccess()
    {
        return self::ACCESS_ALL;
    }

    public static function getStandardSettingValueList()
    {
        return array(
            self::SETTING_SHOW_TITLE => true,
            self::SETTING_WRAP_IN_BOX => true,
            self::SETTING_TITLE => OW_Language::getInstance()->text('frmslideshow', 'news_widget_title'),
            self::SETTING_ICON => self::ICON_USER
        );
    }
}
